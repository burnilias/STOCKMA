<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            throw ValidationException::withMessages([
                'email' => ['Email ou mot de passe incorrect'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('spa')->plainTextToken;

        $company = $user->company;

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => [
                    'id_personne' => $user->id_personne,
                    'nom' => $user->nom,
                    'email' => $user->email,
                    'role' => $user->role,
                    'company_id' => $user->company_id,
                    'company' => $company ? [
                        'id' => $company->id,
                        'nom' => $company->nom,
                    ] : null,
                ],
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_nom' => 'required|string|max:255',
            'company_email' => 'required|email|unique:companies,email',
            'company_telephone' => 'nullable|string|max:50',
            'company_adresse' => 'nullable|string|max:255',
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $company = DB::transaction(function () use ($data) {
            $company = Company::create([
                'nom' => $data['company_nom'],
                'email' => $data['company_email'],
                'telephone' => $data['company_telephone'] ?? null,
                'adresse' => $data['company_adresse'] ?? null,
                'statut' => 'actif',
            ]);

            User::create([
                'nom' => $data['nom'],
                'email' => $data['email'],
                'mot_de_passe' => bcrypt($data['password']),
                'role' => User::ROLE_ADMIN,
                'company_id' => $company->id,
            ]);

            return $company;
        });

        return response()->json([
            'success' => true,
            'message' => 'Votre espace a été créé. Connectez-vous maintenant.',
            'data' => [
                'company' => [
                    'id' => $company->id,
                    'nom' => $company->nom,
                ],
            ],
        ], 201);
    }

  /* public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        // Pour l'instant, logger la demande
        \Log::info("Demande de réinitialisation de mot de passe pour : " . $request->email);

        return response()->json([
            'success' => true,
            'message' => 'Si un compte existe avec cet email, vous recevrez les instructions de réinitialisation. (Fonctionnalité simulée)',
        ]);
    }*/

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié.'], 401);
        }

        $company = $user->company;

        return response()->json([
            'success' => true,
            'data' => [
                'id_personne' => $user->id_personne,
                'nom' => $user->nom,
                'email' => $user->email,
                'role' => $user->role,
                'company_id' => $user->company_id,
                'company' => $company ? [
                    'id' => $company->id,
                    'nom' => $company->nom,
                ] : null,
            ],
        ]);
    }
}
