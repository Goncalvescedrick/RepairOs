# Modèle de Données & Schéma Relationnel — RepairOS

**Moteur de base de données :** MySQL 9.6 / 8.4+ InnoDB  
**Encodage :** `utf8mb4_unicode_ci`  
**Stratégie de clés :** Unsigned BIGINT ou UUID / BIGINT pour id, clé étrangère explicite avec indexation.  
**Conventions de suppression :** `SoftDeletes` sur les entités clientes, appareils, tickets, devis pour préserver l'historique légal et comptable.

---

## 1. Tables du Système

### `tenants` (Ateliers)
| Colonne | Type | Nullable | Index / Contraintes | Description |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | NON | PRIMARY KEY | Identifiant unique de l'atelier |
| `name` | VARCHAR(191) | NON | | Nom de l'atelier |
| `slug` | VARCHAR(191) | NON | UNIQUE | Identifiant URL lisible |
| `phone` | VARCHAR(50) | OUI | | Téléphone commercial |
| `email` | VARCHAR(191) | OUI | | E-mail de contact |
| `address` | TEXT | OUI | | Adresse physique |
| `city` | VARCHAR(100) | OUI | | Ville (ex: Ouagadougou) |
| `country_code` | VARCHAR(10) | NON | DEFAULT 'BF' | Code pays ISO |
| `currency` | VARCHAR(10) | NON | DEFAULT 'XOF' | Devise par défaut |
| `timezone` | VARCHAR(50) | NON | DEFAULT 'Africa/Ouagadougou' | Fuseau horaire |
| `ticket_prefix` | VARCHAR(10) | NON | DEFAULT 'TICK' | Préfixe des tickets |
| `quote_prefix` | VARCHAR(10) | NON | DEFAULT 'DEV' | Préfixe des devis |
| `invoice_prefix` | VARCHAR(10) | NON | DEFAULT 'FAC' | Préfixe des factures |
| `tax_rate` | DECIMAL(5,2) | NON | DEFAULT 0.00 | Taux de taxe/TVA par défaut en % |
| `legal_notices` | TEXT | OUI | | Mentions légales / conditions |
| `is_active` | BOOLEAN | NON | DEFAULT 1 | Statut actif |
| `created_at` / `updated_at` | TIMESTAMP | OUI | | |

---

### `users` (Utilisateurs Globaux)
| Colonne | Type | Nullable | Index / Contraintes | Description |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | NON | PRIMARY KEY | Identifiant utilisateur |
| `name` | VARCHAR(191) | NON | | Nom complet |
| `email` | VARCHAR(191) | NON | UNIQUE | Adresse e-mail |
| `phone` | VARCHAR(50) | OUI | | Numéro de téléphone |
| `password` | VARCHAR(255) | NON | | Mot de passe haché (Bcrypt / Argon2id) |
| `remember_token` | VARCHAR(100) | OUI | | |
| `created_at` / `updated_at` | TIMESTAMP | OUI | | |

---

### `tenant_memberships` (Appartenances & Rôles dans un Atelier)
| Colonne | Type | Nullable | Index / Contraintes | Description |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | NON | PRIMARY KEY | |
| `tenant_id` | BIGINT UNSIGNED | NON | FOREIGN KEY (tenants) | Atelier concerné |
| `user_id` | BIGINT UNSIGNED | NON | FOREIGN KEY (users) | Collaborateur |
| `role` | VARCHAR(30) | NON | | `owner`, `admin`, `technician` |
| `is_active` | BOOLEAN | NON | DEFAULT 1 | Permet de révoquer un accès sans supprimer le compte |
| `created_at` / `updated_at` | TIMESTAMP | OUI | | |
| UNIQUE KEY: `(tenant_id, user_id)` |

---

### `customers` (Fiches Clients de l'Atelier)
| Colonne | Type | Nullable | Index / Contraintes | Description |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | NON | PRIMARY KEY | |
| `tenant_id` | BIGINT UNSIGNED | NON | FOREIGN KEY (tenants) | Isolation tenant |
| `name` | VARCHAR(191) | NON | INDEX (tenant_id, name) | Nom / Prénom ou Entreprise |
| `phone` | VARCHAR(50) | NON | INDEX (tenant_id, phone) | Téléphone principal (ex: +226 xx xx xx xx) |
| `email` | VARCHAR(191) | OUI | | E-mail optionnel |
| `address` | TEXT | OUI | | Adresse physique |
| `notes` | TEXT | OUI | | Remarques internes |
| `created_at` / `updated_at` / `deleted_at` | TIMESTAMP | OUI | | SoftDeletes activé |

