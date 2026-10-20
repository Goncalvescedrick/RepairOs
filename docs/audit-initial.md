# Audit Initial de l'Environnement — RepairOS

**Date de réalisation :** 2026-10-20  
**Auteur :** Antigravity (Agent d'ingénierie logicielle & architecture)  
**Emplacement du projet :** `c:\Users\King\Desktop\Saas`

---

## 1. État des Lieux de l'Espace de Travail

- **Répertoire racine :** `c:\Users\King\Desktop\Saas`
- **Fichiers préexistants :** Aucun (répertoire initialement vide).
- **Fichiers ou code à préserver :** Aucun code antérieur à préserver dans cet espace.
- **Dépôt Git :** Non initialisé pour l'instant.

---

## 2. Inventaire des Outils et Runtimes Détectés

Les vérifications directes en ligne de commande ont confirmé les versions et états suivants :

| Outil / Composant | Version détectée | Chemin / Notes |
|---|---|---|
| **PHP** | `8.4.12 (cli)` NTS Visual C++ 2022 x64 | `C:\laragon\bin\php\php-8.4.12-nts-Win32-vs17-x64\php.exe` |
| **Composer** | `2.10.3` | Opérationnel sous PHP 8.4.12 |
| **Node.js** | `v26.10.0` | 64-bit Windows |
| **npm** | `12.2.0` | Accessible via `npm.cmd` (la politique PowerShell bloque `npm.ps1`) |
| **Git** | `2.56.0.windows.2` | Opérationnel |
| **MySQL Server** | `9.6.0` (Laragon) | Connecté avec succès sur `127.0.0.1:3306` (processus `mysqld.exe` actif) |
| **SQLite (fallback/test)** | `pdo_sqlite` & `sqlite3` actifs | Inclus dans l'extension PHP CLI |

### Extensions PHP Notables Disponibles
- `bcmath` : Calculs de précision pour devises et montants (critique pour XOF/FCFA).
- `pdo_mysql` : Driver MySQL pour Laravel Eloquent.
- `pdo_sqlite` : Driver SQLite pour tests rapides en mémoire/fichier isolé.
- `fileinfo` : Validation MIME stricte côté serveur pour pièces jointes et photos.
- `gd` : Traitement d'images (redimensionnement, conversion).
- `intl` : Formatage localisé (devises, dates).
- `openssl` & `sodium` : Chiffrement, tokens aléatoires sécurisés pour le suivi public.
- `zip` & `xml` : Utiles pour exports et manipulation de documents.

---

## 3. Contraintes Techniques et Stratégie d'Adaptation

1. **Exécution des scripts Node sous PowerShell :**
   - *Constat :* La politique d'exécution Windows restreint `npm.ps1`.
   - *Solution :* Exécuter systématiquement les commandes Node via `npm.cmd` ou `npx.cmd`.

2. **Connexion MySQL :**
   - *Constat :* La connexion sur le nom d'hôte `localhost` en socket/pipe Windows échoue parfois si IPv6 n'est pas mappé uniformément, alors que `127.0.0.1:3306` répond instantanément (MySQL 9.6.0).
   - *Solution :* Configurer explicitement `DB_HOST=127.0.0.1` et `DB_PORT=3306` dans `.env`.

3. **Version Laravel :**
   - Avec PHP 8.4.12, **Laravel 11.x ou 12.x** est le standard de production moderne supportant pleinement PHP 8.4.
   - Nous allons initialiser une structure Laravel propre et robuste répondant à toutes les exigences fonctionnelles et architecturales.

4. **Multi-tenancy :**
   - Choix validé conforme au cahier de pilotage : Base de données partagée avec colonne `tenant_id` sur chaque entité atelier, global scopes Eloquent automatiques, middleware de résolution sécurisée du tenant courant, et vérification stricte par Policies/Gates.

---

## 4. Risques Techniques Initiaux et Mitigations

- **Fuite inter-tenant :** Risque critique n°1. Atténué par Global Scope `TenantScope`, association stricte au modèle `Tenant`, contrôle systématique dans les Policies et tests unitaires / d'intégration simulant des attaques transverses (Tenant A tentant d'accéder aux données du Tenant B).
- **Imprécision financière :** Risque d'arrondi ou de manipulation flottante. Atténué par l'interdiction des `float`, utilisation d'entiers ou décimaux stricts (XOF n'a pas de sous-unité de centimes en pratique usuelle mais nous gérons des entiers XOF / décimaux exacts), et validation des totaux côté serveur.
- **Double facturation / collision de référence :** Risque de concurrence. Atténué par transactions SQL, verrouillage pessimiste (`lockForUpdate`) et index d'unicité `(tenant_id, reference)`.

---

## 5. Décision de passage à la Phase 1 & 2

L'environnement est sain, récent, et dispose de tous les prérequis nécessaires pour développer, tester et exécuter RepairOS sans blocage.
