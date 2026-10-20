# Registre des Décisions Techniques (ADR) — RepairOS

---

## ADR 01 : Stratégie Multi-Tenancy (Base Unique avec `tenant_id`)
- **Contexte :** RepairOS est un SaaS multi-tenant destiné initialement à des ateliers et réparateurs.
- **Décision :** Implémentation d'une base de données relationnelle partagée avec colonne `tenant_id` sur toutes les tables métier, couplée à un `TenantScope` Eloquent automatique et une isolation stricte au niveau de la session et des Policies.
- **Raison :** Permet des migrations simples et atomiques, des coûts d'infrastructure optimisés pour les TPE/PME, tout en garantissant une étanchéité absolue validée par des tests automatisés transverses.

## ADR 02 : Génération Atomique et Concurrente des Références Métier
- **Contexte :** Les références de documents (`TICK-2026-0001`, `FAC-2026-0001`, etc.) doivent être continues, lisibles et sans collision même en cas de soumission simultanée.
- **Décision :** Service `ReferenceGeneratorService` s'appuyant sur une table de compteurs dédiée ou une transaction avec `lockForUpdate` sur le dernier numéro du tenant pour l'année en cours. Index d'unicité `(tenant_id, reference)` au niveau du moteur InnoDB.
- **Raison :** Un simple `COUNT()` n'est pas thread-safe et génère des collisions en cas d'accès concurrents.

## ADR 03 : Gestion Monétaire Exacte (XOF / FCFA)
- **Contexte :** RepairOS opère initialement au Burkina Faso et en Afrique francophone avec le XOF.
- **Décision :** Utilisation de colonnes `DECIMAL(12, 2)` en base et de la bibliothèque `bcmath` ou entiers en mémoire. Interdiction formelle des types `float` IEEE 754 de PHP.
- **Raison :** Prévention des erreurs d'arrondis et conformité avec les audits comptables.

## ADR 04 : Portail Public de Suivi Découplé et Sécurisé
- **Contexte :** Les clients finaux doivent pouvoir suivre l'état de leur réparation sans créer de compte.
- **Décision :** Jeton aléatoire cryptographique de 64 caractères hexadécimaux (`public_tracking_token`) généré avec `random_bytes(32)`. Endpoint dédié `/suivi/{token}` appliquant un `throttle:60,1` (protection anti-brute force).
- **Raison :** Empêche l'énumération séquentielle des tickets et isole totalement l'affichage public (aucune note interne, aucune donnée financière sensible exposée).

## ADR 05 : Stockage Privé et Sécurisé des Pièces Jointes
- **Contexte :** Les photos d'entrée d'appareils et documents techniques ne doivent pas être exposés au public.
- **Décision :** Stockage dans `storage/app/private/tenants/{tenant_id}/...`. Téléchargement via un contrôleur dédié vérifiant l'authentification et l'appartenance au tenant avant tout envoi de flux (`response()->file()`).
- **Raison :** Les liens directs publics (`public/storage`) représentent une faille majeure de confidentialité et de sécurité.

## ADR 06 : Génération des Documents PDF
- **Contexte :** Production de bons de dépôt, devis, factures et reçus imprimables.
- **Décision :** Utilisation de `barryvdh/laravel-dompdf` ou templates HTML/CSS d'impression optimisés avec feuille de style `@media print` native de haute fidélité et moteur PDF compatible PHP 8.4.
- **Raison :** Rendu professionnel, léger, sans dépendance système externe complexe (type Chrome headless lourd).
