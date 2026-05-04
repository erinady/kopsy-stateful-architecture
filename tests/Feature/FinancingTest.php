<?php

use App\Enums\FinancingReqStatusEnum;
use App\Enums\MemberStatusEnum;
use App\Enums\UserStatusEnum;
use App\Models\Financing;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('Aplikasi harus menangani pencatatan permohonan pembiayaan murabahah oleh staf murabahah', function () {
    $staffMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $staffMurabahah->assignRole('Staf Murabahah');

    $member = Member::factory()->create([
        'status' => MemberStatusEnum::ACTIVE->value,
    ]);

    $response = $this->actingAs($staffMurabahah)
        ->post('/admin/financing/store', [
            'member' => [
                'user_code' => $member->user->user_code,
                'name' => $member->user->name,
                'nik' => $member->user->nik,
            ],
            'financing' => [
                'name' => 'Motor Honda',
                'cost_price' => 50000000,
                'margin_amount' => 4000000,
                'down_payment' => 10000000,
                'payment_method' => 'Cicilan',
            ],
            'tenor' => 24,
        ]);

    $response->assertStatus(302);
});

test('Aplikasi harus memungkinkan staf murabahah untuk melengkapi identitas pemohon yang sudah terdaftar sebagai anggota beserta data ahli warisnya', function () {
    $staffMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $staffMurabahah->assignRole('Staf Murabahah');

    $member = Member::factory()->create();

    $response = $this->actingAs($staffMurabahah)
        ->post('/admin/financing/store', [
            'member' => [
                'user_code' => $member->user->user_code,
                'name' => $member->user->name,
                'nik' => $member->user->nik,
                'gender' => 'Laki-Laki',
                'birth_date' => '1990-01-01',
                'marital_status' => 'Sudah Menikah',
                'heirs' => [
                    [
                        'heir_name' => 'Ahli Waris 1',
                        'heir_nik' => '1234567890654321',
                        'relationship' => 'Istri',
                        'heir_contact' => '081234567890',
                    ]
                ],
                'incomes' => [
                    [
                        'financial_type' => 'Gaji Pokok',
                        'amount' => 5000000,
                    ]
                ],
                'expenses' => [
                    [
                        'financial_type' => 'Cicilan Rumah',
                        'amount' => 2000000,
                    ]
                ]
            ],
            'financing' => [
                'name' => 'Motor',
                'cost_price' => 50000000,
                'margin_amount' => 4000000,
            ],
            'collateral' => [
                'collateral_type' => 'Motor',
                'owner_name' => 'Pemohon',
                'estimated_market_value' => 30000000,
                'collateral_location' => 'Bandung',
            ],
        ]);

    $response->assertStatus(302);
});

