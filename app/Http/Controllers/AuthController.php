<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function webCreate(): View|RedirectResponse
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.login');
    }

    public function webStore(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['email'] = strtolower(trim($credentials['email']));
        $credentials['is_admin'] = true;

        if (! Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi admin tidak valid.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function webDestroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Anda berhasil keluar.');
    }

    public function create(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Send credentials to POST /api/login',
            'data' => null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $validator->validated();
        $email = strtolower(trim($credentials['email']));
        $user = User::query()->where('email', $email)->where('is_admin', true)->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials are incorrect.',
                'data' => null,
            ], 401);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Authenticated successfully',
            'data' => [
                'user' => $user->only(['id', 'name', 'email']),
                'token' => $token,
            ],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
            'data' => null,
        ]);
    }
}
