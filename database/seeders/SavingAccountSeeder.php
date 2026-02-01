<?php

namespace Database\Seeders;

use App\Models\SavingAccount;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SavingAccountSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'anggota@example.com')->first();

        foreach (['Simpanan Pokok', 'Simpanan Wajib', 'Simpanan Sukarela'] as $type) {
            SavingAccount::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'type' => $type,
                'balance' => rand(500000, 2000000),
            ]);
        }
    }
}