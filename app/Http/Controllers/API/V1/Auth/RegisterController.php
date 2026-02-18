<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;

use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

use App\Http\Resources\UserResource;


class RegisterController extends Controller
{
    public function __invoke(RegisterUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'username' => $data['username'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'user',
        ]);

        // Create a token for the user
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'User successfully registered.',
            'data'    => new UserResource($user),
        ], 201);
    }
}
