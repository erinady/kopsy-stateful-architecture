<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Str;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil user yang ada
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('Tidak ada user. Jalankan UserSeeder dulu.');
            return;
        }

        $banks = [
            'Bank Syariah Indonesia',
            'Bank Mandiri',
            'BNI',
            'BRI',
            'BCA',
        ];

        foreach ($users as $user) {
            // Setiap user punya 1–2 rekening
            $totalAccount = rand(1, 2);

            for ($i = 0; $i < $totalAccount; $i++) {
                Account::create([
                    'user_id' => $user->id,
                    'account_number' => $this->generateAccountNumber(),
                    'account_name' => $user->name,
                    'bank_name' => $banks[array_rand($banks)],
                ]);
            }
        }
    }

    private function generateAccountNumber(): string
    {
        return '1' . rand(1000000000, 9999999999);
    }
}
