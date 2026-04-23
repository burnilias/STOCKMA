<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // $this->authorize('viewAny', User::class);

        $query = User::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $users = $query->orderBy('nom')->paginate($perPage);

        $data = collect($users->items())->map(fn (User $u) => $this->userResponse($u))->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        // $this->authorize('create', User::class);

        $user = User::query()->create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur créé.',
            'data' => $this->userResponse($user),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        // $this->authorize('view', $user);

        return response()->json([
            'success' => true,
            'data' => $this->userResponse($user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        // $this->authorize('update', $user);

        $data = $request->validated();
        if (empty($data['mot_de_passe'])) {
            unset($data['mot_de_passe']);
        }
        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur mis à jour.',
            'data' => $this->userResponse($user->fresh()),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        // $this->authorize('delete', $user);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userResponse(User $user): array
    {
        return [
            'id_personne' => $user->id_personne,
            'nom' => $user->nom,
            'email' => $user->email,
            'role' => $user->role,
            'company_id' => $user->company_id,
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }
}
