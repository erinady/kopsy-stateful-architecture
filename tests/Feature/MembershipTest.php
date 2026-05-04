<?php

use App\Enums\EducationEnum;
use App\Enums\MemberStatusEnum;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);
beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

describe('Aplikasi harus memungkinkan hanya pengguna dengan peran Sekretaris yang dapat menambah data anggota yang sudah terdaftar', function () {
    it('Sekretaris dapat menambah data pengurus', function () {
        $sekretaris = User::factory()->create();
        $sekretaris->assignRole('Sekretaris');
        $responseSekretaris = $this->actingAs($sekretaris)
            ->post('/admin/users/store', [
                'name' => 'Test Member',
                'gender' => 'Laki-laki',
                'birth_place' => 'Bandung',
                'birth_date' => '1990-01-01',
                'marital_status' => 'Kawin',
                'email' => 'test@example.com',
                'password' => 'password',
                'user_code' => 'KSP999',
                'domicile_address' => 'Jl. Test No. 123',
                'last_education' => EducationEnum::DIPLOMA_IV_BACHELOR->value,
                'nik' => '1234567890123456',
                'phone_number' => '081234567890',
                'heir_nik' => '6543210987654321',
                'heir_name' => 'Heir Test',
                'heir_relationship' => 'Saudara',
                'heir_contact' => '081234567891',
            ]);

        // check if status aktif
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'status' => 'Aktif'
        ]);
        $responseSekretaris->assertStatus(302);
    });

    it('Sekretaris tidak dapat mengubah data pengurus jika tidak memiliki peran yang sesuai', function () {
        $anggota = User::factory()->create();
        $anggota->assignRole('Anggota');
        $responseAnggota = $this->actingAs($anggota)
            ->post('/admin/users/store', [
                'name' => 'Test Member',
                'gender' => 'Laki-laki',
                'birth_place' => 'Bandung',
                'birth_date' => '1990-01-01',
                'marital_status' => 'Kawin',
                'email' => 'test@example.com',
                'password' => 'password',
                'user_code' => 'KSP999',
                'domicile_address' => 'Jl. Test No. 123',
                'last_education' => EducationEnum::DIPLOMA_IV_BACHELOR->value,
                'nik' => '1234567890123456',
                'phone_number' => '081234567890',
                'heir_nik' => '6543210987654321',
                'heir_name' => 'Heir Test',
                'heir_relationship' => 'Saudara',
                'heir_contact' => '081234567891',
            ]);

        $responseAnggota->assertStatus(403);
    });
});

describe('Aplikasi harus memungkinkan hanya pengguna dengan peran Sekretaris yang dapat mengubah data pengurus koperasi yang sudah terdaftar', function () {
    it('Sekretaris dapat mengubah data pengurus', function () {
        $sekretaris = User::factory()->create();
        $sekretaris->assignRole('Sekretaris');
        $responseSekretaris = $this->actingAs($sekretaris)
            ->post('/admin/users/store', [
                'name' => 'Test Member',
                'gender' => 'Laki-laki',
                'birth_place' => 'Bandung',
                'birth_date' => '1990-01-01',
                'marital_status' => 'Kawin',
                'email' => 'test@example.com',
                'password' => 'password',
                'user_code' => 'KSP999',
                'domicile_address' => 'Jl. Test No. 123',
                'last_education' => EducationEnum::DIPLOMA_IV_BACHELOR->value,
                'nik' => '1234567890123456',
                'phone_number' => '081234567890',
                'heir_nik' => '6543210987654321',
                'heir_name' => 'Heir Test',
                'heir_relationship' => 'Saudara',
                'heir_contact' => '081234567891',
            ]);

        // check if status aktif
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'status' => 'Aktif'
        ]);
        $responseSekretaris->assertStatus(302);
    });

    it('Sekretaris tidak dapat mengubah data pengurus jika tidak memiliki peran yang sesuai', function () {
        $anggota = User::factory()->create();
        $anggota->assignRole('Anggota');
        $responseAnggota = $this->actingAs($anggota)
            ->post('/admin/users/store', [
                'name' => 'Test Member',
                'gender' => 'Laki-laki',
                'birth_place' => 'Bandung',
                'birth_date' => '1990-01-01',
                'marital_status' => 'Kawin',
                'email' => 'test@example.com',
                'password' => 'password',
                'user_code' => 'KSP999',
                'domicile_address' => 'Jl. Test No. 123',
                'last_education' => EducationEnum::DIPLOMA_IV_BACHELOR->value,
                'nik' => '1234567890123456',
                'phone_number' => '081234567890',
                'heir_nik' => '6543210987654321',
                'heir_name' => 'Heir Test',
                'heir_relationship' => 'Saudara',
                'heir_contact' => '081234567891',
            ]);

        $responseAnggota->assertStatus(403);
    });
});

