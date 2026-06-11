<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Name_User' => 'required|string|max:55',
            'Username_User' => 'required|string|max:55|unique:users,Username_User',
            'Password_User' => 'required|string|max:55',
        ], [
            'Username_User.unique' => 'Username sudah digunakan, silakan pilih yang lain.'
        ]);

        User::create([
            'Name_User' => $validated['Name_User'],
            'Username_User' => $validated['Username_User'],
            'Password_User' => $validated['Password_User'],
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan!');
    }
}
