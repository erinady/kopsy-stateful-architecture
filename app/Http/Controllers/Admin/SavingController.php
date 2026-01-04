<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionStatus;
use App\Http\Requests\StoreSavingTransactionValidationRequest;
use App\Models\SavingTransaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SavingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
        $data = SavingTransaction::with( 'savingAccount.user.workUnit')->find($id);

        return inertia('Admin/Savings/Show', [
            'data' => $data,
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

    public function validation(string $id)
    {
        $data = SavingTransaction::with( 'savingAccount.user')->where('status', TransactionStatus::PENDING)->find($id);
        return inertia('Admin/Savings/Validate', [
            'data' => $data,
        ]);
    }

    public function validate(StoreSavingTransactionValidationRequest $request, string $id)
    {
        try {
            $data = $request->validated();

            $transaction = SavingTransaction::findOrFail($id);
            if ($data['description'] !== null) {
                $transaction->status = TransactionStatus::REJECTED;
                $transaction->description = $data['description'];
            } else {
                $transaction->status = TransactionStatus::COMPLETED;
            }
            $transaction->save();

            return redirect()->route('admin.savings.validation', ['id' => $id])->with('success', 'Transaksi simpanan berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui transaksi simpanan: ' . $e->getMessage());
        }
    }
}