describe('Aplikasi harus memungkinkan hanya pengguna dengan peran Ketua Koperasi yang dapat memproses pengunduran diri anggota', function () {
    it('Ketua dapat memproses pengunduran diri anggota', function () {
        $ketua = User::factory()->create();
        $anggota = Member::factory()->create([
            'status' => MemberStatusEnum::RESIGNED_REQUESTED->value
        ]);
        $user = User::where('id', $anggota->user_id)->first();
        $user->assignRole('Anggota');
        $ketua->assignRole('Ketua');
        $responseKetua = $this->actingAs($ketua)
            ->put('/admin/resignation/' . $user->id, [
                'status' => 'accept'
            ]);

        $responseKetua->assertStatus(302);
    });

    it('Ketua tidak dapat memproses pengunduran diri anggota jika tidak memiliki peran yang sesuai', function () {
        $anggotaBiasa = Member::factory()->create([
            'status' => MemberStatusEnum::RESIGNED_REQUESTED->value
        ]);
        $user = User::where('id', $anggotaBiasa->user_id)->first();
        $user->assignRole('Anggota');
        $responseAnggota = $this->actingAs($user)
            ->put('/admin/resignation/' . $anggotaBiasa->id);

        $responseAnggota->assertStatus(403);
    });
});

test('Aplikasi harus memastikan bahwa hanya anggota dengan status Aktif yang dapat mengajukan pengunduran diri dari keanggotaan koperasi', function () {
    $anggotaBiasa = Member::factory()->create([
        'status' => MemberStatusEnum::PAYMENT_PENDING->value
    ]);
    $user = User::where('id', $anggotaBiasa->user_id)->first();
    $user->assignRole('Anggota');
    $responseAnggota = $this->actingAs($user)
        ->post('/user/resign', [
            'document' => UploadedFile::fake()->create('resign.pdf')
        ]);

    $responseAnggota->assertSessionHasErrors([
        'resign' => 'Status anggota tidak valid untuk pengajuan pengunduran diri.'
    ]);
});

test('Aplikasi harus secara otomatis membuat nomor anggota yang unik untuk setiap anggota yang berhasil didaftarkan, sesuai dengan standar penomoran koperasi yang berlaku', function () {
    // unit
    $anggota1 = User::factory()->create();
    $anggota1->assignRole('Anggota');
    $anggota2 = User::factory()->create();
    $anggota2->assignRole('Anggota');

    expect($anggota1->user_code)->not()->toBe($anggota2->user_code);
});

test('Aplikasi harus secara otomatis menetapkan status keanggotaan anggota baru menjadi Menunggu Pembayaran setelah proses registrasi selesai dilakukan', function () {
    $sekretaris = User::factory()->create();
    $sekretaris->assignRole('Sekretaris');
    $responseSekretaris = $this->actingAs($sekretaris)
        ->post('/admin/users/store', [
            'name' => 'Test Member',
            'gender' => 'Laki-laki',
            'birth_place' => 'Bandung',
            'birth_date' => '1990-01-01',
            'marital_status' => 'Kawin',
            'email' => 'test@example.com',
            'password' => 'password',
            'domicile_address' => 'Jl. Test No. 123',
            'last_education' => EducationEnum::DIPLOMA_IV_BACHELOR->value,
            'nik' => '1234567890123456',
            'phone_number' => '081234567890',
            'heir_nik' => '6543210987654321',
            'heir_name' => 'Heir Test',
            'heir_relationship' => 'Saudara',
            'heir_contact' => '081234567891',
        ]);

    $responseSekretaris->assertStatus(302);
    // check if has status aktif
    $this->assertDatabaseHas('members', [
        'user_id' => User::where('nik', '1234567890123456')->first()->id,
        'status' => 'Menunggu Pembayaran'
    ]);
});

test('Aplikasi harus memastikan bahwa nomor pengurus non-anggota bersifat unik dan tidak boleh sama dengan nomor anggota maupun nomor pengurus aktif lainnya yang sudah terdaftar', function () {
    $anggota1 = User::factory()->create();
    $anggota2 = User::factory()->create();

    expect($anggota1->user_code)->not()->toBe($anggota2->user_code);
});

test('Aplikasi harus mencegah anggota mengajukan pengunduran diri baru apabila anggota tersebut sudah memiliki pengajuan pengunduran diri yang masih aktif (belum diproses) dalam aplikasi', function () {
    $anggotaBiasa = Member::factory()->create([
        'status' => MemberStatusEnum::ACTIVE->value
    ]);
    $user = User::where('id', $anggotaBiasa->user_id)->first();
    $user->assignRole('Anggota');
    $responseAnggota = $this->actingAs($user)
        ->post('/user/resign', [
            'document' => UploadedFile::fake()->create('resign.pdf')
        ]);

    $responseAnggota->assertSessionHasErrors([
        'resign' => 'Permohonan pengunduran diri sudah pernah diajukan. Anda tidak dapat mengajukan lagi.'
    ]);
});

test('Aplikasi harus memverifikasi bahwa anggota tidak memiliki kewajiban finansial maupun administratif yang belum diselesaikan sebelum mengizinkan pengajuan pengunduran diri', function () {
    $anggotaBiasa = Member::factory()->create();
    $user = User::where('id', $anggotaBiasa->user_id)->first();
    $user->assignRole('Anggota');

    $responseAnggota = $this->actingAs($user)
        ->post('/user/resign', [
            'document' => UploadedFile::fake()->create('resign.pdf')
        ]);

    $responseAnggota->assertStatus(302);
});
