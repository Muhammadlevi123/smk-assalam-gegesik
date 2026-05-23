<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // ── index ────────────────────────────────────────────────────────────────
    public function index(Request $request): Response
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        $paginator = $query->latest()->paginate(10)->withQueryString();

        $users = [
            'data'         => $paginator->getCollection()->map(fn ($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'role'       => $u->role,
                'created_at' => $u->created_at,
                'updated_at' => $u->updated_at,
            ])->all(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'links'        => $paginator->linkCollection()->toArray(),
        ];

        $roles = User::select('role')
            ->distinct()
            ->whereNotNull('role')
            ->orderBy('role')
            ->pluck('role')
            ->toArray();

        return Inertia::render('admin/user/Index', [
            'users'   => $users,
            'filters' => $request->only(['search', 'role']),
            'roles'   => $roles,
        ]);
    }

    // ── create ───────────────────────────────────────────────────────────────
    public function create(): Response
    {
        return Inertia::render('admin/user/Create');
    }

    // ── store ────────────────────────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|string|in:admin,user',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'created');
    }

    // ── show ─────────────────────────────────────────────────────────────────
    public function show(User $user): Response
    {
        return Inertia::render('admin/user/Show', [
            'user' => $user,
        ]);
    }

    // ── edit ─────────────────────────────────────────────────────────────────
    public function edit(User $user): Response
    {
        return Inertia::render('admin/user/Edit', [
            'user' => $user,
        ]);
    }

    // ── update ───────────────────────────────────────────────────────────────
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|string|in:admin,user',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'updated');
    }

    // ── destroy ──────────────────────────────────────────────────────────────
    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors([
                'delete_error' => 'Anda tidak dapat menghapus akun Anda sendiri.',
            ]);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'deleted');
    }
}
