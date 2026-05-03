<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionTypeEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberBankAccount;
use App\Models\SavingAccount;
use App\Models\SavingTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    /**
     * Display the withdrawal creation page
     */
    public function create()
    {
        $members = Member::query()
            ->with([
                'user',
                'savingAccounts.savingProduct',
                'bankAccounts' => function ($q) {
                    $q->latest();
                },
            ])
            ->whereHas('user', function ($q) {
                $q->where('status', UserStatusEnum::ACTIVE->value);
            })
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'name' => $member->user?->name,
                    'user_code' => $member->user?->user_code,
                    'savingAccounts' => $member->savingAccounts->map(function ($acc) {
                        return [
                            'id' => $acc->id,
                            'type' => $acc->savingProduct?->name ?? '-',
                            'balance' => DB::table('get_saving_account_balance')->where('saving_account_id', $acc->id)->value('total_balance') ?? 0,
                            'tenor_months' => $acc->saving_tenor,
                            'target_amount' => $acc->target_amount,
                            'opened_at' => optional($acc->created_at)->toDateString(),
                        ];
                    })->toArray(),
                    'accounts' => $member->bankAccounts->map(function ($acc) {
                        return [
                            'bank_name' => $acc->bank_name,
                            'account_name' => $acc->account_name,
                            'account_number' => $acc->account_number,
                        ];
                    })->toArray(),
                ];
            });

        return Inertia::render('Admin/Savings/Withdrawal/Create', [
            'members' => $members,
        ]);
    }

    /**
     * Store withdrawal transaction
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'saving_account_id' => 'required|exists:saving_accounts,id',
            'amount' => 'required|numeric|min:1',
            'withdrawal_date' => 'required|date|before_or_equal:today',
            'method' => 'required|in:Tunai,Non-Tunai',
            'bank_name' => 'required_if:method,Non-Tunai|nullable|string',
            'account_name' => 'required_if:method,Non-Tunai|nullable|string',
            'account_number' => 'required_if:method,Non-Tunai|nullable|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        $member = Member::with('user')->findOrFail($validated['member_id']);
        $savingAccount = SavingAccount::findOrFail($validated['saving_account_id']);
        $savingBalance = DB::table('get_saving_account_balance')->where('saving_account_id', $savingAccount->id)->value('total_balance') ?? 0;

        // Verify that the saving account belongs to the member
        if ((int) $savingAccount->member_id !== (int) $member->id) {
            return back()
                ->withErrors(['saving_account_id' => 'Rekening simpanan tidak ditemukan untuk anggota ini']);
        }

        // Verify balance is sufficient
        if ($savingBalance < $validated['amount']) {
            return back()
                ->withErrors(['amount' => 'Saldo tidak cukup untuk penarikan sebesar Rp ' . number_format($validated['amount'])]);
        }

        $savingType = (string) ($savingAccount->savingProduct?->name ?? '');
        $typeLower = mb_strtolower($savingType);

        // Berjangka can only be withdrawn after maturity date.
        if (str_contains($typeLower, 'berjangka')) {
            $tenorMonths = (int) ($savingAccount->saving_tenor ?? 0);
            if ($tenorMonths > 0 && $savingAccount->created_at) {
                $maturityDate = Carbon::parse($savingAccount->created_at)->addMonths($tenorMonths)->startOfDay();
                if (Carbon::today()->lt($maturityDate)) {
                    return back()->withErrors([
                        'saving_account_id' => 'Tabungan berjangka belum jatuh tempo. Pencairan dapat dilakukan mulai ' . $maturityDate->format('d/m/Y'),
                    ]);
                }
            }
        }

        // Ibadah can be withdrawn only when target amount has been reached.
        if (str_contains($typeLower, 'ibadah')) {
            $targetAmount = (float) ($savingAccount->target_amount ?? 0);
            if ($targetAmount > 0 && (float) $savingBalance < $targetAmount) {
                return back()->withErrors([
                    'saving_account_id' => 'Tabungan ibadah belum mencapai target minimal Rp ' . number_format($targetAmount, 0, ',', '.'),
                ]);
            }
        }

        try {
            [$transaction, $saldoSebelum] = DB::transaction(function () use ($validated, $member, $savingAccount) {
                $lockedSavingAccount = SavingAccount::query()
                    ->whereKey($savingAccount->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Get latest balance from database view (source of truth)
                $saldoSebelum = DB::table('get_saving_account_balance')
                    ->where('saving_account_id', $lockedSavingAccount->id)
                    ->value('total_balance');

                if ($saldoSebelum === null) {
                    // fallback to model field if view not available
                    $saldoSebelum = (float) ($lockedSavingAccount->balance ?? 0);
                } else {
                    $saldoSebelum = (float) $saldoSebelum;
                }

                if ($saldoSebelum < (float) $validated['amount']) {
                    throw new \RuntimeException('Saldo tidak cukup untuk penarikan.');
                }

                $transaction = SavingTransaction::create([
                    'saving_transaction_code' => $this->generateTransactionCode(),
                    'saving_account_id' => $lockedSavingAccount->id,
                    'saving_amount' => $validated['amount'],
                    'transaction_type' => TransactionTypeEnum::WITHDRAWAL->value,
                    'saving_payment_method' => $validated['method'],
                    'transaction_date' => $validated['withdrawal_date'],
                    'saving_description' => $validated['notes'] ?? '',
                    'updated_by' => auth()->id(),
                ]);

                if ($validated['method'] === 'Non-Tunai') {
                    MemberBankAccount::updateOrCreate(
                        [
                            'member_id' => $member->id,
                            'account_number' => $validated['account_number'],
                        ],
                        [
                            'bank_name' => $validated['bank_name'],
                            'account_name' => $validated['account_name'],
                        ]
                    );

                    $transaction->update([
                        'account_number' => $validated['account_number'],
                    ]);
                }

                $lockedSavingAccount->update([
                    'balance' => $saldoSebelum - $validated['amount'],
                ]);

                return [$transaction, $saldoSebelum];
            });

            // Get admin/pengurus info
            $admin = auth()->user();
            $namaAdmin = $admin->name ?? 'Pengurus';

            // Prepare receipt data
            $strukData = [
                'transaction_id' => $transaction->id,
                'no_transaksi' => $transaction->saving_transaction_code,
                'tanggal' => $transaction->transaction_date,
                'pengurus' => $namaAdmin,
                'nama_anggota' => $member->user?->name ?? '-',
                'no_anggota' => $member->user?->user_code ?? '-',
                'jenis' => $savingType !== '' ? $savingType : '-',
                'metode' => $validated['method'],
                'nominal' => $validated['amount'],
                'saldo_sebelum' => $saldoSebelum,
                'saldo_sesudah' => $saldoSebelum - $validated['amount'],
                'bank_name' => $validated['bank_name'] ?? '',
                'account_name' => $validated['account_name'] ?? '',
                'account_number' => $validated['account_number'] ?? '',
            ];

            try {
                $receiptPath = $this->storeReceiptImage($transaction, $strukData);
                if ($receiptPath) {
                    $transaction->update([
                        'saving_transaction_receipt' => $receiptPath,
                    ]);
                }
            } catch (\Throwable $receiptException) {
                // Receipt generation should not break successful withdrawal posting.
                report($receiptException);
            }

            return redirect()
                ->route('admin.withdrawal.create')
                ->with('success', 'Penarikan simpanan berhasil disimpan')
                ->with('struk', $strukData);

        } catch (\Exception $e) {
            report($e);

            return back()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate and store withdrawal receipt as PNG, then return relative storage path.
     */
    private function storeReceiptImage(SavingTransaction $transaction, array $strukData): ?string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }

        $width = 700;
        $height = 900;
        $image = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 17, 24, 39);
        $gray = imagecolorallocate($image, 107, 114, 128);
        $green = imagecolorallocate($image, 22, 163, 74);

        imagefill($image, 0, 0, $white);

        $y = 30;
        imagestring($image, 5, 24, $y, 'KOPERASI POLBAN - STRUK PENARIKAN', $black);
        $y += 26;
        imagestring($image, 3, 24, $y, 'Tanggal cetak: ' . now()->format('d/m/Y H:i:s'), $gray);

        $y += 24;
        imageline($image, 24, $y, $width - 24, $y, $gray);

        $rows = [
            ['No Transaksi', (string) ($strukData['no_transaksi'] ?? '-')],
            ['Nama Anggota', (string) ($strukData['nama_anggota'] ?? '-')],
            ['No Anggota', (string) ($strukData['no_anggota'] ?? '-')],
            ['Jenis Simpanan', (string) ($strukData['jenis'] ?? '-')],
            ['Metode', (string) ($strukData['metode'] ?? '-')],
            ['Tanggal Penarikan', Carbon::parse($strukData['tanggal'] ?? now())->format('d/m/Y')],
            ['Nominal', 'Rp ' . number_format((float) ($strukData['nominal'] ?? 0), 0, ',', '.')],
            ['Saldo Sebelum', 'Rp ' . number_format((float) ($strukData['saldo_sebelum'] ?? 0), 0, ',', '.')],
            ['Saldo Sesudah', 'Rp ' . number_format((float) ($strukData['saldo_sesudah'] ?? 0), 0, ',', '.')],
        ];

        if (($strukData['metode'] ?? '') === 'Non-Tunai') {
            $rows[] = ['Bank', (string) ($strukData['bank_name'] ?? '-')];
            $rows[] = ['Atas Nama', (string) ($strukData['account_name'] ?? '-')];
            $rows[] = ['No Rekening', (string) ($strukData['account_number'] ?? '-')];
        }

        $rows[] = ['Pengurus', (string) ($strukData['pengurus'] ?? '-')];

        $y += 18;
        foreach ($rows as [$label, $value]) {
            imagestring($image, 3, 24, $y, $label . ':', $gray);
            imagestring($image, 4, 240, $y - 1, mb_strimwidth($value, 0, 56, '...'), $black);
            $y += 28;
        }

        $y += 8;
        imageline($image, 24, $y, $width - 24, $y, $gray);
        $y += 18;
        imagestring($image, 4, 24, $y, 'Transaksi berhasil diposting.', $green);

        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);

        if ($binary === false) {
            return null;
        }

        $directory = 'saving-transactions/receipts/' . now()->format('Y-m');
        $filename = 'struk-withdrawal-' . $transaction->id . '.png';
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    /**
     * Generate unique transaction code
     */
    private function generateTransactionCode(): string
    {
        $date = Carbon::now()->format('d');
        $prefix = 'W' . $date;

        $latestTransaction = SavingTransaction::where('transaction_type', TransactionTypeEnum::WITHDRAWAL->value)
            ->whereDate('created_at', Carbon::today())
            ->where('saving_transaction_code', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('saving_transaction_code')
            ->first();

        $lastNumber = 0;
        if ($latestTransaction) {
            preg_match('/(\d{4})$/', (string) $latestTransaction->saving_transaction_code, $matches);
            $lastNumber = (int) ($matches[1] ?? 0);
        }

        return $prefix . str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
    }
}
