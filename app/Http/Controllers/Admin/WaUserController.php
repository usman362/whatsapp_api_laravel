<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaUser;
use Illuminate\Http\Request;

class WaUserController extends Controller
{
    public function index(Request $request)
    {
        $query = WaUser::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('phone_e164', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('api_base_url', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.wa-users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.wa-users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'phone_e164' => 'required|string|max:20|unique:wa_users,phone_e164',
            'name' => 'nullable|string|max:255',
            'api_base_url' => 'required|url|max:500',
            'api_token' => 'nullable|string|max:500',
        ]);

        $validated['active'] = $request->boolean('active');

        WaUser::create($validated);

        return redirect()->route('admin.wa-users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(WaUser $waUser)
    {
        return view('admin.wa-users.edit', ['user' => $waUser]);
    }

    public function update(Request $request, WaUser $waUser)
    {
        $validated = $request->validate([
            'phone_e164' => "required|string|max:20|unique:wa_users,phone_e164,{$waUser->id}",
            'name' => 'nullable|string|max:255',
            'api_base_url' => 'required|url|max:500',
            'api_token' => 'nullable|string|max:500',
        ]);

        $validated['active'] = $request->boolean('active');

        $waUser->update($validated);

        return redirect()->route('admin.wa-users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(WaUser $waUser)
    {
        $waUser->delete();

        return redirect()->route('admin.wa-users.index')
            ->with('success', 'User deleted successfully.');
    }
}
