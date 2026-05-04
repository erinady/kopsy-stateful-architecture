<?php

use App\Models\User;

// 61, 58
test('Aplikasi harus secara otomatis membuat nomor anggota yang unik untuk setiap anggota yang berhasil didaftarkan, sesuai dengan standar penomoran koperasi yang berlaku', function () {
    // unit
    $anggota1 = User::factory()->create();
    $anggota2 = User::factory()->create();

    expect($anggota1->user_code)->toMatch('/^MBR\d{6}$/');
    expect($anggota2->user_code)->toMatch('/^MBR\d{6}$/');
    expect($anggota1->user_code)->not()->toEqual($anggota2->user_code);
});
