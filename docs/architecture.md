# Architecture Globale — RepairOS

**Produit :** RepairOS (SaaS de gestion des réparations & interventions techniques)  
**Version de conception :** 1.0  
**Stack de base :** PHP 8.4+, Laravel 12/11, MySQL 9 / 8.4 InnoDB utf8mb4, Blade, Tailwind CSS, Alpine.js, Vite.

---

## 1. Principes Directeurs d'Architecture

1. **Monolithe Modulaire & Épuré :**
   - Pas de sur-ingénierie microservices.
   - Les domaines métier sont isolés dans des Services (`app/Services/...`), Form Requests (`app/Http/Requests/...`), et Enums / Statuts stricts.
   - Contrôleurs allégés : ils orchestrent les requêtes, invoquent les services et renvoient des vues Blade ou des réponses JSON contrôlées.

2. **Stratégie Multi-Tenant en Base Partagée (Row-Level Security) :**
   - Chaque entité liée à un atelier porte une colonne `tenant_id` non nulle avec clé étrangère vers `tenants.id`.
   - Modèle d'appartenance `tenant_memberships` liant `users` à `tenants` avec un rôle explicite (`owner`, `admin`, `technician`).
   - `TenantScope` (Global Scope Eloquent) qui filtre automatiquement toutes les requêtes selon le tenant actif de la session ou du contexte d'exécution.
   - Injection automatique de `tenant_id` lors de la création d'un enregistrement via le trait `BelongsToTenant`.
   - Contrôle d'autorisation redondant dans les `Policies` de Laravel (Défense en profondeur : Scope SQL + Policy Gate).

3. **Protection des Données & Espace Public :**
   - Aucune URL interne ne permet de traverser les tenants par falsification d'ID (`IDOR`).
   - Le portail de suivi client public fonctionne sur un endpoint dédié (`/suivi/{token}`) totalement découplé des contrôleurs d'administration. Ce portail utilise un `public_tracking_token` cryptographiquement sécurisé (64 caractères hexadécimaux / alphanumériques) et une vue DTO/Resource stricte ne renvoyant aucune note interne ni information sensible.

4. **Gestion Financière Immuable :**
   - Devis et factures calculés à 100% côté serveur.
   - Les montants sont exprimés en entiers ou décimaux fixes (sans approximations de flottants binaires IEEE 754).
   - Les factures émises et les devis validés comportent un snapshot des éléments (lignes, prix, désignation, taux de taxe) afin qu'une modification ultérieure d'un catalogue n'altère jamais les documents comptables déjà produits.

---

## 2. Structure Modulaire Applicative

```
app/
├── Enums/
│   ├── RepairStatus.php           (RECU, DIAGNOSTIC, DEVIS_EN_ATTENTE, etc.)
│   ├── QuoteStatus.php            (BROUILLON, ENVOYE, ACCEPTE, REFUSE, EXPIRE, ANNULE)
│   ├── PaymentMethod.php          (ESPECES, MOBILE_MONEY, VIREMENT, CARTE, AUTRE)
│   ├── UserRole.php               (OWNER, ADMIN, TECHNICIAN)
│   └── TicketPriority.php         (BASSE, NORMALE, HAUTE, URGENTE)
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── DashboardController.php
│   │   ├── CustomerController.php
│   │   ├── DeviceController.php
│   │   ├── RepairTicketController.php
│   │   ├── RepairInterventionController.php
│   │   ├── QuoteController.php
│   │   ├── InvoiceController.php
│   │   ├── PaymentController.php
│   │   ├── AttachmentController.php
│   │   ├── PublicTrackingController.php
│   │   ├── WorkshopSettingsController.php
│   │   ├── TeamController.php
│   │   └── ReportController.php
│   ├── Middleware/
│   │   ├── SetCurrentTenant.php
│   │   ├── EnsureUserBelongsToTenant.php
│   │   └── CheckRolePermission.php
│   └── Requests/
├── Models/
│   ├── Tenant.php
│   ├── User.php
│   ├── TenantMembership.php
│   ├── Customer.php
│   ├── Device.php
│   ├── RepairTicket.php
│   ├── RepairStatusHistory.php
│   ├── RepairIntervention.php
│   ├── Quote.php
│   ├── QuoteItem.php
│   ├── Invoice.php
│   ├── InvoiceItem.php
│   ├── Payment.php
│   ├── Attachment.php
│   └── AuditLog.php
├── Services/
│   ├── TenantManager.php
│   ├── ReferenceGeneratorService.php
│   ├── RepairStateMachine.php
│   ├── QuoteService.php
│   ├── InvoiceService.php
│   ├── PaymentService.php
│   ├── DocumentPdfService.php
│   ├── NotificationMessageService.php
│   ├── AttachmentStorageService.php
│   └── AuditService.php
└── Traits/
    └── BelongsToTenant.php
```

---

## 3. Flux de Données & Isolation

```
[Requête Utilisateur Authentifié]
           │
           ▼
[Middleware SetCurrentTenant] 
  - Détermine le tenant_id actif (Session de l'atelier)
  - Vérifie que l'utilisateur appartient à ce tenant et est 'actif'
  - Initialise TenantManager::setTenant(...)
           │
           ▼
[Route & Form Request] 
  - Validation stricte des données
           │
           ▼
[Controller]
  - Appel d'une Policy ($this->authorize(...))
  - Invoque un Service Métier
           │
           ▼
[Services Métiers & Modèles Eloquent]
  - Trait BelongsToTenant active automatiquement le TenantScope (WHERE tenant_id = ?)
  - Transactions SQL pour toute modification financière ou de statut
  - AuditService journalise l'action
           │
           ▼
[Vue Blade / Réponse HTTP]
```
