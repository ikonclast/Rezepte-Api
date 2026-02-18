<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;

use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    // GET /api/users (Admin only)
    public function index()
    {
        $users = User::query()
            ->select(['id', 'username', 'email', 'role'])
            ->orderBy('id')
            ->get();

        return UserResource::collection($users);
    }

    public function show(User $user): UserResource
    {
        $this->authorize('view', $user);
        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource|JsonResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        if (array_key_exists('password', $data)) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->fill($data);
        $user->save();

        return new UserResource($user); // 200 OK
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        DB::transaction(function () use ($user) {
            $user->delete();
        });

        return response()->json(null, 204);
    }
}
