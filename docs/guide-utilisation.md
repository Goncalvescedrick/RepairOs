# Guide d'Utilisation Rapide — RepairOS

Bienvenue sur **RepairOS**, la solution professionnelle conçue pour piloter votre atelier de réparation et de maintenance technique.

---

## 1. Premier Démarrage & Configuration de l'Atelier

1. **Création du compte** : Rendez-vous sur la page d'accueil et cliquez sur **« Créer mon atelier »**.
2. Renseignez le nom de votre atelier (ex: *Ouaga Tech Réparation*), votre téléphone, mot de passe et ville.
3. Dès votre inscription validée, votre espace de travail dédié est instantanément provisionné avec le rôle de **Propriétaire (Owner)**.
4. Rendez-vous dans **Paramètres** pour ajuster votre devise (par défaut `XOF - FCFA`), le taux de TVA éventuel et les mentions contractuelles affichées sur vos devis et factures.

---

## 2. Le Cycle Complet d'une Réparation en 10 Étapes

```
1. Client  ➔  2. Appareil  ➔  3. Ticket (RECU)  ➔  4. Diagnostic  ➔  5. Devis
      ➔  6. Accord Client  ➔  7. Intervention  ➔  8. Facture  ➔  9. Règlement  ➔  10. Restitution
```

### Étape 1 : Enregistrer le client
- Menu **Clients** ➔ **Nouveau client**.
- Renseignez nom, téléphone principal (compatible WhatsApp), adresse et remarques éventuelles.

### Étape 2 : Enregistrer l'appareil déposé
- Sur la fiche client ou via **Appareils** ➔ **Ajouter un appareil**.
- Précisez la catégorie (PC portable, unité centrale, imprimante, équipement réseau...), marque, modèle, numéro de série et état esthétique entrant.

### Étape 3 : Créer le Ticket de Réparation
- Cliquez sur **Nouveau Ticket**.
- Décrivez précisément le symptôme constaté et notez les accessoires déposés (ex: *Chargeur d'origine 65W*).
- Imprimez le **Bon de Prise en Charge** pour le remettre au client.
- Envoyez-lui son **Lien de suivi sécurisé** en un clic sur WhatsApp !

### Étape 4 : Consigner les Diagnostics et Interventions
- Le technicien assigné passe le ticket au statut **DIAGNOSTIC**.
- Il renseigne les symptômes au multimètre/oscilloscope, le composant défaillant identifié et la solution proposée.
- Les notes techniques internes restent confidentielles ; seul le champ **« Compte-rendu client »** est visible sur le suivi public.

### Étape 5 : Établir et Envoyer le Devis
- Menu **Devis** ➔ **Créer un devis**.
- Ajoutez les lignes de prestations (main d'œuvre, micro-soudure...) et les pièces de rechange nécessaires.
- Les totaux en FCFA sont calculés automatiquement côté serveur.
- Cliquez sur **« Marquer comme Envoyé »** après transmission au client.

### Étape 6 : Validation du Client
- Dès réception de l'accord du client, cliquez sur **« Accepter le devis »**.
- Le ticket passe au statut **EN_REPARATION**.

### Étape 7 : Tests de Contrôle
- Une fois la réparation matérielle terminée, passez le ticket au statut **TEST**.
- Après validation du banc de test, passez au statut **PRET_A_RETIRER**. Le client est averti via WhatsApp qu'il peut venir récupérer son matériel.

### Étape 8 : Génération de la Facture
- Depuis le devis ou la section **Factures**, générez la facture définitive d'un clic.
- La facture fige les prix et applique le taux de TVA configuré.
- Imprimez la facture professionnelle au format standard A4.

### Étape 9 : Enregistrement des Règlements
- Cliquez sur **« Enregistrer un règlement »** sur la facture.
- Saisissez le montant perçu (acompte ou paiement intégral) et le mode de règlement : **Espèces**, **Mobile Money (Orange/Moov/Wave)**, ou **Virement**.
- Un **Reçu de Paiement numéroté (REC-...)** est généré et imprimable immédiatement.

### Étape 10 : Restitution de l'Appareil
- Lorsque le client récupère son bien et que les conditions financières sont remplies, passez le ticket au statut final **RESTITUE**.
- Le ticket est clôturé avec enregistrement de la date exacte de restitution.

---

## 3. Gestion de l'Équipe et Rôles

- **Propriétaire (Owner)** : Accès absolu à tous les modules, paramètres financiers, rapports complets et gestion des collaborateurs.
- **Administrateur (Admin)** : Gestion opérationnelle de l'atelier, suivi des clients, émission des devis et factures.
- **Technicien (Technician)** : Prise en charge des tickets assignés, saisie des diagnostics et interventions techniques. Ne peut pas modifier les paramètres de l'atelier ni supprimer des factures.
