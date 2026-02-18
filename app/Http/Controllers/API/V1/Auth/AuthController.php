<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Http\Resources\UserResource;


class AuthController extends Controller
{
    // POST /auth/login

    public function login(Request $request)
    {

        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'device'   => ['nullable', 'string']
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {

            throw ValidationException::withMessages([
                'email' => ['Die Anmeldedaten sind ungültig.']
            ]);
        }

        // Token ausstellen
        $tokenName = $data['device'] ?? 'api';
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'message' => 'Login erfolgreich.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id'       => $user->id,
                    'username' => $user->username ?? null,
                    'name'     => $user->name ?? null,
                    'email'    => $user->email,
                    'role'     => $user->role ?? 'user',
                ],
            ]
        ], 200);
    }

    // POST /auth/logout  (auth:sanctum)
    public function logout(Request $request)
    {
        // Aktuelles Token widerrufen
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout erfolgreich.'
        ], 200);
    }

    // GET /auth/me  (auth:sanctum)
    public function me(Request $request)
    {
        return new UserResource($request->user());
    }
}
