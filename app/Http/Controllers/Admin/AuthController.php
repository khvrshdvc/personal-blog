<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\AuthenticateAdmin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('admin.login');
    }

    public function store(LoginRequest $request, AuthenticateAdmin $authenticate): RedirectResponse
    {
        $ok = $authenticate(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
            $request->boolean('remember'),
        );

        if (! $ok) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email yoki parol notoʻgʻri.']);
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
