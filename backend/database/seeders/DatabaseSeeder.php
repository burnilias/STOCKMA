<?php

namespace Database\Seeders;

use App\Models\AlerteStock;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $companyPharma = Company::create([
            'nom' => 'Pharmacie Centrale',
            'email' => 'contact@pharmacie.com',
            'telephone' => '01 23 45 67 89',
            'adresse' => '45 Rue de la Médecine, 75005 Paris',
            'statut' => 'actif',
        ]);

        $companyMagasin = Company::create([
            'nom' => 'Magasin Général Plus',
            'email' => 'info@magasinplus.com',
            'telephone' => '04 56 78 90 12',
            'adresse' => '78 Avenue du Commerce, 69001 Lyon',
            'statut' => 'actif',
        ]);

        $companyEntrepot = Company::create([
            'nom' => 'Entrepôt Nord',
            'email' => 'nord@entrepot.com',
            'telephone' => '03 21 43 65 87',
            'adresse' => '12 Zone Industrielle, 59000 Lille',
            'statut' => 'inactif',
        ]);

        $superAdmin = User::create([
            'nom' => 'Super Administrateur',
            'email' => 'super@stock.com',
            'mot_de_passe' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $adminPharma = User::create([
            'nom' => 'Dr. Sarah Martin',
            'email' => 'admin@pharmacie.com',
            'mot_de_passe' => 'password',
            'role' => User::ROLE_ADMIN,
            'company_id' => $companyPharma->id,
        ]);

        $employePharma = User::create([
            'nom' => 'Lucas Bernard',
            'email' => 'employe@pharmacie.com',
            'mot_de_passe' => 'password',
            'role' => User::ROLE_EMPLOYEE,
            'company_id' => $companyPharma->id,
        ]);

        $adminMagasin = User::create([
            'nom' => 'Marie Dubois',
            'email' => 'admin@magasin.com',
            'mot_de_passe' => 'password',
            'role' => User::ROLE_ADMIN,
            'company_id' => $companyMagasin->id,
        ]);

        $employeMagasin = User::create([
            'nom' => 'Thomas Moreau',
            'email' => 'employe@magasin.com',
            'mot_de_passe' => 'password',
            'role' => User::ROLE_EMPLOYEE,
            'company_id' => $companyMagasin->id,
        ]);

        $catPharma1 = Category::create(['nom_categorie' => 'Médicaments', 'company_id' => $companyPharma->id]);
        $catPharma2 = Category::create(['nom_categorie' => 'Soins', 'company_id' => $companyPharma->id]);
        $catPharma3 = Category::create(['nom_categorie' => 'Matériel médical', 'company_id' => $companyPharma->id]);

        $catMaga1 = Category::create(['nom_categorie' => 'Alimentaire', 'company_id' => $companyMagasin->id]);
        $catMaga2 = Category::create(['nom_categorie' => 'Bricolage', 'company_id' => $companyMagasin->id]);
        $catMaga3 = Category::create(['nom_categorie' => 'Électronique', 'company_id' => $companyMagasin->id]);

        $pharmaProducts = [
            ['nom' => 'Doliprane 1000mg', 'prix' => 8.50, 'quantite' => 150, 'id_categorie' => $catPharma1->id_categorie],
            ['nom' => 'Aspirine 500mg', 'prix' => 5.20, 'quantite' => 200, 'id_categorie' => $catPharma1->id_categorie],
            ['nom' => 'Ibuprofène 400mg', 'prix' => 6.80, 'quantite' => 5, 'id_categorie' => $catPharma1->id_categorie],
            ['nom' => 'Bande élastique', 'prix' => 3.50, 'quantite' => 80, 'id_categorie' => $catPharma2->id_categorie],
            ['nom' => 'Compresses steriles', 'prix' => 4.20, 'quantite' => 120, 'id_categorie' => $catPharma2->id_categorie],
            ['nom' => 'Thermomètre digital', 'prix' => 12.90, 'quantite' => 25, 'id_categorie' => $catPharma3->id_categorie],
            ['nom' => 'Tensiomètre', 'prix' => 45.00, 'quantite' => 8, 'id_categorie' => $catPharma3->id_categorie],
            ['nom' => 'Masques chirurgicaux (50)', 'prix' => 15.00, 'quantite' => 3, 'id_categorie' => $catPharma3->id_categorie],
        ];

        $magaProducts = [
            ['nom' => 'Riz 5kg', 'prix' => 18.00, 'quantite' => 50, 'id_categorie' => $catMaga1->id_categorie],
            ['nom' => 'Huile d\'olive 1L', 'prix' => 9.50, 'quantite' => 40, 'id_categorie' => $catMaga1->id_categorie],
            ['nom' => 'Pâtes 500g', 'prix' => 2.20, 'quantite' => 100, 'id_categorie' => $catMaga1->id_categorie],
            ['nom' => 'Tournevis cruciforme', 'prix' => 7.50, 'quantite' => 60, 'id_categorie' => $catMaga2->id_categorie],
            ['nom' => 'Marteau', 'prix' => 12.00, 'quantite' => 30, 'id_categorie' => $catMaga2->id_categorie],
            ['nom' => 'Perceuse sans fil', 'prix' => 89.00, 'quantite' => 15, 'id_categorie' => $catMaga2->id_categorie],
            ['nom' => 'Câble HDMI 2m', 'prix' => 15.90, 'quantite' => 45, 'id_categorie' => $catMaga3->id_categorie],
            ['nom' => 'Disque dur externe 1To', 'prix' => 65.00, 'quantite' => 20, 'id_categorie' => $catMaga3->id_categorie],
        ];

        $createdPharma = [];
        foreach ($pharmaProducts as $p) {
            $createdPharma[] = Product::create(array_merge($p, ['company_id' => $companyPharma->id]));
        }

        $createdMaga = [];
        foreach ($magaProducts as $p) {
            $createdMaga[] = Product::create(array_merge($p, ['company_id' => $companyMagasin->id]));
        }

        AlerteStock::create(['seuil_min' => 20, 'date_alerte' => now()->toDateString(), 'id_product' => $createdPharma[2]->id_product, 'company_id' => $companyPharma->id]);
        AlerteStock::create(['seuil_min' => 10, 'date_alerte' => now()->toDateString(), 'id_product' => $createdPharma[7]->id_product, 'company_id' => $companyPharma->id]);
        AlerteStock::create(['seuil_min' => 5, 'date_alerte' => now()->toDateString(), 'id_product' => $createdMaga[4]->id_product, 'company_id' => $companyMagasin->id]);

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $twoDaysAgo = now()->subDays(2)->toDateString();

        $movementsData = [
            [$createdPharma[0], $adminPharma, 'entry', 100, 'PharmaDistribute', $yesterday],
            [$createdPharma[1], $employePharma, 'entry', 200, 'PharmaDistribute', $yesterday],
            [$createdPharma[2], $adminPharma, 'exit', 20, null, $yesterday, 'Vente'],
            [$createdPharma[4], $employePharma, 'entry', 50, 'MedicalSupply', $today],
            [$createdPharma[6], $adminPharma, 'exit', 2, null, $twoDaysAgo, 'Prêt matériel'],
            [$createdMaga[0], $adminMagasin, 'entry', 80, 'GrossisteAlim', $yesterday],
            [$createdMaga[2], $employeMagasin, 'entry', 150, 'GrossisteAlim', $yesterday],
            [$createdMaga[4], $adminMagasin, 'exit', 5, null, $yesterday, 'Vente client'],
            [$createdMaga[5], $employeMagasin, 'entry', 20, 'OutillagePro', $today],
            [$createdMaga[7], $adminMagasin, 'exit', 3, null, $twoDaysAgo, 'Vente'],
        ];

        foreach ($movementsData as $m) {
            StockMovement::create([
                'id_product' => $m[0]->id_product,
                'id_user' => $m[1]->id_personne,
                'type' => $m[2],
                'quantity' => $m[3],
                'supplier' => $m[4] ?? null,
                'reason' => $m[6] ?? null,
                'date' => $m[5],
                'company_id' => $m[0]->company_id,
            ]);
        }

        foreach (array_merge($createdPharma, $createdMaga) as $p) {
            $entries = StockMovement::where('id_product', $p->id_product)->where('type', StockMovement::TYPE_ENTRY)->sum('quantity');
            $exits = StockMovement::where('id_product', $p->id_product)->where('type', StockMovement::TYPE_EXIT)->sum('quantity');
            $p->quantite = $entries - $exits;
            $p->save();
        }
    }
}
