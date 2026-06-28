# ============================================
# CONFIGURACIÓN PRINCIPAL DE LA APLICACIÓN
# ============================================
APP_NAME="RIC - Sistema de Gestión"
APP_ENV=production
APP_KEY=base64:YTnFpVvfDChFIDQirlj1qTuF1PS6BBVH0eZcr0k83qY=
APP_DEBUG=false
APP_URL=https://tudominio.com
VITE_APP_URL=https://tudominio.com
APP_LOCALE=es
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=es_ES
APP_MAINTENANCE_DRIVER=file
APP_CIPHER=AES-256-CBC

# ============================================
# SEGURIDAD Y CRYPTOGRAFÍA
# ============================================
BCRYPT_ROUNDS=12
HASH_DRIVER=bcrypt

# ============================================
# LOGS Y MONITOREO
# ============================================
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error
LOG_MAX_FILES=7
LOG_DAILY_DAYS=7

# ============================================
# BASE DE DATOS - PRODUCCIÓN
# ============================================
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ric_produccion
DB_USERNAME=ric_user_prod
DB_PASSWORD=TuC0ntr4s3ñ4Segur4!2026#Secure
DB_STRICT_MODE=true
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
DB_TIMEZONE=America/Mexico_City

# ============================================
# SESIONES - OPTIMIZADO PARA PRODUCCIÓN
# ============================================
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=.tudominio.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_COOKIE_NAME=ric_session

# ============================================
# CACHÉ - ALTO RENDIMIENTO
# ============================================
CACHE_DRIVER=redis
CACHE_STORE=redis
CACHE_PREFIX=ric_cache_

# ============================================
# COLAS - PROCESAMIENTO ASÍNCRONO
# ============================================
QUEUE_CONNECTION=redis
QUEUE_PREFIX=ric_queue_
QUEUE_DRIVER=redis

# ============================================
# REDIS - CONFIGURACIÓN COMPLETA
# ============================================
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2
REDIS_QUEUE_DB=3
REDIS_PERSISTENT=1
REDIS_READ_TIMEOUT=60
REDIS_TIMEOUT=60

# ============================================
# CORREO ELECTRÓNICO - GMAIL CON TOKEN
# ============================================
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=sains.ingreso@gmail.com
MAIL_PASSWORD=mtgnasxgppmgpjly  # ¡CAMBIA ESTO POR UN TOKEN DE APLICACIÓN!
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@tudominio.com"
MAIL_FROM_NAME="RIC - Sistema"
MAIL_REPLY_TO="soporte@tudominio.com"
MAIL_QUEUE=true
MAIL_QUEUE_CONNECTION=redis

# ============================================
# AWS S3 - OPCIONAL (PARA ALMACENAMIENTO)
# ============================================
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

# ============================================
# ARCHIVOS Y ALMACENAMIENTO
# ============================================
FILESYSTEM_DISK=local
FILESYSTEM_CLOUD=s3
UPLOAD_MAX_SIZE=20480  # 20MB

# ============================================
# BROADCASTING Y WEBSOCKETS
# ============================================
BROADCAST_CONNECTION=log
BROADCAST_DRIVER=log

# ============================================
# SANCTUM - AUTENTICACIÓN API
# ============================================
SANCTUM_STATEFUL_DOMAINS=tudominio.com,www.tudominio.com
SANCTUM_GUARD=web
SANCTUM_PREFIX=api
SANCTUM_EXPIRATION=120

# ============================================
# CORS - SEGURIDAD PARA PRODUCCIÓN
# ============================================
CORS_ALLOWED_ORIGINS=https://tudominio.com,https://www.tudominio.com
CORS_ALLOWED_METHODS=GET,POST,PUT,PATCH,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Requested-With,X-CSRF-TOKEN
CORS_MAX_AGE=86400

# ============================================
# HTTPS Y SEGURIDAD
# ============================================
FORCE_HTTPS=true
TRUSTED_PROXIES=*  # Configurar con IPs específicas si es posible
TRUSTED_HOSTS=tudominio.com,www.tudominio.com

# ============================================
# RENDIMIENTO Y OPTIMIZACIÓN
# ============================================
PHP_MEMORY_LIMIT=256M
PHP_MAX_EXECUTION_TIME=60
PHP_MAX_INPUT_TIME=120
PHP_POST_MAX_SIZE=20M
PHP_UPLOAD_MAX_FILESIZE=20M

# ============================================
# SCOUT (BUSQUEDA) - OPCIONAL
# ============================================
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=masterKey123!

# ============================================
# PAGOS Y SERVICIOS EXTERNOS
# ============================================
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...

# ============================================
# VARIABLES DE VITE (FRONTEND)
# ============================================
VITE_APP_NAME="${APP_NAME}"
VITE_APP_URL="${APP_URL}"
VITE_RECAPTCHA_SITE_KEY=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI

# ============================================
# MANTENIMIENTO Y BACKUP
# ============================================
BACKUP_DRIVER=local
BACKUP_DISK=local
BACKUP_KEEP_DAYS=30

# ============================================
# EXTRAS Y PERSONALIZACIÓN
# ============================================
TIMEZONE=America/Mexico_City
LOCALE=es
FALLBACK_LOCALE=en
CURRENCY=MXN
DECIMAL_SEPARATOR=.
THOUSAND_SEPARATOR=,
DECIMALS=2

# ============================================
# MONITOREO Y SENTRY (OPCIONAL)
# ============================================
SENTRY_LARAVEL_DSN=https://...
SENTRY_ENVIRONMENT=production
SENTRY_RELEASE=1.0.0
SENTRY_TRACES_SAMPLE_RATE=0.1

# ============================================
# RATELIMIT - PROTECCIÓN DDOS
# ============================================
RATELIMIT_GLOBAL=60,1
RATELIMIT_API=120,1

# ============================================
# CACHE DE VISTAS
# ============================================
VIEW_COMPILED_PATH=/tmp/ric_views














APP_NAME=RIC
APP_ENV=local
APP_KEY=base64:YTnFpVvfDChFIDQirlj1qTuF1PS6BBVH0eZcr0k83qY=
APP_DEBUG=true

APP_URL=https://dramatic-afflicted-drab.ngrok-free.dev
VITE_APP_URL=https://dramatic-afflicted-drab.ngrok-free.dev

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

# PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ric
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=.ngrok-free.dev

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=sains.ingreso@gmail.com
MAIL_PASSWORD=mtgnasxgppmgpjly
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="sains.ingreso@gmail.com"
MAIL_FROM_NAME="RIC"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

SANCTUM_STATEFUL_DOMAINS=dramatic-afflicted-drab.ngrok-free.dev