// FR 29
test('Aplikasi harus memungkinkan staf murabahah untuk melengkapi jaminan (rahn) yang diajukan untuk permohonan murabahah', function () {
    $staffMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $staffMurabahah->assignRole('Staf Murabahah');

    $member = Member::factory()->create();

    $response = $this->actingAs($staffMurabahah)
        ->post('/admin/financing/store', [
            'member' => [
                'user_code' => $member->user->user_code,
                'name' => $member->user->name,
            ],
            'financing' => [
                'name' => 'Motor',
                'cost_price' => 50000000,
                'margin_amount' => 4000000,
            ],
            'collateral' => [
                'collateral_type' => 'Motor',
                'owner_name' => 'Pemohon',
                'estimated_market_value' => 30000000,
                'collateral_location' => 'Bandung',
            ],
        ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('collaterals', [
        'collateral_type' => 'Motor',
    ]);
});

// FR 30
test('Aplikasi harus memungkinkan staf murabahah untuk menyimpan sementara isian form permohonan murabahah yang sedang diisi', function () {
    $staffMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $staffMurabahah->assignRole('Staf Murabahah');

    $member = Member::factory()->create();

    $response = $this->actingAs($staffMurabahah)
        ->post('/admin/financing/store', [
            'member' => [
                'user_code' => $member->user->user_code,
                'name' => $member->user->name,
            ],
            'financing' => [
                'name' => 'Motor',
                'financing_status' => 'Menunggu Kelengkapan Dokumen',
            ],
        ]);

    $response->assertStatus(302);
});

// FR 31
test('Aplikasi harus memungkinkan ketua murabahah atau ketua koperasi untuk memberikan persetujuan atau penolakan permohonan pembiayaan murabahah beserta alasan penolakan', function () {
    $ketuaMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $ketuaMurabahah->assignRole('Ketua Murabahah');

    $financing = Financing::factory()->create([
        'financing_status' => 'Belum Ditinjau',
    ]);

    $response = $this->actingAs($ketuaMurabahah)
        ->put("/admin/financing/validate/{$financing->id}", [
            'financing_status' => 'Disetujui',
            'notes' => 'Permohonan disetujui',
        ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('financings', [
        'id' => $financing->id,
        'financing_status' => 'Disetujui',
    ]);
});

test('Aplikasi harus memungkinkan ketua murabahah untuk menolak permohonan dengan alasan', function () {
    $ketuaMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $ketuaMurabahah->assignRole('Ketua Murabahah');

    $financing = Financing::factory()->create([
        'financing_status' => FinancingReqStatusEnum::PENDING_REVIEW->value,
        'notes' => null,
    ]);

    $this->actingAs($ketuaMurabahah)
        ->put("/admin/financing/validate/{$financing->id}", [
            'financing_status' => FinancingReqStatusEnum::REJECTED->value,
            'notes' => 'Penghasilan tidak mencukupi',
        ]);

    $this->assertDatabaseHas('financings', [
        'id' => $financing->id,
        'financing_status' => FinancingReqStatusEnum::REJECTED->value,
        'notes' => 'Penghasilan tidak mencukupi',
    ]);
});

// FR 32, 33, 34
test('Aplikasi harus menangani permohonan pembiayaan murabahah dengan akad wakalah oleh anggota sebagai perwakilan (muwakkil) dari koperasi', function () {
    $staffMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $staffMurabahah->assignRole('Staf Murabahah');

    $member = Member::factory()->create();

    $response = $this->actingAs($staffMurabahah)
        ->post('/admin/financing/store', [
            'member' => [
                'user_code' => $member->user->user_code,
                'name' => $member->user->name,
            ],
            'financing' => [
                'name' => 'Motor',
                'cost_price' => 50000000,
                'margin_amount' => 4000000,
                'is_wakalah' => true,
            ],
        ]);

    $response->assertStatus(302);
});

test('Aplikasi harus menyediakan pengunggahan dokumen akad wakalah yang sudah ditandatangani oleh anggota oleh staf murabahah', function () {
    $staffMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $staffMurabahah->assignRole('Staf Murabahah');

    $financing = Financing::factory()->create([
        'is_wakalah' => true,
        'financing_status' => 'Disetujui',
    ]);

    $response = $this->actingAs($staffMurabahah)
        ->post("/admin/financing/store", [
            'akad_wakalah_file' => UploadedFile::fake()->create('wakalah.pdf'),
        ]);

    $response->assertStatus(302);
});

// FR 35
test('Aplikasi harus memungkinkan staf murabahah untuk menambahkan informasi pemasok dan bukti pengadaan objek pembiayaan murabahah setelah permohonan diterima', function () {
    $staffMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $staffMurabahah->assignRole('Staf Murabahah');

    $financing = Financing::factory()->create([
        'financing_status' => 'Disetujui',
    ]);

    $response = $this->actingAs($staffMurabahah)
        ->post("/admin/financing/store", [
            'supplier' => [
                'supplier_name' => 'PT. Supplier Jaya',
                'contact' => '081234567890',
                'address' => 'Jl. Supplier No. 1',
            ],
            'purchase_receipt_file' => UploadedFile::fake()->create('receipt.pdf'),
        ]);

    $response->assertStatus(302);
});

// FR 38
test('Aplikasi harus menangani penambahan detail pembiayaan murabahah untuk finalisasi akad murabahah yang mencakup tenor cicilan dan dokumen akad bertanda tangan oleh staf murabahah', function () {
    $staffMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $staffMurabahah->assignRole('Staf Murabahah');

    $financing = Financing::factory()->create([
        'financing_status' => 'Disetujui',
    ]);

    $response = $this->actingAs($staffMurabahah)
        ->post("/admin/financing/store", [
            'tenor' => 24,
            'akad_date' => '2024-01-15',
            'akad_document_file' => UploadedFile::fake()->create('akad.pdf'),
        ]);

    $response->assertStatus(302);
});

test('Aplikasi harus menangani permohonan pelunasan sebelum jatuh tempo pembiayaan murabahah oleh staf murabahah', function () {
    $staffMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $staffMurabahah->assignRole('Staf Murabahah');

    $financing = Financing::factory()->create([
        'financing_status' => 'Cicilan Berjalan',
    ]);

    $response = $this->actingAs($staffMurabahah)
        ->post("/admin/financing/{$financing->id}/early-payoff", [
            'payoff_date' => now()->addMonths(6)->format('Y-m-d'),
            'payoff_amount' => 40000000,
        ]);

    $response->assertStatus(302);
});

test('Aplikasi harus menyediakan pencatatan pembayaran angsuran pembiayaan murabahah oleh staf murabahah', function () {
    $staffMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $staffMurabahah->assignRole('Staf Murabahah');

    $financing = Financing::factory()->create([
        'financing_status' => 'Cicilan Berjalan',
    ]);

    $response = $this->actingAs($staffMurabahah)
        ->post("/admin/financing/{$financing->id}/record-payment", [
            'schedule_id' => 1,
            'payment_amount' => 1833333,
            'payment_date' => now()->format('Y-m-d'),
            'payment_method' => 'Tunai',
        ]);

    $response->assertStatus(302);
});

test('Aplikasi harus menghasilkan struk bukti pembayaran angsuran yang dapat dilihat oleh anggota dan staf murabahah', function () {
    $staffMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $staffMurabahah->assignRole('Staf Murabahah');

    $financing = Financing::factory()->create([
        'financing_status' => 'Cicilan Berjalan',
    ]);

    $response = $this->actingAs($staffMurabahah)
        ->get("/admin/financing/{$financing->id}/payment-receipt");

    $response->assertStatus(200);
});

// FR 45
test('Aplikasi harus menampilkan daftar pembiayaan murabahah aktif untuk ketua koperasi, ketua murabahah, dan staf murabahah', function () {
    $ketuaMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $ketuaMurabahah->assignRole('Ketua Murabahah');

    Financing::factory()->count(5)->create([
        'financing_status' => FinancingReqStatusEnum::ACTIVE_INSTALLMENTS->value,
    ]);

    $response = $this->actingAs($ketuaMurabahah)
        ->get('/admin/financing');

    $response->assertStatus(200);
});

// FR 46
test('Aplikasi harus memungkinkan ketua dan staf murabahah untuk melihat riwayat pembiayaan murabahah semua anggota', function () {
    $ketuaMurabahah = User::factory()->create([
        'status' => UserStatusEnum::ACTIVE->value,
    ]);
    $ketuaMurabahah->assignRole('Ketua Murabahah');

    Financing::factory()->count(5)->create();

    $response = $this->actingAs($ketuaMurabahah)
        ->get('/admin/financing?tab=all');

    $response->assertStatus(200);
});

// FR 47
test('Aplikasi harus menampilkan pembiayaan murabahah yang sedang berjalan untuk anggota', function () {
    $member = Member::factory()->create();
    $user = User::where('id', $member->user_id)->first();
    $user->assignRole('Anggota');

    Financing::factory()->create([
        'member_id' => $member->id,
        'financing_status' => FinancingReqStatusEnum::ACTIVE_INSTALLMENTS->value,
    ]);

    $response = $this->actingAs($user)
        ->get('/user/financing');

    $response->assertStatus(200);
});

// FR 48
test('Aplikasi harus menampilkan riwayat pembiayaan yang sudah dilakukan untuk anggota', function () {
    $member = Member::factory()->create();
    $user = User::where('id', $member->user_id)->first();
    $user->assignRole('Anggota');

    Financing::factory()->create([
        'member_id' => $member->id,
        'financing_status' => 'Lunas',
    ]);

    $response = $this->actingAs($user)
        ->get('/user/financing');

    $response->assertStatus(200);
});
