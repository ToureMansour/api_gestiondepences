# Documentation API - Gestion des Dépenses

## Table des matières

1. [Authentification](#authentification)
2. [Utilisateurs](#utilisateurs)
3. [Dépenses](#dépenses)
4. [Actions Admin](#actions-admin)
5. [Statistiques](#statistiques)

---

## Authentification

### Inscription

**Requête**
- **Méthode**: POST
- **Web Service**: AuthController@register
- **URL**: `/api/register`
- **Paramètres**:
  - `name` (string, requis): Nom complet de l'utilisateur
  - `email` (string, requis): Email valide et unique
  - `password` (string, requis): Minimum 8 caractères
  - `password_confirmation` (string, requis): Confirmation du mot de passe
  - `role` (string, optionnel): "admin" ou "employee" (défaut: "employee")

**Réponse** (201 Created)
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "reference": "550e8400-e29b-41d4-a716-446655440000",
    "name": "John Doe",
    "email": "john@example.com",
    "role": "employee",
    "created_at": "2024-06-25T12:00:00.000000Z",
    "updated_at": "2024-06-25T12:00:00.000000Z"
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

**Codes d'erreur**:
- 422: Validation failed
- 500: Internal server error

---

### Connexion

**Requête**
- **Méthode**: POST
- **Web Service**: AuthController@login
- **URL**: `/api/login`
- **Paramètres**:
  - `email` (string, requis): Email de l'utilisateur
  - `password` (string, requis): Mot de passe de l'utilisateur

**Réponse** (200 OK)
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "id": 1,
    "reference": "550e8400-e29b-41d4-a716-446655440000",
    "name": "John Doe",
    "email": "john@example.com",
    "role": "employee",
    "created_at": "2024-06-25T12:00:00.000000Z",
    "updated_at": "2024-06-25T12:00:00.000000Z"
  },
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

**Codes d'erreur**:
- 401: Invalid credentials
- 422: Validation failed
- 500: Internal server error

---

### Déconnexion

**Requête**
- **Méthode**: POST
- **Web Service**: AuthController@logout
- **URL**: `/api/logout`
- **Headers**: `Authorization: Bearer {token}`
- **Paramètres**: Aucun

**Réponse** (200 OK)
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

**Codes d'erreur**:
- 401: Unauthenticated
- 500: Internal server error

---

## Utilisateurs

### Voir son profil

**Requête**
- **Méthode**: GET
- **Web Service**: UserController@profile
- **URL**: `/api/profile`
- **Headers**: `Authorization: Bearer {token}`
- **Paramètres**: Aucun

**Réponse** (200 OK)
```json
{
  "success": true,
  "message": "Profile retrieved successfully",
  "data": {
    "id": 1,
    "reference": "550e8400-e29b-41d4-a716-446655440000",
    "name": "John Doe",
    "email": "john@example.com",
    "role": "employee",
    "created_at": "2024-06-25T12:00:00.000000Z",
    "updated_at": "2024-06-25T12:00:00.000000Z"
  }
}
```

**Codes d'erreur**:
- 401: Unauthenticated
- 500: Internal server error

---

### Modifier son profil

**Requête**
- **Méthode**: PUT
- **Web Service**: UserController@updateProfile
- **URL**: `/api/profile`
- **Headers**: `Authorization: Bearer {token}`
- **Paramètres**:
  - `name` (string, optionnel): Nouveau nom
  - `email` (string, optionnel): Nouvel email (doit être unique)

**Réponse** (200 OK)
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "reference": "550e8400-e29b-41d4-a716-446655440000",
    "name": "John Updated",
    "email": "john.updated@example.com",
    "role": "employee",
    "created_at": "2024-06-25T12:00:00.000000Z",
    "updated_at": "2024-06-25T12:30:00.000000Z"
  }
}
```

**Codes d'erreur**:
- 401: Unauthenticated
- 422: Validation failed
- 500: Internal server error

---

### Lister tous les utilisateurs (Admin uniquement)

**Requête**
- **Méthode**: GET
- **Web Service**: UserController@index
- **URL**: `/api/users`
- **Headers**: `Authorization: Bearer {token}`
- **Paramètres**: Aucun

**Réponse** (200 OK)
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "reference": "550e8400-e29b-41d4-a716-446655440000",
        "name": "John Doe",
        "email": "john@example.com",
        "role": "employee",
        "created_at": "2024-06-25T12:00:00.000000Z",
        "updated_at": "2024-06-25T12:00:00.000000Z"
      }
    ],
    "first_page_url": "http://localhost:8000/api/users?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/users?page=1",
    "links": [...],
    "next_page_url": null,
    "path": "http://localhost:8000/api/users",
    "per_page": 10,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

**Codes d'erreur**:
- 401: Unauthenticated
- 403: Unauthorized (non-admin)
- 500: Internal server error

---

### Voir un utilisateur (Admin uniquement)

**Requête**
- **Méthode**: GET
- **Web Service**: UserController@show
- **URL**: `/api/users/{userReference}`
- **Headers**: `Authorization: Bearer {token}`
- **Paramètres**:
  - `userReference` (string, requis): UUID de l'utilisateur

**Réponse** (200 OK)
```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "id": 1,
    "reference": "550e8400-e29b-41d4-a716-446655440000",
    "name": "John Doe",
    "email": "john@example.com",
    "role": "employee",
    "created_at": "2024-06-25T12:00:00.000000Z",
    "updated_at": "2024-06-25T12:00:00.000000Z"
  }
}
```

**Codes d'erreur**:
- 401: Unauthenticated
- 403: Unauthorized (non-admin)
- 404: User not found
- 500: Internal server error

---

## Dépenses

### Lister les dépenses

**Requête**
- **Méthode**: GET
- **Web Service**: ExpenseController@index
- **URL**: `/api/expenses`
- **Headers**: `Authorization: Bearer {token}`
- **Paramètres** (query string):
  - `per_page` (integer, optionnel): Nombre par page (défaut: 10)
  - `status` (string, optionnel): Filtre par statut (PENDING, APPROVED, REJECTED, PAID, CANCELLED)
  - `user_id` (integer, optionnel): Filtre par utilisateur (admin uniquement)
  - `date_from` (date, optionnel): Date de début (format: YYYY-MM-DD)
  - `date_to` (date, optionnel): Date de fin (format: YYYY-MM-DD)
  - `amount_min` (decimal, optionnel): Montant minimum
  - `amount_max` (decimal, optionnel): Montant maximum

**Réponse** (200 OK)
```json
{
  "success": true,
  "message": "Expenses retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "reference": "660e8400-e29b-41d4-a716-446655440001",
        "user_id": 1,
        "title": "Restaurant",
        "amount": "45.50",
        "description": "Déjeuner client",
        "proof_file_path": "expenses/xxxxxxxxxxxx.jpg",
        "status": "PENDING",
        "rejection_reason": null,
        "payment_method": null,
        "payment_reference": null,
        "paid_at": null,
        "expense_date": "2024-06-25",
        "created_at": "2024-06-25T12:00:00.000000Z",
        "updated_at": "2024-06-25T12:00:00.000000Z",
        "user": {
          "id": 1,
          "reference": "550e8400-e29b-41d4-a716-446655440000",
          "name": "John Doe",
          "email": "john@example.com"
        }
      }
    ],
    "first_page_url": "http://localhost:8000/api/expenses?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost:8000/api/expenses?page=1",
    "links": [...],
    "next_page_url": null,
    "path": "http://localhost:8000/api/expenses",
    "per_page": 10,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

