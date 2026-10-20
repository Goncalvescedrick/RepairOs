# Limitations Connues et Feuille de Route — RepairOS MVP

## 1. Limitations Fonctionnelles Connues du MVP

1. **Règlement Mobile Money manuel** :
   - Le MVP permet la consignation manuelle avec référence de transaction des règlements perçus par Orange Money, Moov Money, Wave ou virement. Il n'intègre pas d'API de prélèvement direct automatisé (USSD push), ce qui est le choix de sécurité et de conformité retenu pour le MVP en l'absence de conventions bancaires de l'atelier.
2. **Notification WhatsApp via liens profonds (`wa.me`)** :
   - Les messages pré-remplis pour la prise en charge, le devis et l'appareil prêt s'ouvrent via l'application WhatsApp de l'utilisateur. L'API WhatsApp Business officielle (Cloud API / Twilio) fera l'objet d'un module d'extension optionnel.
3. **Architecture Multi-Tenant en base partagée** :
   - Le cloisonnement repose sur un discriminant `tenant_id` avec `TenantScope` global, clés étrangères, politiques d'autorisation (Policies) et tests automatisés. Pour des ateliers traitant des volumes extrêmes (> 500 000 tickets/an), une architecture avec base de données dédiée par atelier (Database-per-tenant) pourra être envisagée.
4. **Impression de documents via moteur de rendu navigateur natif** :
   - Les bons de prise en charge, devis, factures et reçus utilisent des feuilles de style CSS print média optimisées A4 (`window.print()`). Cela évite les dépendances lourdes (Node/Chromium server-side ou wrappers C) tout en garantissant une impression haute définition sur toutes les imprimantes standard.

---

## 2. Évolutions Prévues (Roadmap V2)

- **Module Stock & Pièces détachées** : gestion des stocks d'atelier avec alertes de seuil critique et réapprovisionnement.
- **Portail Client complet** : espace authentifié pour les clients réguliers (flottes d'entreprises) permettant de visualiser l'ensemble de leurs appareils en parc et factures échues.
- **Passerelle SMS transactionnelle** : envoi automatisé de SMS lors du passage au statut `PRET_A_RETIRER` via passerelles locales agréées (Burkina Faso, Côte d'Ivoire, Sénégal).
- **Export Comptable normalisé SYSCOHADA** : journal des ventes et des encaissements exportable pour les cabinets d'expertise comptable.
