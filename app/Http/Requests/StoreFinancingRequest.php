<?php

namespace App\Http\Requests;

use App\Enums\EducationEnum;
use App\Enums\GenderEnum;
use App\Enums\MaritalStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreFinancingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Jika draft (partial save) - semua nullable
        if ($this->routeIs('admin.financing.storeDraft')) {
            return $this->draftRules();
        }

        // Jika submit penuh - required fields
        return $this->submitRules();
    }

    private function draftRules(): array
    {
        return [
            // Member data
            'member.user_code' => 'nullable|string|max:255',
            'member.name' => 'nullable|string|max:255',
            'member.nik' => 'nullable|string|digits:16',
            'member.phone_number' => 'nullable|string|max:20',
            'member.email' => 'nullable|email|max:255',
            'member.birth_place' => 'nullable|string|max:255',
            'member.birth_date' => 'nullable|date',
            'member.gender' => 'nullable|in:' . implode(',', array_column(GenderEnum::cases(), 'value')),
            'member.marital_status' => 'nullable|in:' . implode(',', array_column(MaritalStatusEnum::cases(), 'value')),
            'member.last_education' => 'nullable|in:' . implode(',', array_column(EducationEnum::cases(), 'value')),
            'member.domicile_address' => 'nullable|string|max:500',
            'member.residential_address' => 'nullable|string|max:500',
            'member.dependents' => 'nullable|integer|min:0',
            'member.job_title' => 'nullable|string|max:255',
            'member.company_or_business_name' => 'nullable|string|max:255',
            'member.business_field' => 'nullable|string|max:500',
            'member.tenure_year' => 'nullable|integer|min:0',
            'member.workplace_address' => 'nullable|string|max:500',
            'member.workplace_contact' => 'nullable|string|max:20',
            'member.heirs.*.heir_name' => 'nullable|string|max:255',
            'member.heirs.*.heir_nik' => 'nullable|string|digits:16',
            'member.heirs.*.relationship' => 'nullable|string|max:255',
            'member.heirs.*.heir_contact' => 'nullable|string|max:20',
            'member.incomes.*.financial_type' => 'nullable|string|max:255',
            'member.incomes.*.amount' => 'nullable|numeric|min:0',
            'member.expenses.*.financial_type' => 'nullable|string|max:255',
            'member.expenses.*.amount' => 'nullable|numeric|min:0',

            // Financing data
            'financing.name' => 'nullable|string|max:255',
            'financing.product_type_id' => 'nullable|exists:product_types,id',
            'financing.brand' => 'nullable|string|max:255',
            'financing.condition' => 'nullable|string|max:255',
            'financing.qty' => 'nullable|integer|min:1',
            'financing.request_description' => 'nullable|string|max:1000',
            'financing.cost_price' => 'nullable|numeric|min:0',
            'financing.margin_amount' => 'nullable|numeric|min:0',
            'financing.is_wakalah' => 'nullable|boolean',
            'financing.payment_method' => 'nullable|string|max:255',
            'financing.akad_date' => 'nullable|date',
            'financing.down_payment' => 'nullable|numeric|min:0',
            'financing.notes' => 'nullable|string|max:1000',

            // Supplier data
            'supplier.supplier_name' => 'nullable|string|max:255',
            'supplier.contact' => 'nullable|string|max:255',
            'supplier.address' => 'nullable|string|max:500',
            'supplier.website_url' => 'nullable|url|max:255',

            // File uploads
            'family_card_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'income_slip_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'bank_book_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'down_payment_proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'procurement_proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'akad_document_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ];
    }

    private function submitRules(): array
    {
        return [
            // Member data - REQUIRED saat submit
            'member.user_code' => 'required|string|max:255',
            'member.name' => 'required|string|max:255',
            'member.nik' => 'required|string|digits:16',
            'member.phone_number' => 'required|string|max:20',
            'member.email' => 'required|email|max:255',
            'member.birth_place' => 'required|string|max:255',
            'member.birth_date' => 'required|date',
            'member.gender' => 'required|in:' . implode(',', array_column(GenderEnum::cases(), 'value')),
            'member.marital_status' => 'required|in:' . implode(',', array_column(MaritalStatusEnum::cases(), 'value')),
            'member.last_education' => 'required|in:' . implode(',', array_column(EducationEnum::cases(), 'value')),
            'member.residential_address' => 'required|string|max:500',
            'member.dependents' => 'required|integer|min:0',
            'member.job_title' => 'required|string|max:255',
            'member.company_or_business_name' => 'nullable|string|max:255',
            'member.business_field' => 'nullable|string|max:500',
            'member.tenure_year' => 'nullable|integer|min:0',
            'member.workplace_address' => 'nullable|string|max:500',
            'member.workplace_contact' => 'nullable|string|max:20',

            // Heirs - minimal 1
            'member.heirs' => 'required|array|min:1',
            'member.heirs.*.heir_name' => 'required|string|max:255',
            'member.heirs.*.heir_nik' => 'required|string|digits:16',
            'member.heirs.*.relationship' => 'required|string|max:255',
            'member.heirs.*.heir_contact' => 'required|string|max:20',

            // Financial - minimal 1 income & expense
            'member.incomes' => 'required|array|min:1',
            'member.incomes.*.financial_type' => 'required|string|max:255',
            'member.incomes.*.amount' => 'required|numeric|min:0',
            'member.expenses' => 'required|array|min:1',
            'member.expenses.*.financial_type' => 'required|string|max:255',
            'member.expenses.*.amount' => 'required|numeric|min:0',

            // Financing - REQUIRED
            'financing.name' => 'required|string|max:255',
            'financing.product_type_id' => 'required|exists:product_types,id',
            'financing.qty' => 'required|integer|min:1',
            'financing.condition' => 'nullable|string|max:255',
            'financing.brand' => 'nullable|string|max:255',
            'financing.request_description' => 'nullable|string|max:1000',
            'financing.cost_price' => 'required|numeric|min:0',
            'financing.margin_amount' => 'required|numeric|min:0',
            'financing.is_wakalah' => 'required|boolean',
            'financing.payment_method' => 'required|string|max:255',
            'financing.akad_date' => 'required|date',
            'financing.down_payment' => 'nullable|numeric|min:0',
            'financing.notes' => 'nullable|string|max:1000',

            // Supplier - REQUIRED
            'supplier.supplier_name' => 'required|string|max:255',
            'supplier.contact' => 'nullable|string|max:255',
            'supplier.address' => 'nullable|string|max:500',
            'supplier.website_url' => 'nullable|url|max:255',

            // File uploads - REQUIRED saat submit
            'family_card_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'income_slip_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'bank_book_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'down_payment_proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'procurement_proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'akad_document_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ];
    }

    public function messages(): array
    {
        return [
            'member.user_code.required' => 'Kode member wajib dipilih',
            'member.name.required' => 'Nama lengkap wajib diisi',
            'member.nik.required' => 'NIK wajib diisi',
            'member.nik.digits' => 'NIK harus 16 digit',
            'member.heirs.required' => 'Minimal tambahkan 1 ahli waris',
            'member.heirs.*.heir_name.required' => 'Nama ahli waris wajib diisi',
            'member.incomes.required' => 'Minimal tambahkan 1 sumber penghasilan',
            'member.incomes.*.financial_type.required' => 'Jenis penghasilan wajib diisi',
            'member.expenses.required' => 'Minimal tambahkan 1 jenis pengeluaran',
            'member.expenses.*.financial_type.required' => 'Jenis pengeluaran wajib diisi',
            'financing.name.required' => 'Nama produk wajib diisi',
            'financing.product_type_id.required' => 'Kategori produk wajib dipilih',
            'financing.product_type_id.exists' => 'Kategori produk tidak valid',
            'financing.qty.required' => 'Jumlah barang wajib diisi',
            'financing.cost_price.required' => 'Harga pokok wajib diisi',
            'financing.margin_amount.required' => 'Margin wajib diisi',
            'supplier.supplier_name.required' => 'Nama supplier wajib diisi',
            'family_card_file.required' => 'Dokumen kartu keluarga wajib diupload',
            'income_slip_file.required' => 'Dokumen slip gaji wajib diupload',
            'bank_book_file.required' => 'Dokumen buku tabungan wajib diupload',
        ];
    }
}
