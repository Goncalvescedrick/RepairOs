# Guide de Déploiement et d'Exploitation en Production — RepairOS

## 1. Prérequis Serveur (Linux Ubuntu 22.04 / 24.04 LTS ou Debian 12)

- **Serveur Web** : Nginx 1.24+ avec support HTTP/2 et TLS 1.3
- **PHP** : PHP 8.4 avec extensions obligatoires : `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `session`, `tokenizer`, `xml`, `zip`
- **Base de données** : MySQL 8.4 LTS (ou MariaDB 10.11+ / MySQL 9.x) avec moteur InnoDB et charset `utf8mb4_unicode_ci`
- **Node.js** : Node v20+ LTS et npm pour la compilation des assets (ou compilation préalable via CI/CD)
- **Gestionnaire de processus** : Supervisor (pour les workers de queues et tâches d'arrière-plan)

---

## 2. Étapes de Déploiement Standard

### Étape 1 : Cloner le dépôt et configurer les permissions
```bash
cd /var/www
git clone <URL_DEPOT> repairos
cd repairos

# Attribution des droits au serveur web
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Étape 2 : Dépendances PHP et Configuration d'environnement
```bash
composer install --no-dev --optimize-autoloader

cp .env.example .env
php artisan key:generate
```

Éditer le fichier `.env` pour la production :
```ini
APP_NAME=RepairOS
APP_ENV=production
APP_DEBUG=false
APP_URL=https://repairos.votre-domaine.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=repairos_prod
DB_USERNAME=repairos_user
DB_PASSWORD=MOT_DE_PASSE_SECURISE_ET_COMPLEXE

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
```

### Étape 3 : Migrations et Optimisations Laravel
```bash
# Exécution sécurisée des migrations de production
php artisan migrate --force

# Mise en cache de la configuration et des routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Compilation des assets frontend (si non fait par CI/CD)
npm ci
npm run build
```

---

## 3. Configuration Nginx Recommandée (avec HTTPS Let's Encrypt)

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name repairos.votre-domaine.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name repairos.votre-domaine.com;
    root /var/www/repairos/public;

    # Certificats SSL Let's Encrypt
    ssl_certificate /etc/letsencrypt/live/repairos.votre-domaine.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/repairos.votre-domaine.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # En-têtes de sécurité HTTP stricts
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    index index.php;
    charset utf-8;

    # Bloquer tout accès direct aux fichiers sensibles
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Bloquer l'accès aux répertoires de stockage privé
    location ^~ /storage/private/ {
        deny all;
        return 404;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
}
```

---

## 4. Configuration Supervisor pour les Queues Asynchrones

Créer `/etc/supervisor/conf.d/repairos-worker.conf` :
```ini
[program:repairos-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/repairos/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/repairos/storage/logs/worker.log
stopwaitsecs=3600
```

Charger la configuration :
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start repairos-worker:*
```

---

## 5. Tâches Planifiées (Cron)

Ajouter dans la crontab de l'utilisateur `www-data` (`crontab -u www-data -e`) :
```cron
* * * * * cd /var/www/repairos && php artisan schedule:run >> /dev/null 2>&1
```