---

### `devices` (Appareils Déposés)
| Colonne | Type | Nullable | Index / Contraintes | Description |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | NON | PRIMARY KEY | |
| `tenant_id` | BIGINT UNSIGNED | NON | FOREIGN KEY (tenants) | Isolation tenant |
| `customer_id` | BIGINT UNSIGNED | NON | FOREIGN KEY (customers) | Propriétaire |
| `category` | VARCHAR(50) | NON | | `laptop`, `desktop`, `printer`, `network`, `other` |
| `brand` | VARCHAR(100) | NON | | Marque (HP, Dell, Lenovo, Epson...) |
| `model` | VARCHAR(100) | NON | | Modèle précis |
| `serial_number` | VARCHAR(100) | OUI | INDEX (tenant_id, serial_number) | N° de série |
| `aesthetic_condition` | TEXT | OUI | | Rayures, fissures, traces constatées |
| `accessories` | TEXT | OUI | | Chargeur, sacoche, câble, etc. |
| `notes` | TEXT | OUI | | |
| `created_at` / `updated_at` / `deleted_at` | TIMESTAMP | OUI | | SoftDeletes activé |

---

### `repair_tickets` (Tickets de Réparation — Objet Central)
| Colonne | Type | Nullable | Index / Contraintes | Description |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | NON | PRIMARY KEY | |
| `tenant_id` | BIGINT UNSIGNED | NON | FOREIGN KEY (tenants) | Isolation tenant |
| `reference` | VARCHAR(50) | NON | UNIQUE (tenant_id, reference) | Réf lisible ex: `TICK-2026-0001` |
| `customer_id` | BIGINT UNSIGNED | NON | FOREIGN KEY (customers) | |
| `device_id` | BIGINT UNSIGNED | NON | FOREIGN KEY (devices) | |
| `assigned_to_user_id` | BIGINT UNSIGNED | OUI | FOREIGN KEY (users) | Technicien assigné |
| `status` | VARCHAR(40) | NON | INDEX (tenant_id, status) | Statut de la machine d'état |
| `priority` | VARCHAR(20) | NON | DEFAULT 'NORMALE' | `BASSE`, `NORMALE`, `HAUTE`, `URGENTE` |
| `reported_issue` | TEXT | NON | | Problème déclaré par le client |
| `intake_condition` | TEXT | OUI | | État constaté à la réception |
| `accessories_received` | TEXT | OUI | | |
| `deposit_date` | DATETIME | NON | | Date/heure de dépôt |
| `estimated_completion_date` | DATETIME | OUI | | Date prévisionnelle |
| `closed_at` | DATETIME | OUI | | Date de restitution ou annulation |
| `internal_notes` | TEXT | OUI | | Confidentiel technicien / atelier |
| `client_notes` | TEXT | OUI | | Communication visible client |
| `public_tracking_token` | VARCHAR(80) | NON | UNIQUE | Token cryptographique non prédictible |
| `created_at` / `updated_at` / `deleted_at` | TIMESTAMP | OUI | | SoftDeletes activé |

---

### `repair_status_histories` (Traçabilité des Transitions de Statut)
| Colonne | Type | Nullable | Index / Contraintes | Description |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | NON | PRIMARY KEY | |
| `tenant_id` | BIGINT UNSIGNED | NON | FOREIGN KEY (tenants) | |
| `repair_ticket_id` | BIGINT UNSIGNED | NON | FOREIGN KEY (repair_tickets) | |
| `user_id` | BIGINT UNSIGNED | OUI | FOREIGN KEY (users) | Auteur du changement |
| `from_status` | VARCHAR(40) | OUI | | Statut initial |
| `to_status` | VARCHAR(40) | NON | | Nouveau statut |
| `reason` | VARCHAR(255) | OUI | | Motif (obligatoire si annulation/refus) |
| `created_at` | TIMESTAMP | NON | | Date précise UTC |

---