**Codes d'erreur**:
- 401: Unauthenticated
- 500: Internal server error

---

### Créer une dépense

**Requête**
- **Méthode**: POST
- **Web Service**: ExpenseController@store
- **URL**: `/api/expenses`
- **Headers**: `Authorization: Bearer {token}`, `Content-Type: multipart/form-data`
- **Paramètres**:
  - `title` (string, requis): Titre de la dépense (max 255 caractères)
  - `amount` (decimal, requis): Montant (min 0.01, max 999999.99)
  - `description` (string, optionnel): Description (max 1000 caractères)
  - `expense_date` (date, requis): Date de la dépense (format: YYYY-MM-DD, ne peut pas être dans le futur)
  - `proof` (file, requis): Justificatif (jpeg, jpg, png, pdf, max 2MB)

**Réponse** (201 Created)
```json
{
  "success": true,
  "message": "Expense created successfully",
  "data": {
    "id": 1,
    "reference": "660e8400-e29b-41d4-a716-446655440001",
    "user_id": 1,
    "title": "Restaurant",
    "amount": "45.50",
    "description": "Déjeuner client",
    "proof_file_path": "expenses/xxxxxxxxxxxx.jpg",
    "status": "PENDING",
    "rejection_reason": null,
    "payment_method": null,
    "payment_reference": null,
    "paid_at": null,
    "expense_date": "2024-06-25",
    "created_at": "2024-06-25T12:00:00.000000Z",
    "updated_at": "2024-06-25T12:00:00.000000Z",
    "user": {
      "id": 1,
      "reference": "550e8400-e29b-41d4-a716-446655440000",
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

**Codes d'erreur**:
- 401: Unauthenticated
- 422: Validation failed
- 500: Internal server error

---

### Voir une dépense

**Requête**
- **Méthode**: GET
- **Web Service**: ExpenseController@show
- **URL**: `/api/expenses/{expenseReference}`
- **Headers**: `Authorization: Bearer {token}`
- **Paramètres**:
  - `expenseReference` (string, requis): UUID de la dépense

**Réponse** (200 OK)
```json
{
  "success": true,
  "message": "Expense retrieved successfully",
  "data": {
    "id": 1,
    "reference": "660e8400-e29b-41d4-a716-446655440001",
    "user_id": 1,
    "title": "Restaurant",
    "amount": "45.50",
    "description": "Déjeuner client",
    "proof_file_path": "expenses/xxxxxxxxxxxx.jpg",
    "status": "PENDING",
    "rejection_reason": null,
    "payment_method": null,
    "payment_reference": null,
    "paid_at": null,
    "expense_date": "2024-06-25",
    "created_at": "2024-06-25T12:00:00.000000Z",
    "updated_at": "2024-06-25T12:00:00.000000Z",
    "user": {
      "id": 1,
      "reference": "550e8400-e29b-41d4-a716-446655440000",
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

**Codes d'erreur**:
- 401: Unauthenticated
- 403: Unauthorized (accès à une dépense d'un autre utilisateur)
- 404: Expense not found
- 500: Internal server error

---

### Modifier une dépense

**Requête**
- **Méthode**: PUT
- **Web Service**: ExpenseController@update
- **URL**: `/api/expenses/{expenseReference}`
- **Headers**: `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Paramètres**:
  - `expenseReference` (string, requis): UUID de la dépense
  - `title` (string, optionnel): Nouveau titre
  - `amount` (decimal, optionnel): Nouveau montant
  - `description` (string, optionnel): Nouvelle description
  - `expense_date` (date, optionnel): Nouvelle date

**Note**: La dépense doit avoir le statut PENDING pour être modifiée. Seul le propriétaire peut modifier sa dépense.

**Réponse** (200 OK)
```json
{
  "success": true,
  "message": "Expense updated successfully",
  "data": {
    "id": 1,
    "reference": "660e8400-e29b-41d4-a716-446655440001",
    "user_id": 1,
    "title": "Restaurant Updated",
    "amount": "50.00",
    "description": "Déjeuner client mis à jour",
    "proof_file_path": "expenses/xxxxxxxxxxxx.jpg",
    "status": "PENDING",
    "rejection_reason": null,
    "payment_method": null,
    "payment_reference": null,
    "paid_at": null,
    "expense_date": "2024-06-25",
    "created_at": "2024-06-25T12:00:00.000000Z",
    "updated_at": "2024-06-25T12:30:00.000000Z",
    "user": {
      "id": 1,
      "reference": "550e8400-e29b-41d4-a716-446655440000",
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

**Codes d'erreur**:
- 401: Unauthenticated
- 403: Unauthorized (pas le propriétaire ou statut non PENDING)
- 404: Expense not found
- 422: Validation failed
- 500: Internal server error

---

### Annuler une dépense

**Requête**
- **Méthode**: DELETE
- **Web Service**: ExpenseController@destroy
- **URL**: `/api/expenses/{expenseReference}`
- **Headers**: `Authorization: Bearer {token}`
- **Paramètres**:
  - `expenseReference` (string, requis): UUID de la dépense

**Note**: La dépense doit avoir le statut PENDING pour être annulée. Seul le propriétaire peut annuler sa dépense. La suppression est logique (statut passe à CANCELLED).

**Réponse** (200 OK)
```json
{
  "success": true,
  "message": "Expense cancelled successfully",
  "data": {
    "id": 1,
    "reference": "660e8400-e29b-41d4-a716-446655440001",
    "user_id": 1,
    "title": "Restaurant",
    "amount": "45.50",
    "description": "Déjeuner client",
    "proof_file_path": "expenses/xxxxxxxxxxxx.jpg",
    "status": "CANCELLED",
    "rejection_reason": null,
    "payment_method": null,
    "payment_reference": null,
    "paid_at": null,
    "expense_date": "2024-06-25",
    "created_at": "2024-06-25T12:00:00.000000Z",
    "updated_at": "2024-06-25T12:30:00.000000Z",
    "user": {
      "id": 1,
      "reference": "550e8400-e29b-41d4-a716-446655440000",
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

**Codes d'erreur**:
- 401: Unauthenticated
- 403: Unauthorized (pas le propriétaire ou statut non PENDING)
- 404: Expense not found
- 422: Cannot cancel expense with current status
- 500: Internal server error

---

## Actions Admin

### Approuver une dépense (Admin uniquement)

**Requête**
- **Méthode**: POST
- **Web Service**: AdminExpenseController@approve
- **URL**: `/api/expenses/{expenseReference}/approve`
- **Headers**: `Authorization: Bearer {token}`
- **Paramètres**:
  - `expenseReference` (string, requis): UUID de la dépense

**Note**: La dépense doit avoir le statut PENDING.

**Réponse** (200 OK)
```json
{
  "success": true,
  "message": "Expense approved successfully",
  "data": {
    "id": 1,
    "reference": "660e8400-e29b-41d4-a716-446655440001",
    "user_id": 1,
    "title": "Restaurant",
    "amount": "45.50",
    "description": "Déjeuner client",
    "proof_file_path": "expenses/xxxxxxxxxxxx.jpg",
    "status": "APPROVED",
    "rejection_reason": null,
    "payment_method": null,
    "payment_reference": null,
    "paid_at": null,
    "expense_date": "2024-06-25",
    "created_at": "2024-06-25T12:00:00.000000Z",
    "updated_at": "2024-06-25T12:30:00.000000Z",
    "user": {
      "id": 1,
      "reference": "550e8400-e29b-41d4-a716-446655440000",
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

**Codes d'erreur**:
- 401: Unauthenticated
- 403: Unauthorized (non-admin)
- 404: Expense not found
- 422: Cannot approve expense with current status
- 500: Internal server error

---

### Rejeter une dépense (Admin uniquement)

**Requête**
- **Méthode**: POST
- **Web Service**: AdminExpenseController@reject
- **URL**: `/api/expenses/{expenseReference}/reject`
- **Headers**: `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Paramètres**:
  - `expenseReference` (string, requis): UUID de la dépense
  - `reason` (string, requis): Motif du rejet (max 500 caractères)

**Note**: La dépense doit avoir le statut PENDING.

**Réponse** (200 OK)
```json
{
  "success": true,
  "message": "Expense rejected successfully",
  "data": {
    "id": 1,
    "reference": "660e8400-e29b-41d4-a716-446655440001",
    "user_id": 1,
    "title": "Restaurant",
    "amount": "45.50",
    "description": "Déjeuner client",
    "proof_file_path": "expenses/xxxxxxxxxxxx.jpg",
    "status": "REJECTED",
    "rejection_reason": "Justificatif illisible",
    "payment_method": null,
    "payment_reference": null,
    "paid_at": null,
    "expense_date": "2024-06-25",
    "created_at": "2024-06-25T12:00:00.000000Z",
    "updated_at": "2024-06-25T12:30:00.000000Z",
    "user": {
      "id": 1,
      "reference": "550e8400-e29b-41d4-a716-446655440000",
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

**Codes d'erreur**:
- 401: Unauthenticated
- 403: Unauthorized (non-admin)
- 404: Expense not found
- 422: Validation failed ou Cannot reject expense with current status
- 500: Internal server error

---

### Marquer comme payée (Admin uniquement)

**Requête**
- **Méthode**: POST
- **Web Service**: AdminExpenseController@pay
- **URL**: `/api/expenses/{expenseReference}/pay`
- **Headers**: `Authorization: Bearer {token}`, `Content-Type: application/json`
- **Paramètres**:
  - `expenseReference` (string, requis): UUID de la dépense
  - `payment_method` (string, requis): Méthode de paiement ("cash", "mobile_money", "transfer")
  - `reference` (string, optionnel): Référence du paiement (max 255 caractères)
  - `paid_at` (date, optionnel): Date du paiement (format: YYYY-MM-DD HH:MM:SS)

**Note**: La dépense doit avoir le statut APPROVED.

**Réponse** (200 OK)
```json
{
  "success": true,
  "message": "Expense marked as paid successfully",
  "data": {
    "id": 1,
    "reference": "660e8400-e29b-41d4-a716-446655440001",
    "user_id": 1,
    "title": "Restaurant",
    "amount": "45.50",
    "description": "Déjeuner client",
    "proof_file_path": "expenses/xxxxxxxxxxxx.jpg",
    "status": "PAID",
    "rejection_reason": null,
    "payment_method": "transfer",
    "payment_reference": "REF-123456",
    "paid_at": "2024-06-25T14:30:00.000000Z",
    "expense_date": "2024-06-25",
    "created_at": "2024-06-25T12:00:00.000000Z",
    "updated_at": "2024-06-25T14:30:00.000000Z",
    "user": {
      "id": 1,
      "reference": "550e8400-e29b-41d4-a716-446655440000",
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

**Codes d'erreur**:
- 401: Unauthenticated
- 403: Unauthorized (non-admin)
- 404: Expense not found
- 422: Validation failed ou Cannot mark as paid expense with current status
- 500: Internal server error

---

## Statistiques

### Obtenir les statistiques

**Requête**
- **Méthode**: GET
- **Web Service**: StatsController@index
- **URL**: `/api/stats`
- **Headers**: `Authorization: Bearer {token}`
- **Paramètres**: Aucun

**Réponse** (200 OK)
```json
{
  "success": true,
  "message": "Statistics retrieved successfully",
  "data": {
    "total_expenses": 150,
    "pending_count": 25,
    "approved_count": 80,
    "rejected_count": 30,
    "paid_count": 15,
    "cancelled_count": 0,
    "total_amount": "12500.50",
    "pending_amount": "2500.00",
    "approved_amount": "8000.00",
    "paid_amount": "2000.50",
    "rejected_amount": "0.00"
  }
}
```

**Codes d'erreur**:
- 401: Unauthenticated
- 500: Internal server error

---

## Notes importantes

### Authentification
- Toutes les requêtes (sauf register et login) nécessitent un token Bearer dans le header `Authorization`
- Le token est obtenu lors de la connexion et doit être inclus dans chaque requête protégée

### UUID References
- Chaque utilisateur et dépense possède un `reference` (UUID) unique
- Ce `reference` est utilisé dans les URLs pour les opérations de lecture, modification et suppression
- L'ID numérique interne est toujours présent mais ne doit pas être utilisé côté frontend

### Statuts des dépenses
- **PENDING**: En attente de validation (modifiable par le propriétaire)
- **APPROVED**: Approuvée, en attente de paiement (non modifiable)
- **REJECTED**: Refusée avec motif (non modifiable)
- **PAID**: Payée (verrouillée)
- **CANCELLED**: Annulée par l'employé (non modifiable)

### Rôles
- **admin**: Accès complet à toutes les fonctionnalités
- **employee**: Accès limité à ses propres dépenses et profil

### Validation
- Tous les champs requis sont validés côté serveur
- Les messages d'erreur sont en français
- Les fichiers uploadés sont limités à 2MB

### Pagination
- Les listes utilisent la pagination Laravel
- Par défaut: 10 éléments par page
- Le paramètre `per_page` peut être ajusté

### Codes d'erreur HTTP
- **200**: Success
- **201**: Created
- **400**: Bad Request
- **401**: Unauthenticated
- **403**: Unauthorized
- **404**: Not Found
- **422**: Validation Error
- **500**: Internal Server Error
