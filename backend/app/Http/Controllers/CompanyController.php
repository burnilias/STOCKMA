<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index(): JsonResponse
    {
        $companies = Company::withCount(['users', 'products'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $companies,
        ]);
    }

    public function store(Request $request): JsonResponse
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
            'message' => 'Entreprise créée avec succès.',
            'data' => $company,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $company = Company::with(['users', 'products'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $company,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $company = Company::findOrFail($id);

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('companies')->ignore($company->id)],
            'telephone' => 'nullable|string|max:50',
            'adresse' => 'nullable|string|max:255',
        ]);

        $company->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Entreprise mise à jour.',
            'data' => $company,
        ]);
    }

    public function updateStatut(Request $request, int $id): JsonResponse
    {
        $company = Company::findOrFail($id);

        $data = $request->validate([
            'statut' => 'required|in:actif,inactif',
        ]);

        $company->update(['statut' => $data['statut']]);

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour.',
            'data' => $company,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $company = Company::findOrFail($id);

        DB::transaction(function () use ($company) {
            $company->users()->delete();
            $company->products()->delete();
            $company->categories()->delete();
            $company->stockMovements()->delete();
            $company->alerteStocks()->delete();
            $company->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Entreprise supprimée.',
        ]);
    }
}
