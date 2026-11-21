FROM php:8.3-cli-alpine

# تثبيت متطلبات النظام ومحرك PostgreSQL
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    linux-headers

RUN docker-php-ext-install pdo pdo_pgsql zip

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# نسخ ملفات المشروع
COPY . .

# تثبيت حزم الاعتماديات
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ضبط الصلاحيات والربط الرمزي للتخزين
RUN php artisan storage:link || true
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 8080

# تشغيل التهيئة والترحيل وزرع البيانات تلقائياً ثم بدء تشغيل السيرفر
CMD ["sh", "-c", "php artisan config:clear && php artisan migrate --force && php artisan db:seed --class=PersonnelRecordsMasterSeeder --force && php artisan db:seed --class=ExcelStaffMigrationSeeder --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
