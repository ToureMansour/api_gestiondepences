# 📊 API de Gestion des Dépenses d'Entreprise

Une API REST robuste et sécurisée pour la gestion des dépenses d'entreprise, construite avec Laravel 11 et suivant une architecture en couches (Controller-Service-Repository).

## 🎯 Objectifs

- Gérer les utilisateurs (admin / employé)
- Gérer les dépenses avec workflow de validation
- Assurer la traçabilité complète
- Appliquer des règles métier strictes
- Servir un frontend web ou mobile

## 🏗️ Architecture

Le projet suit une architecture en couches propre :

```
app/
├── Http/
│   ├── Controllers/     # Couche de présentation
│   ├── Middleware/      # Middlewares (auth, rôles)
│   └── Requests/        # FormRequests pour la validation
├── Interfaces/         # Contrats des repositories
├── Models/             # Modèles Eloquent
├── Repositories/       # Implémentation des repositories
├── Services/           # Logique métier
├── Providers/          # Service Providers
└── Tests/              # Tests unitaires et fonctionnels
    ├── Unit/            # Tests des services et repositories
    └── Feature/         # Tests des controllers et API
```

## 🚀 Installation

### Prérequis

- PHP 8.2+
- Composer
- MySQL/MariaDB
- Laravel 11

### Étapes

1. **Cloner le projet**
```bash
git clone <repository-url>
cd api_gestiondepences
```

2. **Installer les dépendances**
```bash
composer install
```

3. **Configurer l'environnement**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurer la base de données**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=depensys
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Exécuter les migrations**
```bash
php artisan migrate
```

6. **Démarrer le serveur**
```bash
php artisan serve
```

## 📡 Endpoints API

### 🔐 Authentification

| Méthode | Endpoint | Description | Rôle |
|---------|----------|-------------|------|
| POST | `/api/register` | Créer un compte | Public |
| POST | `/api/login` | Se connecter | Public |
| POST | `/api/logout` | Se déconnecter | Authentifié |

### 👤 Utilisateurs

| Méthode | Endpoint | Description | Rôle |
|---------|----------|-------------|------|
| GET | `/api/profile` | Voir son profil | Tous |
| PUT | `/api/profile` | Modifier son profil | Tous |
| GET | `/api/users` | Lister tous les utilisateurs | Admin |
| GET | `/api/users/{id}` | Voir un utilisateur | Admin |

### 💰 Dépenses

| Méthode | Endpoint | Description | Rôle |
|---------|----------|-------------|------|
| GET | `/api/expenses` | Lister les dépenses | Tous |
| POST | `/api/expenses` | Créer une dépense | Tous |
| GET | `/api/expenses/{id}` | Voir une dépense | Tous |
| PUT | `/api/expenses/{id}` | Modifier une dépense | Propriétaire |
| DELETE | `/api/expenses/{id}` | Annuler une dépense | Propriétaire |

### 🔧 Actions Admin

| Méthode | Endpoint | Description | Rôle |
|---------|----------|-------------|------|
| POST | `/api/expenses/{id}/approve` | Approuver une dépense | Admin |
| POST | `/api/expenses/{id}/reject` | Refuser une dépense | Admin |
| POST | `/api/expenses/{id}/pay` | Marquer comme payée | Admin |

### 📊 Statistiques

| Méthode | Endpoint | Description | Rôle |
|---------|----------|-------------|------|
| GET | `/api/stats` | Obtenir les statistiques | Tous |

## 🔄 Workflow Métier

### Statuts des dépenses

1. **PENDING** : En attente de validation
2. **APPROVED** : Approuvée, en attente de paiement
3. **REJECTED** : Refusée avec motif
4. **PAID** : Payée
5. **CANCELLED** : Annulée par l'employé

### Règles métier

- ✅ Une dépense peut être modifiée uniquement si status = PENDING
- ✅ Seul un admin peut changer le statut
- ✅ Une dépense APPROVED ne peut plus être modifiée
- ✅ Une dépense PAID est verrouillée
- ✅ La suppression physique est interdite (utilise CANCELLED)

## 🧪 Tests

### Exécuter les tests
```bash
# Tests unitaires (services, repositories)
php artisan test --testsuite=Unit

# Tests fonctionnels (controllers, API)
php artisan test --testsuite=Feature

# Tous les tests
php artisan test

# Tests avec couverture
php artisan test --coverage
```

### Tests d'API

### Créer un utilisateur admin
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin User",
    "email": "admin@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "admin"
  }'
```

### Se connecter
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'
```

### Créer une dépense
```bash
curl -X POST http://localhost:8000/api/expenses \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "title=Restaurant" \
  -F "amount=45.50" \
  -F "description=Déjeuner client" \
  -F "expense_date=2024-04-14" \
  -F "proof=@/path/to/receipt.jpg"
```

## 🔐 Sécurité

- **Authentification** : Tokens Sanctum
- **Autorisation** : Middleware de rôles
- **Validation** : FormRequests avec messages personnalisés
- **Protection** : Upload de fichiers sécurisés
- **CORS** : Configuration adaptée

## 📝 Technologies Utilisées

- **Backend** : Laravel 11
- **Base de données** : MySQL
- **Authentification** : Laravel Sanctum
- **Architecture** : Controller-Service-Repository Pattern
- **Validation** : Form Requests
- **Tests** : PHPUnit (Unit + Feature)
- **Mocking** : Mockery
- **File Storage** : Local Storage

## 🚀 Évolutions Futures

- [ ] Multi-entreprise
- [ ] Abonnement SaaS
- [ ] API Mobile Money
- [ ] Notifications push
- [ ] Application mobile

## 📄 License

Ce projet est sous licence MIT.

---

**Développé avec en Laravel 11**