### `repair_interventions` (Observations Techniques et Travaux)
| Colonne | Type | Nullable | Index / Contraintes | Description |
|---|---|---|---|---|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | NON | PRIMARY KEY | |
| `tenant_id` | BIGINT UNSIGNED | NON | FOREIGN KEY (tenants) | |
| `repair_ticket_id` | BIGINT UNSIGNED | NON | FOREIGN KEY (repair_tickets) | |
| `user_id` | BIGINT UNSIGNED | NON | FOREIGN KEY (users) | Technicien ayant opéré |
| `intervention_type` | VARCHAR(50) | NON | | `DIAGNOSTIC`, `OPERATION`, `TEST`, `AUTRE` |
| `symptoms_observed` | TEXT | OUI | | Symptômes techniques constatés |
| `diagnosis` | TEXT | OUI | | Diagnostic technique approfondi |
| `identified_cause` | TEXT | OUI | | Cause racine identifiée |
| `work_performed` | TEXT | NON | | Description du travail réalisé |
| `test_results` | TEXT | OUI | | Résultat des tests après intervention |
| `duration_minutes` | INT UNSIGNED | OUI | | Temps passé en minutes |
| `internal_notes` | TEXT | OUI | | Strictement interne |
| `customer_summary` | TEXT | OUI | | Synthèse visible par le client |
| `created_at` / `updated_at` | TIMESTAMP | OUI | | |

---

### `quotes` (Devis) & `quote_items` (Lignes de Devis)
**Table `quotes` :**
- `id`, `tenant_id`, `repair_ticket_id`, `customer_id`, `reference` (UNIQUE tenant_id, reference)
- `status` (`BROUILLON`, `ENVOYE`, `ACCEPTE`, `REFUSE`, `EXPIRE`, `ANNULE`)
- `subtotal_amount` (DECIMAL 12,2 ou BIGINT)
- `tax_rate` (DECIMAL 5,2)
- `tax_amount` (DECIMAL 12,2)
- `discount_amount` (DECIMAL 12,2 DEFAULT 0)
- `total_amount` (DECIMAL 12,2)
- `currency` (VARCHAR 10)
- `valid_until` (DATE)
- `sent_at`, `accepted_at`, `refused_at`, `refusal_reason`
- `notes`

**Table `quote_items` :**
- `id`, `quote_id`, `type` (`SERVICE`, `PART`, `OTHER`), `description`, `quantity`, `unit_price`, `total_price`

---

### `invoices` (Factures) & `invoice_items` (Lignes de Factures)
**Table `invoices` :**
- `id`, `tenant_id`, `repair_ticket_id`, `quote_id` (nullable), `customer_id`
- `reference` (UNIQUE tenant_id, reference, ex: `FAC-2026-0001`)
- `status` (`EMISE`, `PARTIELLEMENT_PAYEE`, `PAYEE`, `ANNULEE`)
- `subtotal_amount`, `tax_rate`, `tax_amount`, `discount_amount`, `total_amount`
- `paid_amount` (DECIMAL 12,2 DEFAULT 0.00)
- `balance_due` (DECIMAL 12,2)
- `currency`, `due_date`, `issued_at`, `paid_at`, `notes`

**Table `invoice_items` :**
- `id`, `invoice_id`, `type`, `description`, `quantity`, `unit_price`, `total_price`

---

### `payments` (Paiements et Règlements)
- `id`, `tenant_id`, `invoice_id`, `repair_ticket_id`, `user_id` (qui a encaissé)
- `reference` (UNIQUE tenant_id, reference, ex: `REC-2026-0001` pour le reçu)
- `amount` (DECIMAL 12,2)
- `payment_method` (`ESPECES`, `MOBILE_MONEY`, `VIREMENT`, `CARTE`, `AUTRE`)
- `transaction_reference` (optionnel, ex: réf transaction Orange Money / Moov Money / Wave)
- `paid_at` (DATETIME)
- `notes`
- `is_cancelled` (BOOLEAN DEFAULT 0), `cancellation_reason`, `cancelled_by_user_id`

---

### `attachments` (Photos et Fichiers Justificatifs)
- `id`, `tenant_id`, `attachable_type`, `attachable_id` (Polymorphic: Ticket, Device, etc.)
- `user_id` (qui a téléversé)
- `original_filename`
- `storage_path` (chemin sécurisé dans `storage/app/private/tenants/...`)
- `mime_type`
- `file_size`
- `file_category` (`INTAKE_PHOTO`, `DEVICE_PHOTO`, `DIAGNOSTIC_DOCUMENT`, `PROOF`)
- `is_public_to_client` (BOOLEAN DEFAULT 0 - si l'atelier choisit de rendre la photo visible dans le suivi client)

---

### `audit_logs` (Journal d'Audit de Sécurité)
- `id`, `tenant_id` (nullable pour les échecs de connexion avant détection), `user_id` (nullable)
- `event` (ex: `ticket.created`, `status.transitioned`, `invoice.issued`, `payment.recorded`, `auth.failed`)
- `auditable_type`, `auditable_id`
- `ip_address`, `user_agent`
- `old_values` (JSON), `new_values` (JSON - sans secrets ni mots de passe)
- `created_at`
