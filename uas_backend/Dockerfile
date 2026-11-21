FROM php:8.4-cli

# تثبيت مكتبات PostgreSQL وضغط الملفات
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath \
    && rm -rf /var/lib/apt/lists/*

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# نسخ ملفات المشروع
COPY . .

# تثبيت اعتماديات Laravel بدون تشغيل سكريبتات أثناء البناء لتفادي أي خطأ
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs --no-scripts

# إنشاء وتأكيد مجلدات التخزين مع الصلاحيات الكاملة
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

EXPOSE 8080

# تشغيل اكتشاف الحزم والسيرفر عند الإقلاع
CMD ["sh", "-c", "php artisan config:clear && php artisan package:discover --ansi && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
