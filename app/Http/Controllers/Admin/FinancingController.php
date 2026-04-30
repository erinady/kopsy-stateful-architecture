<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ConditionEnum;
use App\Enums\EducationEnum;
use App\Enums\FinancialCategoryEnum;
use App\Enums\FinancialCostEnum;
use App\Enums\FinancialIncomeEnum;
use App\Enums\FinancingReqStatusEnum;
use App\Enums\HeirEnum;
use App\Enums\InstallmentPaymentScheduleStatusEnum;
use App\Enums\MaritalStatusEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFinancingRequest;
use App\Models\Financing;
use App\Models\Member;
use App\Models\Supplier;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancingController extends Controller
{
    private function baseQuery(Request $request)
    {
        $search = $request->input('search');
        $tab = $request->input('tab', 'all');

        return Financing::with([
            'member.user' => function ($query) {
                $query->select('id', 'name', 'user_code');
            },
            'installment' => function ($query) {
                $query->withCount([
                    'paymentSchedules' => function ($q) {
                        $q->where('installment_schedule_status', '!=', InstallmentPaymentScheduleStatusEnum::PAID->value);
                    }
                ]);
            },
            'financingItem.productType' => function ($query) {
                $query->select('product_types.id', 'product_types.product_type_name');
            }
        ])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('member.user', function ($userQuery) use ($search) {
                    $userQuery->where(function ($userSearchQuery) use ($search) {
                        $userSearchQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('user_code', 'like', "%{$search}%");
                    });
                });
            })
            ->when($tab === 'request', function ($q) {
                $q->whereIn('financing_status', [
                    FinancingReqStatusEnum::WAITING_DOCUMENTS->value,
                    FinancingReqStatusEnum::PENDING_REVIEW->value,
                ]);
            })
            ->when($tab === 'paid_early_request', function ($q) {
                $q->where(
                    'financing_status',
                    FinancingReqStatusEnum::PAID_EARLY_REQUESTED->value,
                );
            })
            ->when($tab === 'active', function ($q) {
                $q->where(
                    'financing_status',
                    FinancingReqStatusEnum::ACTIVE_INSTALLMENTS->value,
                );
            });
    }
    /**
     * Get common dropdown data
     */
    private function getCommonData(): array
    {
        return [
            'educations' => array_column(EducationEnum::cases(), 'value'),
            'marriageStatuses' => array_column(MaritalStatusEnum::cases(), 'value'),
            'incomes' => array_column(FinancialIncomeEnum::cases(), 'value'),
            'expenses' => array_column(FinancialCostEnum::cases(), 'value'),
            'relationships' => array_column(HeirEnum::cases(), 'value'),
            'conditions' => array_column(ConditionEnum::cases(), 'value'),
            'productTypes' => DB::table('product_types')->select('id', 'product_type_name')->get(),
        ];
    }

    /**
     * Format member data untuk response
     */
    private function formatMemberData(Member $member): array
    {
        $financials = $member->financials ?? collect();
        $job = $member->memberJobs;

        return [
            'id' => $member->id,
            'user_code' => $member->user->user_code,
            'name' => $member->user->name,
            'email' => $member->user->email,
            'nik' => $member->user->nik,
            'phone_number' => $member->user->phone_number,
            'gender' => $member->gender,
            'birth_place' => $member->birth_place,
            'birth_date' => $member->birth_date,
            'marital_status' => $member->marital_status,
            'last_education' => $member->last_education,
            'dependents' => $member->dependents,
            'domicile_address' => $member->domicile_address,
            'residential_address' => $member->residential_address,
            'job_title' => $job?->job_title,
            'company_or_business_name' => $job?->company_or_business_name,
            'business_field' => $job?->business_field,
            'tenure_year' => $job?->tenure_year,
            'workplace_address' => $job?->workplace_address,
            'workplace_contact' => $job?->workplace_contact,
            'incomes' => $financials->where('category', FinancialCategoryEnum::INCOME->value)->map(fn($f) => [
                'financial_type' => $f->financial_type,
                'amount' => $f->amount,
            ])->values(),
            'expenses' => $financials->where('category', FinancialCategoryEnum::EXPENSE->value)->map(fn($f) => [
                'financial_type' => $f->financial_type,
                'amount' => $f->amount,
            ])->values(),
            'heirs' => $member->heirs->map(fn($h) => [
                'heir_nik' => $h->heir_nik,
                'heir_name' => $h->heir_name,
                'relationship' => $h->relationship,
                'heir_contact' => $h->heir_contact,
            ])->values(),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $tab = $request->input('tab', 'all');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $query = $this->baseQuery($request)->orderBy($sortBy, $sortDir);

        $financings = $query
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($f) {
                return [
                    'id' => $f->id,
                    'financing_transaction_code' => $f->financing_transaction_code,
                    'akad_date' => $f->akad_date,
                    'user' => $f->member->user
                        ? ($f->member->user->user_code . ' - ' . $f->member->user->name)
                        : '-',
                    'tenor_left' => $f->installment?->payment_schedules_count ?? 0,
                    'product_type' => $f->financingItem?->productType?->product_type_name,
                    'financing_status' => $f->financing_status,
                ];
            });

        $summary = [
            ['title' => 'Total Pengajuan Pembiayaan Murabahah', 'value' => Financing::whereIn('financing_status', [
                FinancingReqStatusEnum::WAITING_DOCUMENTS->value,
                FinancingReqStatusEnum::PENDING_REVIEW->value,
            ])->count()],
            ['title' => 'Total Pembiayaan Berlangsung', 'value' => Financing::where('financing_status', FinancingReqStatusEnum::ACTIVE_INSTALLMENTS->value)->count()],
            ['title' => 'Total Pengajuan Pelunasan Sebelum Jatuh Tempo', 'value' => Financing::where('financing_status', FinancingReqStatusEnum::PAID_EARLY_REQUESTED->value)->count()],
        ];

        return inertia('Admin/Financing/Index', [
            'financings' => $financings,
            'summary' => $summary,
            'filters' => compact('search', 'perPage', 'tab', 'sortBy', 'sortDir'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Admin/Financing/Create', [
            'data' => $this->getCommonData(),
        ]);
    }

    /**
     * Load draft financing
     */
    public function loadDraft(string $id)
    {
        $financing = Financing::where('id', $id)
            ->where('financing_status', FinancingReqStatusEnum::WAITING_DOCUMENTS->value)
            ->with([
                'member.user',
                'member.financials',
                'member.memberDocs',
                'member.heirs',
                'member.memberJobs',
                'financingItem.productType',
                'financingItem.supplier',
            ])
            ->first();

        if (!$financing) {
            return inertia('Admin/Financing/Create', [
                'data' => $this->getCommonData(),
                'financing' => null,
            ]);
        }

        return inertia('Admin/Financing/Create', [
            'data' => $this->getCommonData(),
            'financing' => [
                'member' => $this->formatMemberData($financing->member),
                'financing' => [
                    'name' => $financing->financingItem->name,
                    'product_type_id' => $financing->financingItem->product_type_id,
                    'brand' => $financing->financingItem->brand,
                    'condition' => $financing->financingItem->condition,
                    'qty' => $financing->financingItem->qty,
                    'request_description' => $financing->financingItem->request_description,
                    'cost_price' => $financing->financingItem->cost_price,
                    'margin_amount' => $financing->financingItem->margin_amount,
                    'supplier_id' => $financing->financingItem->supplier_id,
                    'down_payment' => $financing->down_payment,
                    'is_wakalah' => $financing->is_wakalah,
                    'payment_method' => $financing->payment_method,
                    'akad_date' => $financing->akad_date,
                    'notes' => $financing->notes,
                ],
                'documents' => [
                    'family_card' => $financing->member->memberDocs->where('doc_name', 'kartu_keluarga')->first()?->doc_attachment,
                    'income_slip' => $financing->member->memberDocs->where('doc_name', 'slip_gaji')->first()?->doc_attachment,
                    'bank_book' => $financing->member->memberDocs->where('doc_name', 'buku_tabungan')->first()?->doc_attachment,
                    'down_payment_proof' => $financing->member->memberDocs->where('doc_name', 'down_payment_proof')->first()?->doc_attachment,
                    'purchase_receipt' => $financing->financingItem->purchase_receipt,
                    'akad_document' => $financing->signed_akad_document,
                    'collateral_proof' => $financing->collateral?->collateral_proof,
                ],
                'supplier' => $financing->financingItem->supplier ? [
                    'supplier_name' => $financing->financingItem->supplier->supplier_name,
                    'contact' => $financing->financingItem->supplier->contact,
                    'address' => $financing->financingItem->supplier->address,
                    'website_url' => $financing->financingItem->supplier->website_url,
                ] : null,
            ],
        ]);
    }

    /**
     * Store draft financing
     */
    public function storeDraft(StoreFinancingRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $user = User::with('member')->where('user_code', $validated['member']['user_code'])->firstOrFail();
            $verifier = auth()->user();

            // Update user
            $user->update([
                'name' => $validated['member']['name'],
                'nik' => $validated['member']['nik'],
                'email' => $validated['member']['email'] ?? $user->email,
                'phone_number' => $validated['member']['phone_number'] ?? $user->phone_number,
            ]);

            // Update member
            $user->member->update([
                'gender' => $validated['member']['gender'] ?? $user->member->gender,
                'birth_place' => $validated['member']['birth_place'] ?? $user->member->birth_place,
                'birth_date' => $validated['member']['birth_date'] ?? $user->member->birth_date,
                'last_education' => $validated['member']['last_education'] ?? $user->member->last_education,
                'domicile_address' => $validated['member']['domicile_address'] ?? $user->member->domicile_address,
                'residential_address' => $validated['member']['residential_address'] ?? $user->member->residential_address,
                'marital_status' => $validated['member']['marital_status'] ?? $user->member->marital_status,
                'dependents' => $validated['member']['dependents'] ?? $user->member->dependents,
            ]);

            // Sync heirs
            $user->member->heirs()->delete();
            if (!empty($validated['member']['heirs'] ?? [])) {
                $user->member->heirs()->createMany($validated['member']['heirs']);
            }

            // Sync documents
            $documents = [
                'kartu_keluarga' => 'family_card_file',
                'slip_gaji' => 'income_slip_file',
                'buku_tabungan' => 'bank_book_file',
            ];
            foreach ($documents as $docName => $fileField) {
                if ($request->hasFile($fileField)) {
                    $user->member->memberDocs()->updateOrCreate(
                        ['doc_name' => $docName],
                        ['doc_attachment' => $request->file($fileField)->store('documents', 'public')]
                    );
                }
            }

            // Sync financials
            $user->member->financials()->delete();
            if (!empty($validated['member']['incomes'] ?? [])) {
                foreach ($validated['member']['incomes'] as $income) {
                    $user->member->financials()->create([
                        'financial_type' => $income['financial_type'],
                        'amount' => $income['amount'],
                        'category' => FinancialCategoryEnum::INCOME->value,
                    ]);
                }
            }
            if (!empty($validated['member']['expenses'] ?? [])) {
                foreach ($validated['member']['expenses'] as $expense) {
                    $user->member->financials()->create([
                        'financial_type' => $expense['financial_type'],
                        'amount' => $expense['amount'],
                        'category' => FinancialCategoryEnum::EXPENSE->value,
                    ]);
                }
            }

            // Sync job
            $user->member->memberJobs()->delete();
            $user->member->memberJobs()->create([
                'job_title' => $validated['member']['job_title'] ?? null,
                'company_or_business_name' => $validated['member']['company_or_business_name'] ?? null,
                'business_field' => $validated['member']['business_field'] ?? null,
                'tenure_year' => $validated['member']['tenure_year'] ?? null,
                'workplace_address' => $validated['member']['workplace_address'] ?? null,
                'workplace_contact' => $validated['member']['workplace_contact'] ?? null,
            ]);

            // Create/update financing
            $financing = Financing::updateOrCreate(
                ['member_id' => $user->member->id, 'financing_status' => FinancingReqStatusEnum::WAITING_DOCUMENTS->value],
                [
                    'down_payment' => $validated['financing']['down_payment'] ?? 0,
                    'akad_date' => $validated['financing']['akad_date'] ?? null,
                    'is_wakalah' => $validated['financing']['is_wakalah'] ?? null,
                    'payment_method' => $validated['financing']['payment_method'] ?? null,
                    'updated_by' => $verifier->id,
                    'financing_status' => FinancingReqStatusEnum::WAITING_DOCUMENTS->value,
                ]
            );

            // Update financing item
            $financing->financingItem()->updateOrCreate(
                ['financing_id' => $financing->id],
                [
                    'name' => $validated['financing']['name'] ?? null,
                    'brand' => $validated['financing']['brand'] ?? null,
                    'request_description' => $validated['financing']['request_description'] ?? null,
                    'qty' => $validated['financing']['qty'] ?? null,
                    'condition' => $validated['financing']['condition'] ?? null,
                    'cost_price' => $validated['financing']['cost_price'] ?? null,
                    'margin_amount' => $validated['financing']['margin_amount'] ?? null,
                    'product_type_id' => $validated['financing']['product_type_id'] ?? null,
                    'supplier_id' => $validated['financing']['supplier_id'] ?? null,
                    'purchase_receipt' => $request->hasFile('purchase_receipt_file')
                        ? $request->file('purchase_receipt_file')->store('documents', 'public')
                        : null,
                ]
            );

            DB::commit();

            return back()->with('success', 'Draft berhasil disimpan');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error storing draft: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal menyimpan draft: ' . $e->getMessage()]);
        }
    }

    /**
     * Search members dengan optimized query
     */
    public function searchMembers(Request $request)
    {
        $query = $request->get('q');

        if (strlen($query) < 2) {
            return response()->json(['members' => []]);
        }

        $members = Member::query()
            ->with(['user:id,user_code,name,email,nik,phone_number,role_id', 'memberDocs', 'financials', 'heirs', 'memberJobs'])
            ->whereHas('user', function ($q) use ($query) {
                $q->whereHas('role', fn($roleQ) => $roleQ->where('role_name', 'Anggota'))
                    ->where('status', UserStatusEnum::ACTIVE->value)
                    ->where(function ($searchQ) use ($query) {
                        $searchQ->where('name', 'ILIKE', "%{$query}%")
                            ->orWhere('user_code', 'ILIKE', "%{$query}%");
                    });
            })
            ->limit(5)
            ->get()
            ->map(fn($member) => $this->formatMemberData($member));

        return response()->json(['members' => $members]);
    }

    /**
     * Search suppliers
     */
    public function searchSuppliers(Request $request)
    {
        $query = $request->get('q');

        if (strlen($query) < 2) {
            return response()->json(['suppliers' => []]);
        }

        $suppliers = Supplier::query()
            ->where('supplier_name', 'ILIKE', "%{$query}%")
            ->select('id', 'supplier_name', 'contact', 'address', 'website_url')
            ->limit(5)
            ->get();

        return response()->json(['suppliers' => $suppliers]);
    }
}
