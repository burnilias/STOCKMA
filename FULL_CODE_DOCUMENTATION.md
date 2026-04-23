# Documentation Complète du Code - Système de Gestion de Stock

## Table des Matières
1. [Architecture Générale](#architecture-générale)
2. [Modèles (Models)](#modèles)
3. [Contrôleurs (Controllers)](#contrôleurs)
4. [Requêtes de Validation (Form Requests)](#requêtes-de-validation)
5. [Traits et Classes Support](#traits-et-classes-support)
6. [Routes API](#routes-api)
7. [Politiques d'Autorisation (Policies)](#politiques-dautorisation)

---

## Architecture Générale

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           CLIENT (React/Vue)                                │
└─────────────────────────────────┬───────────────────────────────────────────┘
                                  │ HTTP/JSON
┌─────────────────────────────────▼───────────────────────────────────────────┐
│                         LARAVEL API (Backend)                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────────┐   │
│  │   Routes    │→ │ Middleware  │→ │ Controllers │→ │     Models      │   │
│  │   (api.php) │  │  (Sanctum)  │  │   (API)     │  │  (Eloquent)     │   │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────────┘   │
│                                                           ↓                 │
│                                                    ┌─────────────┐         │
│                                                    │  MySQL DB   │         │
│                                                    └─────────────┘         │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Structure des Dossiers

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Contrôleurs API
│   │   ├── Requests/         # Validation des requêtes
│   │   └── Middleware/       # Middleware personnalisés
│   ├── Models/               # Modèles Eloquent
│   ├── Policies/             # Politiques d'autorisation
│   ├── Support/              # Classes utilitaires
│   └── Traits/               # Traits réutilisables
├── database/
│   ├── migrations/           # Migrations de la base
│   └── factories/            # Factories pour les tests
├── routes/
│   └── api.php             # Routes API
└── config/                 # Configuration
```

---

## Modèles

### 1. User (`app/Models/User.php`)

**Rôle :** Représente les utilisateurs du système (Super Admin, Admin, Employé)

**Attributs :**
| Champ | Type | Description |
|-------|------|-------------|
| `id_personne` | int (PK) | Identifiant unique |
| `nom` | string | Nom complet |
| `email` | string | Email unique |
| `mot_de_passe` | string | Hash du mot de passe |
| `role` | string | `super_admin`, `admin`, `employee` |
| `company_id` | int (FK) | ID de l'entreprise |
| `remember_token` | string | Token "remember me" |
| `email_verified_at` | datetime | Date de vérification email |
| `created_at` / `updated_at` | datetime | Timestamps |

**Constantes de Rôle :**
```php
const ROLE_SUPER_ADMIN = 'super_admin';
const ROLE_ADMIN = 'admin';
const ROLE_EMPLOYEE = 'employee';
```

**Relations :**
- `company()` → BelongsTo `Company`
- `stockMovements()` → HasMany `StockMovement`

**Méthodes :**
```php
getAuthPassword(): string           // Retourne 'mot_de_passe' pour Auth
getRouteKeyName(): string           // Retourne 'id_personne' pour routing
isAdmin(): bool                     // Vérifie role admin ou super_admin
isSuperAdmin(): bool                // Vérifie role super_admin uniquement
```

---

### 2. Company (`app/Models/Company.php`)

**Rôle :** Représente une entreprise/client (multi-tenancy)

**Attributs :**
| Champ | Type | Description |
|-------|------|-------------|
| `id` | int (PK) | Identifiant unique |
| `nom` | string | Nom de l'entreprise |
| `email` | string | Email de contact |
| `telephone` | string | Numéro de téléphone |
| `adresse` | string | Adresse physique |
| `statut` | string | `actif` ou `inactif` |

**Relations :**
- `users()` → HasMany `User`
- `categories()` → HasMany `Category`
- `products()` → HasMany `Product`
- `stockMovements()` → HasMany `StockMovement`
- `alerteStocks()` → HasMany `AlerteStock`

---

### 3. Product (`app/Models/Product.php`)

**Rôle :** Représente un produit dans l'inventaire

**Attributs :**
| Champ | Type | Description |
|-------|------|-------------|
| `id_product` | int (PK) | Identifiant unique |
| `nom` | string | Nom du produit |
| `prix` | decimal(10,2) | Prix unitaire |
| `quantite` | int | Quantité en stock |
| `id_categorie` | int (FK) | ID de la catégorie |
| `company_id` | int (FK) | ID de l'entreprise |
| `created_at` / `updated_at` | datetime | Timestamps |

**Relations :**
- `category()` → BelongsTo `Category`
- `stockMovements()` → HasMany `StockMovement`
- `alerteStocks()` → HasMany `AlerteStock`

**Méthodes :**
```php
verifierStock(int $quantiteDemandee): bool
    // Vérifie si la quantité demandée est disponible
    // Retourne true si $this->quantite >= $quantiteDemandee

stockStatus(?int $seuilMin = null): string
    // Retourne le statut du stock:
    // - 'out' si quantite <= 0
    // - 'low' si quantite <= seuilMin
    // - 'ok' sinon
```

---

### 4. Category (`app/Models/Category.php`)

**Rôle :** Catégorisation des produits

**Attributs :**
| Champ | Type | Description |
|-------|------|-------------|
| `id_categorie` | int (PK) | Identifiant unique |
| `nom_categorie` | string | Nom de la catégorie |
| `company_id` | int (FK) | ID de l'entreprise |

**Relations :**
- `products()` → HasMany `Product`

---

### 5. StockMovement (`app/Models/StockMovement.php`)

**Rôle :** Enregistre tous les mouvements d'entrée et de sortie de stock

**Attributs :**
| Champ | Type | Description |
|-------|------|-------------|
| `id` | int (PK) | Identifiant unique |
| `id_product` | int (FK) | ID du produit concerné |
| `id_user` | int (FK) | ID de l'utilisateur ayant fait le mouvement |
| `type` | string | `entry` ou `exit` |
| `quantity` | int | Quantité déplacée |
| `note` | text | Note libre |
| `supplier` | string | Fournisseur (pour les entrées) |
| `reason` | string | Raison (pour les sorties) |
| `date` | date | Date du mouvement |
| `company_id` | int (FK) | ID de l'entreprise |
| `created_at` / `updated_at` | datetime | Timestamps |

**Constantes :**
```php
const TYPE_ENTRY = 'entry';
const TYPE_EXIT = 'exit';
```

**Relations :**
- `product()` → BelongsTo `Product`
- `user()` → BelongsTo `User`

---

### 6. AlerteStock (`app/Models/AlerteStock.php`)

**Rôle :** Définit les seuils d'alerte pour les produits

**Attributs :**
| Champ | Type | Description |
|-------|------|-------------|
| `id_alerte` | int (PK) | Identifiant unique |
| `seuil_min` | int | Seuil minimum déclenchant l'alerte |
| `date_alerte` | date | Date de dernière alerte déclenchée |
| `id_product` | int (FK) | ID du produit surveillé |
| `company_id` | int (FK) | ID de l'entreprise |

**Relations :**
- `product()` → BelongsTo `Product`

**Méthodes :**
```php
declencherAlerte(): void
    // Met à jour date_alerte avec la date actuelle

notifierAdmin(): void
    // Log l'alerte (placeholder pour envoi email)
```

---

### 7. Admin (`app/Models/Admin.php`)

**Rôle :** Extension de User pour les fonctionnalités admin

**Note :** Utilise la même table `users` (héritage Single Table)

**Méthodes :**
```php
creerUtilisateur(array $data): User    // Crée un nouvel utilisateur
gererProduit(): void                   // Placeholder
gererCategorie(): void                 // Placeholder
gererCommande(): void                  // Placeholder
```

---

## Contrôleurs

### 1. AuthController (`app/Http/Controllers/AuthController.php`)

**Responsabilité :** Authentification et gestion des sessions

| Méthode | Route | Description |
|---------|-------|-------------|
| `login()` | POST `/api/login` | Authentification, création token Sanctum |
| `register()` | POST `/api/register` | Création entreprise + admin initial |
| `logout()` | POST `/api/logout` | Déconnexion (révocation token) |
| `me()` | GET `/api/me` | Informations utilisateur connecté |

**Flux de Login :**
```php
1. Validation credentials (email, password)
2. Auth::attempt() → Vérifie le hash mot_de_passe
3. Suppression des tokens existants ($user->tokens()->delete())
4. Création nouveau token : $user->createToken('spa')
5. Retour : { token, user: { id_personne, nom, email, role, company } }
```

**Flux de Register :**
```php
1. Validation des champs entreprise + utilisateur
2. DB::transaction():
   a. Company::create([...])
   b. User::create([ role => ROLE_ADMIN, company_id => $company->id ])
3. Retour : { success: true, message: "Votre espace a été créé..." }
```

---

### 2. ProductController (`app/Http/Controllers/ProductController.php`)

**Responsabilité :** CRUD des produits

| Méthode | Route | Auth | Description |
|---------|-------|------|-------------|
| `index()` | GET `/api/products` | Auth | Liste paginée avec filtres |
| `store()` | POST `/api/products` | Auth | Création produit |
| `show()` | GET `/api/products/{id}` | Auth | Détails produit |
| `update()` | PUT `/api/products/{id}` | Auth | Mise à jour |
| `destroy()` | DELETE `/api/products/{id}` | Auth | Suppression |

**Filtres de index() :**
- `?search=nom` : Recherche par nom (LIKE %nom%)
- `?category_id=X` : Filtre par catégorie
- `?per_page=N` : Pagination (max 100)

**Réponse productResponse() :**
```json
{
  "id_product": 1,
  "nom": "Produit A",
  "prix": 19.99,
  "quantite": 50,
  "stock_status": "ok|low|out",
  "id_categorie": 2,
  "category": { "id_categorie": 2, "nom_categorie": "Catégorie X" },
  "created_at": "2024-01-15T10:30:00Z",
  "updated_at": "2024-01-20T14:22:00Z"
}
```

---

### 3. CategoryController (`app/Http/Controllers/CategoryController.php`)

**Responsabilité :** CRUD des catégories

| Méthode | Route | Auth | Description |
|---------|-------|------|-------------|
| `index()` | GET `/api/categories` | Auth | Liste paginée |
| `store()` | POST `/api/categories` | Admin | Création |
| `update()` | PUT `/api/categories/{id}` | Admin | Mise à jour |
| `destroy()` | DELETE `/api/categories/{id}` | Admin | Suppression |

---

### 4. StockMovementController (`app/Http/Controllers/StockMovementController.php`)

**Responsabilité :** Gestion des mouvements de stock

| Méthode | Route | Auth | Description |
|---------|-------|------|-------------|
| `index()` | GET `/api/stock-movements` | Auth | Liste paginée avec filtres |
| `store()` | POST `/api/stock-movements` | Auth | Création mouvement |

**Filtres de index() :**
- `?product_id=X` : Filtre par produit
- `?type=entry|exit` : Filtre par type
- `?date_from=YYYY-MM-DD` : Date début
- `?date_to=YYYY-MM-DD` : Date fin

**Flux store() - Logique métier critique :**
```php
DB::transaction(function () use ($data, $request): StockMovement {
    // 1. Verrouillage pessimiste du produit
    $product = Product::query()->lockForUpdate()->findOrFail($data['product_id']);
    
    $qty = (int) $data['quantity'];
    
    if ($data['type'] === StockMovement::TYPE_ENTRY) {
        // ENTRÉE: Ajouter à la quantité
        $product->quantite = (int) $product->quantite + $qty;
    } else {
        // SORTIE: Vérifier et soustraire
        $current = (int) $product->quantite;
        if ($current < $qty) {
            throw ValidationException::withMessages([
                'quantity' => ['Stock insuffisant. Disponible : '.$current],
            ]);
        }
        $product->quantite = $current - $qty;
    }
    
    // 2. Sauvegarde produit
    $product->save();
    
    // 3. Création du mouvement
    $mov = StockMovement::create([
        'id_product' => $product->id_product,
        'id_user' => $request->user()->id_personne,
        'type' => $data['type'],
        'quantity' => $qty,
        'note' => $data['note'] ?? null,
        'supplier' => $data['supplier'] ?? null,
        'reason' => $data['reason'] ?? null,
        'date' => $data['date'],
    ]);
    
    // 4. Synchronisation des alertes
    AlerteStockSync::afterQuantityChange($product->fresh());
    
    return $mov;
});
```

---

### 5. AlerteStockController (`app/Http/Controllers/AlerteStockController.php`)

**Responsabilité :** Gestion des alertes de stock

| Méthode | Route | Auth | Description |
|---------|-------|------|-------------|
| `index()` | GET `/api/alertes` | Admin | Liste avec produits et statut actif |
| `store()` | POST `/api/alertes` | Admin | Créer alerte |
| `update()` | PUT `/api/alertes/{id}` | Admin | Modifier seuil |
| `destroy()` | DELETE `/api/alertes/{id}` | Admin | Supprimer |

**Logique de `alerte_active` dans index() :**
```php
'alerte_active' => $p && (int) $p->quantite <= (int) $a->seuil_min
```

---

### 6. ReportController (`app/Http/Controllers/ReportController.php`)

**Responsabilité :** Rapports et tableau de bord

| Méthode | Route | Auth | Description |
|---------|-------|------|-------------|
| `dashboard()` | GET `/api/reports/dashboard` | Auth | KPIs et graphiques |
| `stockStatus()` | GET `/api/reports/stock-status` | Auth | Liste produits avec statut |
| `lowStock()` | GET `/api/reports/low-stock` | Auth | Produits en alerte |
| `movementHistory()` | GET `/api/reports/movement-history` | Auth | Historique filtré |

**dashboard() - KPIs calculés :**
```php
$cacheKey = "dashboard_data_{$companyId}";
$data = Cache::remember($cacheKey, now()->addMinutes(5), function () {
    return [
        'kpis' => [
            'total_products' => Product::count(),
            'valeur_totale' => SUM(quantite * prix) de tous les produits,
            'alertes_count' => COUNT(produits où quantite <= seuil_min),
            'out_of_stock_count' => COUNT(produits où quantite <= 0),
        ],
        'recent_movements' => 10 derniers mouvements,
        'chart' => [
            'labels' => [7 derniers jours],
            'entries' => [quantités entrées par jour],
            'exits' => [quantités sorties par jour],
        ],
    ];
});
```

---

### 7. UserController (`app/Http/Controllers/UserController.php`)

**Responsabilité :** CRUD des utilisateurs

| Méthode | Route | Auth | Description |
|---------|-------|------|-------------|
| `index()` | GET `/api/users` | Admin | Liste paginée |
| `store()` | POST `/api/users` | Admin | Création |
| `show()` | GET `/api/users/{id}` | Admin | Détails |
| `update()` | PUT `/api/users/{id}` | Admin | Mise à jour |
| `destroy()` | DELETE `/api/users/{id}` | Admin | Suppression |

---

### 8. CompanyController (`app/Http/Controllers/CompanyController.php`)

**Responsabilité :** Gestion des entreprises (Super Admin uniquement)

| Méthode | Route | Auth | Description |
|---------|-------|------|-------------|
| `index()` | GET `/api/companies` | Super Admin | Liste avec compteurs |
| `store()` | POST `/api/companies` | Super Admin | Création entreprise + admin |
| `show()` | GET `/api/companies/{id}` | Super Admin | Détails avec users/products |
| `update()` | PUT `/api/companies/{id}` | Super Admin | Mise à jour |
| `updateStatut()` | PATCH `/api/companies/{id}/statut` | Super Admin | Activer/Désactiver |
| `destroy()` | DELETE `/api/companies/{id}` | Super Admin | Suppression avec cascade |

**destroy() - Suppression en cascade :**
```php
DB::transaction(function () use ($company) {
    $company->users()->delete();
    $company->products()->delete();
    $company->categories()->delete();
    $company->stockMovements()->delete();
    $company->alerteStocks()->delete();
    $company->delete();
});
```

---

## Requêtes de Validation

### StoreProductRequest
```php
rules():
  'nom' => 'required|string|max:255'
  'prix' => 'required|numeric|min:0'
  'quantite' => 'nullable|integer|min:0'
  'id_categorie' => 'nullable|exists:categories,id_categorie'
```

### UpdateProductRequest
```php
rules():
  'nom' => 'sometimes|required|string|max:255'
  'prix' => 'sometimes|required|numeric|min:0'
  'quantite' => 'sometimes|required|integer|min:0'
  'id_categorie' => 'nullable|exists:categories,id_categorie'
```

### StoreStockMovementRequest
```php
rules():
  'product_id' => 'required|exists:products,id_product'
  'type' => 'required|in:entry,exit'
  'quantity' => 'required|integer|min:1'
  'date' => 'required|date'
  'note' => 'nullable|string|max:2000'
  'supplier' => 'nullable|string|max:255'
  'reason' => 'nullable|string|max:255'
```

### StoreUserRequest / UpdateUserRequest
```php
rules():
  'nom' => 'required|string|max:255'
  'email' => 'required|email|unique:users,email'
  'mot_de_passe' => 'required|string|min:8'
  'role' => 'required|in:admin,employee'
```

---

## Traits et Classes Support

### BelongsToCompany (`app/Traits/BelongsToCompany.php`)

**Responsabilité :** Ajoute le scope multi-tenant aux modèles

```php
trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::creating(function ($model) {
            if (auth()->check() && empty($model->company_id)) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }
    
    public function scopeForCurrentCompany($query)
    {
        return $query->where('company_id', auth()->user()->company_id);
    }
}
```

**Modèles utilisant ce trait :** User, Product, Category, StockMovement, AlerteStock

---

### StockMovementFormatter (`app/Support/StockMovementFormatter.php`)

**Responsabilité :** Formatage uniforme des mouvements de stock

```php
class StockMovementFormatter
{
    public static function toArray(StockMovement $movement): array
    {
        return [
            'id' => $movement->id,
            'type' => $movement->type,        // 'entry' | 'exit'
            'quantity' => (int) $movement->quantity,
            'note' => $movement->note,
            'supplier' => $movement->supplier,   // Pour les entrées
            'reason' => $movement->reason,     // Pour les sorties
            'date' => $movement->date->format('Y-m-d'),
            'created_at' => $movement->created_at?->toIso8601String(),
            'product' => [...],                  // Détails produit
            'user' => [...],                   // Détails utilisateur
        ];
    }
}
```

---

### AlerteStockSync (`app/Support/AlerteStockSync.php`)

**Responsabilité :** Synchronisation des alertes après changement de quantité

```php
class AlerteStockSync
{
    public static function afterQuantityChange(Product $product): void
    {
        // Appelé après chaque mouvement de stock
        // Vérifie si le produit a des alertes actives
        // Placeholder pour extension future
    }
}
```

---

## Routes API

```php
// routes/api.php

// Public
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Authentifié
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Categories (lecture pour tous)
    Route::get('/categories', [CategoryController::class, 'index']);
    
    // Products (CRUD pour tous les authentifiés)
    Route::apiResource('products', ProductController::class);
    
    // Stock Movements
    Route::get('/stock-movements', [StockMovementController::class, 'index']);
    Route::post('/stock-movements', [StockMovementController::class, 'store']);
    
    // Reports
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard']);
    Route::get('/reports/stock-status', [ReportController::class, 'stockStatus']);
    Route::get('/reports/low-stock', [ReportController::class, 'lowStock']);
    Route::get('/reports/movement-history', [ReportController::class, 'movementHistory']);
    
    // Admin uniquement
    Route::middleware('admin')->group(function () {
        Route::apiResource('categories', CategoryController::class)
            ->except(['index']);
        Route::apiResource('alertes', AlerteStockController::class);
        Route::apiResource('users', UserController::class);
    });
    
    // Super Admin uniquement
    Route::middleware('super_admin')->group(function () {
        Route::apiResource('companies', CompanyController::class);
        Route::patch('/companies/{id}/statut', [CompanyController::class, 'updateStatut']);
    });
});
```

---

## Politiques d'Autorisation

### Structure des Policies

Chaque modèle a une Policy correspondante dans `app/Policies/` :

| Policy | Méthodes | Règles |
|--------|----------|--------|
| `ProductPolicy` | viewAny, view, create, update, delete | Vérifie company_id |
| `CategoryPolicy` | viewAny, view, create, update, delete | Vérifie company_id |
| `UserPolicy` | viewAny, view, create, update, delete | Vérifie company_id + role |
| `StockMovementPolicy` | viewAny, view, create | Vérifie company_id |

### Exemple : ProductPolicy
```php
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // Tous les authentifiés
    }
    
    public function view(User $user, Product $product): bool
    {
        return $user->company_id === $product->company_id;
    }
    
    public function create(User $user): bool
    {
        return true; // Tous peuvent créer dans leur company
    }
    
    public function update(User $user, Product $product): bool
    {
        return $user->company_id === $product->company_id;
    }
    
    public function delete(User $user, Product $product): bool
    {
        return $user->company_id === $product->company_id;
    }
}
```

---

## Middleware Personnalisés

### Admin Middleware
```php
// Vérifie que l'utilisateur est admin ou super_admin
public function handle($request, Closure $next)
{
    if (!$request->user()->isAdmin()) {
        abort(403, 'Accès admin requis.');
    }
    return $next($request);
}
```

### Super Admin Middleware
```php
// Vérifie que l'utilisateur est super_admin
public function handle($request, Closure $next)
{
    if (!$request->user()->isSuperAdmin()) {
        abort(403, 'Accès super admin requis.');
    }
    return $next($request);
}
```

---

## Schéma de Base de Données

```sql
-- Table: companies
CREATE TABLE companies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    telephone VARCHAR(50),
    adresse VARCHAR(255),
    statut ENUM('actif', 'inactif') DEFAULT 'actif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: users
CREATE TABLE users (
    id_personne INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'employee') DEFAULT 'employee',
    company_id INT,
    remember_token VARCHAR(100),
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id)
);

-- Table: categories
CREATE TABLE categories (
    id_categorie INT PRIMARY KEY AUTO_INCREMENT,
    nom_categorie VARCHAR(255) NOT NULL,
    company_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id)
);

