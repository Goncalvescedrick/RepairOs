# RepairOS — Logiciel SaaS de Gestion d'Ateliers & Réparations

[![Tests](https://img.shields.io/badge/tests-passing-brightgreen.svg)]()
[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)]()
[![PHP](https://img.shields.io/badge/PHP-8.4-blue.svg)]()
[![Devise](https://img.shields.io/badge/Devise-XOF%20(FCFA)-emerald.svg)]()
[![Fuseau](https://img.shields.io/badge/Timezone-Africa%2FOuagadougou-purple.svg)]()

> **RepairOS** est un SaaS professionnel complet, sécurisé et multi-tenant conçu pour les réparateurs informatiques, électroniques et ateliers de maintenance technique.
>
> Initialement calibré pour le **Burkina Faso** et l'**Afrique francophone**, il centralise le cycle de vie complet d'une intervention :
>
> **Client ➔ Appareil ➔ Ticket (RECU) ➔ Diagnostic ➔ Devis ➔ Accord Client ➔ Réparation ➔ Tests ➔ Facturation ➔ Règlements (Mobile Money / Cash) ➔ Restitution.**

---

## 🚀 Proposition de Valeur

« *RepairOS vous permet de suivre chaque réparation, de mieux organiser votre atelier, de réduire les oublis et de professionnaliser la relation avec vos clients.* »

- **Zéro perte d'équipement** : bons de prise en charge imprimables avec photo de l'état entrant et accessoires déposés.
- **Suivi public en direct** : chaque client suit l'avancement de son appareil via un lien chiffré unique sans création de compte.
- **Notifications WhatsApp pré-remplies** : messages générés en français avec intégration en un clic vers WhatsApp Web / Mobile.
- **Rigueur financière** : devis verrouillés après acceptation, factures immuables, acomptes, soldes dus et reçus d'encaissement numérotés (`REC-YYYY-xxxx`).
- **Isolation stricte des ateliers** : architecture multi-tenant étanche avec séparation des données, politiques d'accès (Policies) et contrôle des rôles (`OWNER`, `ADMIN`, `TECHNICIAN`).

---

## 🛠️ Stack Technique

- **Backend** : PHP 8.4, Laravel 12.x, Eloquent ORM, Form Requests, Policies, Services métier dédiés.
- **Base de Données** : MySQL 8.4 LTS / MySQL 9.x, moteur InnoDB, encodage `utf8mb4_unicode_ci`, clés étrangères et transactions atomiques.
- **Frontend** : Blade Components réutilisables, Tailwind CSS, Alpine.js, Vite.
- **Calculs Financiers** : Précision décimale exacte (`DECIMAL(12,2)`), aucun flottant IEEE 754 binaire.
- **Stockage Sécurisé** : Stockage privé local (`storage/app/private`), validation stricte des types MIME réels, hash UUID et protection anti-directory traversal.

---

## 📦 Périmètre Fonctionnel (Modules A à N)

| Module | Description | Statut |
|---|---|---|
| **Module A — Authentification & Espaces** | Inscription atelier, provisionnement automatique du tenant, rôles, sessions, invitations | ✅ Opérationnel |
| **Module B — Tableau de bord** | KPIs temps réel : tickets ouverts, devis en attente, prêts à retirer, CA, impayés | ✅ Opérationnel |
| **Module C — Clients** | Fiches clients, téléphone normalisé, recherche instantanée, historique | ✅ Opérationnel |
| **Module D — Appareils** | Équipements rattachés aux clients, catégories, état esthétique, accessoires | ✅ Opérationnel |
| **Module E — Tickets de Réparation** | Références séquentielles atomiques (`TICK-YYYY-xxxx`), priorités, bon de dépôt imprimable | ✅ Opérationnel |
| **Module F — Cycle de Vie (State Machine)** | Machine à états stricte : 9 statuts contrôlés avec historique des transitions | ✅ Opérationnel |
| **Module G — Diagnostics & Interventions** | Relevé des pannes, temps passé, dissociation stricte rapport client / notes internes | ✅ Opérationnel |
| **Module H — Devis** | Devis chiffrés (`DEV-YYYY-xxxx`), lignes pièces & main d'œuvre, acceptation/refus client | ✅ Opérationnel |
| **Module I — Facturation & Paiements** | Factures figées (`FAC-YYYY-xxxx`), acomptes, espèces, Mobile Money, reçus de caisse | ✅ Opérationnel |
| **Module J — Photos & Pièces Jointes** | Téléversement privé, scan MIME, téléchargement sous autorisation, photos publiques/privées | ✅ Opérationnel |
| **Module K — Communication Client** | Modèles de messages en français, intégration WhatsApp (`wa.me`) sans fuite de secrets | ✅ Opérationnel |
| **Module L — Suivi Public Sécurisé** | Page `/suivi/{token}` avec jeton cryptographique 64 car., zéro divulgation de notes internes | ✅ Opérationnel |
| **Module M — Paramètres Atelier** | Identité, devise (`XOF`), fuseau horaire, taux TVA, mentions légales et de garde | ✅ Opérationnel |
| **Module N — Audit & Rapports** | Traces immuables des opérations sensibles, délais moyens de traitement, stats techniciens | ✅ Opérationnel |

---

## ⚙️ Installation & Démarrage Rapide

### 1. Cloner et installer les dépendances
```bash
git clone <URL_DEPOT>
cd Saas

# Dépendances PHP
composer install

# Dépendances JS et compilation des assets
npm install
npm run build
```

### 2. Configuration d'environnement
```bash
cp .env.example .env
php artisan key:generate
```

Renseigner les accès MySQL dans `.env` :
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=repair_os
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Migrations & Données de démonstration
```bash
# Créer les tables MySQL
php artisan migrate

# (Optionnel) Seeder de test
# php artisan db:seed
```

### 4. Lancer le serveur de développement
```bash
php artisan serve
```
L'application est accessible sur : `http://127.0.0.1:8000`

---

## 🧪 Tests Automatisés

Le projet inclut une suite de tests automatisés couvrant l'ensemble du cycle de vie et les règles de sécurité :

```bash
# Lancement de tous les tests
php artisan test
```

### Principaux scénarios testés :
- `tests/Feature/EndToEndRepairWorkflowTest.php` : Parcours complet en 15 étapes (dépôt, devis, réparation, facture, encaissement, reçu, suivi public, restitution).
- `tests/Feature/SecurityAndIsolationTest.php` : Cloisonnement inter-ateliers, tentative de falsification d'ID, protection des pièces jointes, rejet des trop-perçus, permissions rôles.
- `tests/Feature/TenantIsolationTest.php` : Scopes automatiques sur chaque requête Eloquent.
- `tests/Unit/FinancialCalculationsTest.php` : Précision des remises, taxes, imputations d'acomptes et soldes restant dus.
- `tests/Unit/RepairStateMachineTest.php` : Gardes de transition et interdictions de saut de statuts arbitraires.

---

## 📚 Documentation Complète

- [Architecture Système](docs/architecture.md)
- [Modèle de Données & Diagramme Relationnel](docs/modele-donnees.md)
- [Matrice des Permissions & Rôles](docs/permissions.md)
- [Workflows & Machine à États](docs/workflows.md)
- [Guide de Déploiement en Production](docs/deploiement.md)
- [Stratégie de Sauvegarde & PRA](docs/sauvegardes.md)
- [Guide d'Utilisation Atelier](docs/guide-utilisation.md)
- [Limitations Connues & Roadmap V2](docs/limitations.md)

---

## 📄 Licence

Logiciel propriétaire sous licence privée. Tous droits réservés.
