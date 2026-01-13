<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Loan;
use App\Models\User;
use App\Enums\UserStatus;
use App\Models\Financing;
use Illuminate\Http\Request;
use App\Models\SavingAccount;
use App\Models\SavingTransaction;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(Request $req)
    {
        $startDate = Carbon::parse($req->start_date ?? now()->startOfMonth());
        $endDate = Carbon::parse($req->end_date ?? now()->endOfMonth());
        $filterBy = $req->filter_by ?? 'month';

        // Get previous period dates
        [$prevStartDate, $prevEndDate] = $this->getPreviousPeriod($startDate, $endDate, $filterBy);

        return inertia('Admin/Dashboard', [
            'active_user_count' => User::where('status', UserStatus::ACTIVE->value)
                ->where('created_at', '<=', $endDate)->count(),
            'active_user_percentage' => $this->calculatePercentage(
                User::where('status', UserStatus::ACTIVE->value)->where('created_at', '<=', $endDate)->count(),
                User::where('status', UserStatus::ACTIVE->value)->where('created_at', '<=', $prevEndDate)->count()
            ),
            'total_saving_amount' => SavingAccount::sum('balance') ?? 0,
            'total_financing_amount' => Loan::whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_price') ?? 0,
            'total_financing_percentage' => $this->calculatePercentage(
                Loan::whereBetween('created_at', [$startDate, $endDate])->sum('total_price') ?? 0,
                Loan::whereBetween('created_at', [$prevStartDate, $prevEndDate])->sum('total_price') ?? 0
            ),
            'transaction_data' => $this->getRecentTransactions(),
            'registration_data' => $this->getPendingRegistrations(),
            'financing_data' => $this->getRecentFinancings(),
            'financing_stats' => $this->getFinancingStats(),
        ]);
    }

    private function getPreviousPeriod(Carbon $start, Carbon $end, string $filterBy): array
    {
        return match($filterBy) {
            'month' => [
                $start->copy()->subMonth()->startOfMonth(),
                $start->copy()->subMonth()->endOfMonth()
            ],
            'year' => [
                $start->copy()->subYear()->startOfYear(),
                $start->copy()->subYear()->endOfYear()
            ],
            default => [
                $start->copy()->subDay(),
                $start->copy()->subDay()
            ],
        };
    }

    private function calculatePercentage($current, $previous): int
    {
        return $previous == 0 ? 0 : round((($current - $previous) / $previous) * 100);
    }

    private function getRecentTransactions()
    {
        return SavingTransaction::with('savingAccount.user')
            ->latest()->take(5)->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'user_name' => $t->savingAccount->user->name,
                'amount' => $t->amount,
                'type' => $t->type,
                'created_at' => $t->created_at->toDateTimeString(),
            ]);
    }

    private function getPendingRegistrations()
    {
        return User::with('workUnit')
            ->where('status', UserStatus::INREVIEW->value)
            ->latest()->take(5)->get()
            ->map(fn($u) => [
                'name' => $u->name,
                'email' => $u->email,
                'created_at' => $u->created_at,
                'work_unit' => $u->workUnit?->name ?? '-',
            ]);
    }

    private function getRecentFinancings()
    {
        return Financing::with('user')
            ->latest()->take(5)->get()
            ->map(fn($f) => [
                'id' => $f->id,
                'product_type' => $f->product_type,
                'status' => $f->status,
                'member_number' => $f->user->id,
                'user_name' => $f->user->name,
                'created_at' => $f->created_at,
            ]);
    }

    private function getFinancingStats()
    {
        return Financing::selectRaw('EXTRACT(MONTH FROM created_at) AS month, COUNT(*) AS count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();
    }
}
