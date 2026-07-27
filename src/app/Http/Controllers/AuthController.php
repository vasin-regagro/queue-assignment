<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Exceptions\ApiException;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(private readonly JwtService $jwt) {}

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $user = User::create([
            ...$data,
            'roles' => [UserRole::USER->value],
            'is_guest' => false,
        ]);

        return $this->tokenResponse($user, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $data['email'])->where('is_guest', false)->first();

        if (! $user || ! Hash::check($data['password'], (string) $user->password)) {
            throw ApiException::unauthorized('The credentials are invalid.');
        }

        return $this->tokenResponse($user);
    }

    public function guest(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['nullable', 'string', 'max:255']]);
        $expiresAt = now()->addHours(24);

        $user = User::create([
            'name' => $data['name'] ?? 'Guest '.Str::upper(Str::random(6)),
            'email' => null,
            'password' => null,
            'roles' => [UserRole::USER->value],
            'is_guest' => true,
            'guest_expires_at' => $expiresAt,
        ]);

        return $this->tokenResponse($user, 201);
    }

    private function tokenResponse(User $user, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => [
                'accessToken' => $this->jwt->issue($user),
                'tokenType' => 'Bearer',
                'expiresIn' => $user->is_guest ? 86400 : config('jwt.ttl'),
                'user' => [
                    'id' => $user->id,
                    'displayName' => $user->name,
                    'roles' => $user->roles,
                    'isGuest' => $user->is_guest,
                ],
            ],
        ], $status);
    }
}
