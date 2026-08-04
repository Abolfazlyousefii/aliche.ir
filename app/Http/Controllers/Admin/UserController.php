<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\GuildUnion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $status = (string) $request->query('status', '');

        $users = User::query()
            ->with(['roles', 'union'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'status'));
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => Role::query()->orderBy('label')->get(),
            'unions' => GuildUnion::query()->where('is_active', true)->orderBy('title')->orderBy('name')->get(),
            'selectedRoles' => collect(),
            'user' => null,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        try {
            $user = User::create($this->userData($request->validated()));
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages($this->uniqueConstraintMessages($exception));
        }

        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index')->with('success', 'کاربر با موفقیت ایجاد شد.');
    }

    public function show(User $user): View
    {
        $user->load(['roles.permissions', 'union']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user->load(['roles', 'union']),
            'roles' => Role::query()->orderBy('label')->get(),
            'unions' => GuildUnion::query()->where('is_active', true)->orderBy('title')->orderBy('name')->get(),
            'selectedRoles' => $user->roles()->pluck('roles.id'),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        try {
            $user->update($this->userData($request->validated(), updating: true));
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages($this->uniqueConstraintMessages($exception));
        }

        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index')->with('success', 'کاربر با موفقیت ویرایش شد.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'کاربر با موفقیت حذف شد.');
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    private function userData(array $validated, bool $updating = false): array
    {
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'union_id' => $validated['union_id'] ?? null,
            'is_active' => (bool) $validated['is_active'],
        ];

        if (! $updating || filled($validated['password'] ?? null)) {
            $data['password'] = $validated['password'];
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function uniqueConstraintMessages(UniqueConstraintViolationException $exception): array
    {
        $message = $exception->getMessage();

        if (Str::contains($message, 'users_email_unique')) {
            return ['email' => 'این ایمیل قبلاً برای کاربر دیگری ثبت شده است.'];
        }

        if (Str::contains($message, 'users_mobile_unique')) {
            return ['mobile' => 'این شماره تماس قبلاً برای کاربر دیگری ثبت شده است.'];
        }

        return ['email' => 'اطلاعات وارد شده با کاربر دیگری تکراری است.'];
    }
}
