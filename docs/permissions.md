# Matrice des Rôles & Permissions — RepairOS

**Architecture des autorisations :** Laravel Policies + Middleware `CheckRolePermission`.  
Chaque requête est évaluée selon l'atelier actif (`current_tenant`) et le rôle attribué dans `tenant_memberships`.

---

## 1. Définition des Rôles

| Rôle | Portée & Responsabilité |
|---|---|
| **OWNER (Propriétaire de l'atelier)** | Contrôle total sur son atelier, son abonnement, l'équipe, la configuration fiscale et légale, les rapports financiers et la gestion des utilisateurs. |
| **ADMIN (Gestionnaire d'atelier)** | Gestion opérationnelle quotidienne : clients, appareils, tickets, devis, facturation, encaissements, pièces jointes. Ne peut pas supprimer le compte du propriétaire ni modifier les paramètres réservés au propriétaire. |
| **TECHNICIAN (Technicien de maintenance)** | Travail d'atelier : consultation des tickets, réalisation des diagnostics, enregistrement des interventions, téléversement de photos d'entrée/sortie, mise à jour des étapes techniques (ex: Diagnostic -> Test -> Réparation). Pas d'accès direct aux fonctions comptables sensibles (factures globales, modification des paramètres de l'atelier). |

---

## 2. Matrice des Droits par Domaine Métier

| Ressource / Action | OWNER | ADMIN | TECHNICIAN | Règle / Policy |
|---|:---:|:---:|:---:|---|
| **Clients** | | | | |
| Lister / Rechercher | OUI | OUI | OUI | Filtré par `tenant_id` |
| Créer / Modifier | OUI | OUI | OUI | Validation unicité téléphone/atelier |
| Archiver (Soft Delete) | OUI | OUI | NON | Uniquement si aucun ticket ouvert actif |
| **Appareils** | | | | |
| Enregistrer / Modifier | OUI | OUI | OUI | Lié au client et au tenant |
| **Tickets de Réparation** | | | | |
| Créer un ticket | OUI | OUI | OUI | Génération de réf atomique + Bon de dépôt |
| Modifier détails généraux | OUI | OUI | OUI | |
| Changer priorité / technicien | OUI | OUI | NON | Seuls Owner/Admin peuvent réaffecter |
| Transitions de statut | OUI | OUI | PARTIEL | Technicien peut passer DIAGNOSTIC, TEST, EN_REPARATION. Restitution réservée aux Admin/Owner avec contrôle financier. |
| Annuler un ticket | OUI | OUI | NON | Exige un motif obligatoire |
| **Interventions & Diagnostics**| | | | |
| Ajouter diagnostic / travail | OUI | OUI | OUI | Technicien assigné ou équipe |
| Modifier une intervention | OUI | OUI | Auteur seul (24h)| |
| **Devis** | | | | |
| Créer / Modifier un devis | OUI | OUI | OUI | Statut BROUILLON uniquement |
| Envoyer au client | OUI | OUI | OUI | Génération PDF / Lien WhatsApp |
| Valider accord / refus | OUI | OUI | OUI | Enregistre date et statut immuable |
| Annuler un devis | OUI | OUI | NON | |
| **Facturation & Paiements** | | | | |
| Générer une facture | OUI | OUI | NON | Snapshot immuable des montants |
| Enregistrer un paiement | OUI | OUI | NON | Enregistre auteur, moyen et référence |
| Annuler un paiement erroné | OUI | NON | NON | Réservé à l'Owner avec motif d'audit |
| Télécharger reçus et factures| OUI | OUI | OUI | Documents PDF générés côté serveur |
| **Pièces Jointes & Photos** | | | | |
| Téléverser photo état entrée | OUI | OUI | OUI | Validation MIME stricte côté serveur |
| Télécharger / Visualiser | OUI | OUI | OUI | Contrôle d'accès tenant + stockage privé |
| Supprimer un fichier | OUI | OUI | NON | |
| **Gestion d'Atelier & Équipe**| | | | |
| Inviter / Activer collaborateur| OUI | NON | NON | Rôle assigné strictement inférieur ou égal |
| Désactiver un collaborateur | OUI | NON | NON | Empêche la désactivation du dernier Owner |
| Modifier paramètres fiscaux / nom | OUI | NON | NON | |
| Consulter les rapports financiers | OUI | OUI (partiel) | NON | Rentabilité & soldes réservés direction |
