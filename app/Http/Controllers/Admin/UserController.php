<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TypeUser;
use App\Models\Area;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['typeUser', 'area', 'areas'])->get();
        $typeUsers = TypeUser::all();
        $areas = Area::all();
        return view('admin.users', compact('users', 'typeUsers', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Name_User' => 'required|string|max:55',
            'Username_User' => 'required|string|max:55|unique:users,Username_User',
            'Password_User' => 'required|string|max:55',
            'Id_Type_User' => 'required|exists:type_users,Id_Type_User',
            'Id_Area' => 'nullable|exists:areas,Id_Area',
            'Id_Areas' => 'nullable|array',
            'Id_Areas.*' => 'exists:areas,Id_Area',
        ], [
            'Username_User.unique' => 'Username sudah digunakan, silakan pilih yang lain.'
        ]);

        $user = User::create([
            'Name_User' => $validated['Name_User'],
            'Username_User' => $validated['Username_User'],
            'Password_User' => $validated['Password_User'],
            'Id_Type_User' => $validated['Id_Type_User'],
            'Id_Area' => $validated['Id_Area'] ?? null,
        ]);

        if (!empty($validated['Id_Areas'])) {
            $user->areas()->sync($validated['Id_Areas']);
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'Name_User' => 'required|string|max:55',
            'Username_User' => 'required|string|max:55|unique:users,Username_User,' . $id . ',Id_User',
            'Password_User' => 'nullable|string|max:55',
            'Id_Type_User' => 'required|exists:type_users,Id_Type_User',
            'Id_Area' => 'nullable|exists:areas,Id_Area',
            'Id_Areas' => 'nullable|array',
            'Id_Areas.*' => 'exists:areas,Id_Area',
        ], [
            'Username_User.unique' => 'Username sudah digunakan, silakan pilih yang lain.'
        ]);

        $data = [
            'Name_User' => $validated['Name_User'],
            'Username_User' => $validated['Username_User'],
            'Id_Type_User' => $validated['Id_Type_User'],
            'Id_Area' => $validated['Id_Area'] ?? null,
        ];

        if ($request->filled('Password_User')) {
            $data['Password_User'] = $validated['Password_User'];
        }

        $user->update($data);

        if (!empty($validated['Id_Areas'])) {
            $user->areas()->sync($validated['Id_Areas']);
        } else {
            $user->areas()->sync([]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus!');
    }
}
