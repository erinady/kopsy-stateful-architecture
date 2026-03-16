<?php

namespace App\Http\Controllers\Auth;

use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use App\Models\UserDoc;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class RegisterController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:users',
            'nama_lengkap' => 'required|string',
            'nik' => 'required|digits:16|unique:users',
            'password' => 'required|min:8|confirmed',
            'foto_pribadi' => 'required|image|max:2048',
            'foto_ktp' => 'required|image|max:2048',
        ]);

        DB::transaction(function () use ($request, $data) {
            $profilePath = $request->file('foto_pribadi')->store('profile', 'public');
            $ktpPath = $request->file('foto_ktp')->store('ktp', 'public');
            $latestMemberNumber = User::max('member_number');
            $nextNumber = $latestMemberNumber
                ? ((int) preg_replace('/\D/', '', $latestMemberNumber)) + 1
                : 1;

            // Get or create User role
            $userRole = Role::firstOrCreate(['name' => 'Anggota']);

            $memberNumber = 'KSP' . $userRole->id . (User::count() + 1);

            $user = User::create([
                'id' => Str::uuid(),
                'member_number' => $memberNumber,
                'name' => $data['nama_lengkap'],
                'email' => $data['email'],
                'nik' => $data['nik'],
                'password' => Hash::make($data['password']),
                'profile_picture' => $profilePath,
                'status' => 'Dalam Peninjauan',
                'role_id' => $userRole->id,
            ]);

            UserDoc::create([
                'name' => 'KTP',
                'attachment' => $ktpPath,
                'user_id' => $user->id,
            ]);
        });

        return redirect()->route('auth.register.success');
    }
}

