<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;

class AdminController extends Controller
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
        return inertia('Admin/Admins/Create', [
            'title' => 'Create Admin',
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
        $user = [
            'name' => 'Asep Suhendar',
            'nik' => '123456789012345',
            'gender' => 'Laki-laki',
            'birth_date' => '1990-01-01',
            'last_education' => 'STRATA I',
            'work_unit' => 'JTK',
            'institution' => 'Politeknik Negeri Bandung',
            'marital_status' => 'Kawin',
            'spouse_name' => 'Siti Aminah',
            'dependents' => 2,
            'phone_number' => '081234567890',
            'email' => 'user@example.com',
            'address' => 'Jl. Merdeka No. 123, Bandung',
            'residential_address' => 'Jl. Sudirman No. 456, Bandung',
        ];
        $heirs = [
            [
                'name' => 'Siti Aminah',
                'relationship' => 'Istri',
                'contact' => '081298765432',
            ]
        ];
        return inertia('Admin/Admins/Show', [
            'user' => $user,
            'heirs' => $heirs,
            'id' => $id,
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

    public function list(Request $request)
    {
        $admins = User::with('role')
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($qq) use ($request) {
                    $qq->where('name', 'like', "%{$request->search}%")
                       ->orWhere('nik', 'like', "%{$request->search}%")
                       ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->role, function ($q) use ($request) {
                $q->whereHas('role', function ($r) use ($request) {
                    $r->where('name', $request->role);
                });
            })
            ->paginate($request->perPage ?? 10)
            ->withQueryString()
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'nik' => $user->nik,
                    'name' => $user-> name,
                    'email' => $user->email,
                    'posisi' => $user->role->name,
                    'status' => $user->status,
                    'avatar' => $user->profile_picture
                        ? asset('storage/' . $user->profile_picture)
                        : 'https://i.pravatar.cc/40?u=' . $user->id,
                ];
            });

        $roles = Role::whereHas('users')->pluck('name');

        return inertia('Admin/Admins/List', [
            'admins' => $admins,
            'roles' => $roles,
            'filters' => $request->only(['search', 'status', 'role', 'perPage']),
            'title' => 'Pengelolaan Admin'
        ]);
    }
}