-- Table: products
CREATE TABLE products (
    id_product INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    prix DECIMAL(10,2) NOT NULL DEFAULT 0,
    quantite INT NOT NULL DEFAULT 0,
    id_categorie INT,
    company_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categorie) REFERENCES categories(id_categorie),
    FOREIGN KEY (company_id) REFERENCES companies(id)
);

-- Table: stock_movements
CREATE TABLE stock_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_product INT NOT NULL,
    id_user INT NOT NULL,
    type ENUM('entry', 'exit') NOT NULL,
    quantity INT NOT NULL,
    note TEXT,
    supplier VARCHAR(255),
    reason VARCHAR(255),
    date DATE NOT NULL,
    company_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_product) REFERENCES products(id_product),
    FOREIGN KEY (id_user) REFERENCES users(id_personne),
    FOREIGN KEY (company_id) REFERENCES companies(id)
);

-- Table: alerte_stocks
CREATE TABLE alerte_stocks (
    id_alerte INT PRIMARY KEY AUTO_INCREMENT,
    seuil_min INT NOT NULL,
    date_alerte DATE,
    id_product INT NOT NULL,
    company_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_product) REFERENCES products(id_product),
    FOREIGN KEY (company_id) REFERENCES companies(id)
);

-- Table: personal_access_tokens (Sanctum)
CREATE TABLE personal_access_tokens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (tokenable_type, tokenable_id)
);
```

---

## Exemples de Requêtes API

### 1. Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'
```
**Réponse :**
```json
{
  "success": true,
  "data": {
    "token": "1|abcdefghijklmnopqrstuvwxyz",
    "user": {
      "id_personne": 1,
      "nom": "Admin User",
      "email": "admin@example.com",
      "role": "admin",
      "company_id": 1,
      "company": { "id": 1, "nom": "Ma Société" }
    }
  }
}
```

