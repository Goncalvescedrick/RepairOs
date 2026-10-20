# Stratégie de Sauvegarde et Plan de Continuité d'Activité (PCA/PRA) — RepairOS

Une sauvegarde n'est fiable que si sa restauration effective et son intégrité ont été testées avec succès.

---

## 1. Périmètre des Données Critiques à Sauvegarder

RepairOS repose sur deux composants d'état indispensables à la reprise après sinistre :
1. **La base de données relationnelle MySQL** :
   - Contient l'ensemble des données multi-tenants : ateliers, utilisateurs, membres, clients, appareils, tickets, historiques de transitions, interventions, devis, factures, encaissements et journaux d'audit.
2. **Le système de fichiers privé (`storage/app/private`)** :
   - Contient les photos d'équipements déposés, diagnostics, fiches techniques et justificatifs joints aux tickets.

---

## 2. Automatisation des Sauvegardes Quotidiennes

### Script de Sauvegarde Sécurisée (`/usr/local/bin/backup-repairos.sh`)

```bash
#!/usr/bin/env bash
set -euo pipefail

# Configuration
APP_DIR="/var/www/repairos"
BACKUP_DIR="/var/backups/repairos"
DATE=$(date +"%Y%m%d_%H%M%S")
RETENTION_DAYS=30

mkdir -p "${BACKUP_DIR}"

echo "[$(date)] Début de la sauvegarde RepairOS..."

# 1. Export MySQL cohérent avec verrouillage transactionnel
DB_NAME=$(grep DB_DATABASE "${APP_DIR}/.env" | cut -d '=' -f2)
DB_USER=$(grep DB_USERNAME "${APP_DIR}/.env" | cut -d '=' -f2)
DB_PASS=$(grep DB_PASSWORD "${APP_DIR}/.env" | cut -d '=' -f2)

mysqldump --single-transaction --quick --routines --triggers \
    -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" | gzip > "${BACKUP_DIR}/db_${DATE}.sql.gz"

echo "[$(date)] Base de données exportée : db_${DATE}.sql.gz"

# 2. Sauvegarde des pièces jointes privées
tar -czf "${BACKUP_DIR}/files_${DATE}.tar.gz" -C "${APP_DIR}/storage/app" private

echo "[$(date)] Fichiers privés archivés : files_${DATE}.tar.gz"

# 3. Chiffrement GPG des archives (chiffrement asymétrique)
# gpg --encrypt --recipient backups@repairos.bf "${BACKUP_DIR}/db_${DATE}.sql.gz"
# gpg --encrypt --recipient backups@repairos.bf "${BACKUP_DIR}/files_${DATE}.tar.gz"

# 4. Nettoyage des anciennes sauvegardes (> 30 jours)
find "${BACKUP_DIR}" -type f -name "*.gz" -mtime +${RETENTION_DAYS} -delete

echo "[$(date)] Sauvegarde terminée avec succès."
```

Rendre le script exécutable :
```bash
chmod 700 /usr/local/bin/backup-repairos.sh
```

Programmation cron quotidienne à 02h00 :
```cron
0 2 * * * /usr/local/bin/backup-repairos.sh >> /var/log/repairos_backup.log 2>&1
```

---

## 3. Procédure Éprouvée de Restauration (Disaster Recovery)

En cas de défaillance majeure ou corruption :

### Étape 1 : Préparation de l'environnement
Mettre l'application en mode maintenance :
```bash
cd /var/www/repairos
php artisan down --message="Maintenance système et restauration de données en cours."
```

### Étape 2 : Restauration de la Base de Données
```bash
# Décompression et injection dans MySQL
gunzip < /var/backups/repairos/db_20261020_020000.sql.gz | mysql -u repairos_user -p repairos_prod
```

### Étape 3 : Restauration des Fichiers Privés
```bash
tar -xzf /var/backups/repairos/files_20261020_020000.tar.gz -C /var/www/repairos/storage/app/
chown -R www-data:www-data /var/www/repairos/storage/app/private
chmod -R 750 /var/www/repairos/storage/app/private
```

### Étape 4 : Vérification et Réouverture
```bash
# Vérification des migrations
php artisan migrate --status

# Réactivation du service
php artisan up
```

---

## 4. Test Périodique d'Intégrité (Drill Trimestriel)

Chaque trimestre, une restauration doit être exécutée sur un environnement de staging isolé pour vérifier que :
1. La base restaurée se charge sans erreurs d'encodage (support parfait des caractères et accents français).
2. Les montants financiers et factures historiques sont rigoureusement intacts.
3. Les pièces jointes sont lisibles par les utilisateurs autorisés.
