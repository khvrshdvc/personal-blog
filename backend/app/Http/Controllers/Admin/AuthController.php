<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\AttemptAdminLogin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AttemptAdminLogin $attempt): Response
    {
        abort_unless($request->hasSession(), 403, 'Bu manbadan kirish mumkin emas.');

        if (! $attempt($request->validated('password'))) {
            throw ValidationException::withMessages([
                'password' => 'Parol notoʻgʻri.',
            ]);
        }

        $request->session()->regenerate();

        return response()->noContent();
    }

    public function logout(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['name' => $request->user()->name]);
    }
}
