<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('label', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.roles.index', compact('roles', 'search'));
    }

    public function create(): View
    {
        $permissions = Permission::query()->orderBy('group')->orderBy('name')->get()->groupBy('group');
        $selectedPermissions = collect();

        return view('admin.roles.create', compact('permissions', 'selectedPermissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRole($request);

        $role = Role::create($this->roleData($validated));
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('admin.roles.index')->with('success', 'نقش با موفقیت ایجاد شد.');
    }

    public function show(Role $role): View
    {
        $role->load(['permissions' => fn ($query) => $query->orderBy('group')->orderBy('name')])->loadCount('users');

        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::query()->orderBy('group')->orderBy('name')->get()->groupBy('group');
        $selectedPermissions = $role->permissions()->pluck('permissions.id');

        return view('admin.roles.edit', compact('role', 'permissions', 'selectedPermissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $this->validateRole($request, $role);

        $role->update($this->roleData($validated));
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('admin.roles.index')->with('success', 'نقش با موفقیت ویرایش شد.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'نقش با موفقیت حذف شد.');
    }

    /**
     * @param array<string, mixed> $validated
     * @return array{name: string, label: string, description?: string|null}
     */
    private function roleData(array $validated): array
    {
        $validated = $this->sanitizeRichTextFields($validated, ['description']);

        return [
            'name' => $validated['name'],
            'label' => $validated['label'],
            'description' => $validated['description'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRole(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', 'alpha_dash:ascii', Rule::unique('roles', 'name')->ignore($role)],
            'label' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);
    }
}