### 2. Créer un mouvement d'entrée
```bash
curl -X POST http://localhost:8000/api/stock-movements \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 5,
    "type": "entry",
    "quantity": 100,
    "date": "2024-01-20",
    "supplier": "Fournisseur ABC",
    "note": "Réception commande #1234"
  }'
```

### 3. Créer un mouvement de sortie
```bash
curl -X POST http://localhost:8000/api/stock-movements \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz" \
  -H "Content-Type: application/json" \
  -d '{
    "product_id": 5,
    "type": "exit",
    "quantity": 20,
    "date": "2024-01-20",
    "reason": "Vente",
    "note": "Commande client #5678"
  }'
```

### 4. Obtenir le tableau de bord
```bash
curl -X GET http://localhost:8000/api/reports/dashboard \
  -H "Authorization: Bearer 1|abcdefghijklmnopqrstuvwxyz"
```
**Réponse :**
```json
{
  "success": true,
  "data": {
    "kpis": {
      "total_products": 150,
      "valeur_totale": 45000.00,
      "alertes_count": 5,
      "out_of_stock_count": 2
    },
    "recent_movements": [...],
    "chart": {
      "labels": ["2024-01-14", "2024-01-15", ..., "2024-01-20"],
      "entries": [50, 30, 0, 100, 20, 0, 45],
      "exits": [20, 15, 10, 5, 30, 25, 10]
    }
  }
}
```
