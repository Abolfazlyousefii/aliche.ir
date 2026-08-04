<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->merge([
            'mobile' => PhoneNumber::normalize($request->input('mobile')),
        ]);

        $credentials = $request->validate([
            'mobile' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string'],
        ], [], [
            'mobile' => 'شماره تماس',
            'password' => 'رمز عبور',
        ]);

        $remember = $request->boolean('remember');
        $user = User::query()
            ->where('is_active', true)
            ->whereNotNull('mobile')
            ->get()
            ->first(fn (User $user) => PhoneNumber::normalize($user->mobile) === $credentials['mobile']);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'mobile' => 'شماره تماس، رمز عبور یا وضعیت حساب کاربری صحیح نیست.',
            ]);
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'با موفقیت از پنل خارج شدید.');
    }
}
