<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Education;
use App\Enums\FinancialCost;
use App\Enums\FinancialIncome;
use App\Enums\LoanPaymentScheduleStatus;
use App\Enums\MaritalStatus;
use App\Http\Controllers\Controller;
use App\Models\Financing;
use Illuminate\Http\Request;

class FinancingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $educations = array_column(Education::cases(), 'value');
        $marriageStatuses = array_column(MaritalStatus::cases(), 'value');
        $incomes = array_column(FinancialIncome::cases(), 'value');
        $costs = array_column(FinancialCost::cases(), 'value');

        return inertia('Admin/Financing/Create', [
            'educations' => $educations,
            'marriageStatuses' => $marriageStatuses,
            'incomes' => $incomes,
            'costs' => $costs,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $financing = Financing::with(['loan', 'loan.paymentSchedules.payment'])->findOrFail($id);
        $financing->total_price = $financing->cost_price + $financing->margin - $financing->down_payment;

        $loan = $financing->loan;
        if ($loan !== null) {
            $financing->total_paid = $loan->paymentSchedules
                ->where('status', LoanPaymentScheduleStatus::PAID->value)
                ->sum('total_amount');
            $financing->remaining_balance = $loan->remaining_margin + $loan->remaining_principal;
        } else {
            $financing->total_paid = 0;
            $financing->remaining_balance = 0;
        }

        return inertia('Admin/Financing/Show', [
            'data' => $financing
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
