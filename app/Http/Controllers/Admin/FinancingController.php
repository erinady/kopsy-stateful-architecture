<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LoanPaymentScheduleStatus;
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
        //
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
        $financing->total_paid = $financing->loan->paymentSchedules
            ->where('status', LoanPaymentScheduleStatus::PAID->value)
            ->sum('total_amount');
        $financing->remaining_balance = $financing->loan->remaining_margin + $financing->loan->remaining_principal;

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
