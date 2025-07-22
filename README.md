Тестовое задание для бэкенд-разработчика


Задача:
Создать простой REST API для управления заказами в приложении
«Мясофактура»


Инструкция по запуску

1) Клонировать репозиторий. 
2) docker-compose up -d --build собрать и запустить (или docker build. затем docker-compose up -d)
3) Настроить окружение: скопировать  src/.env.example в src/.env и обновить:

DB_CONNECTION=pgsql

DB_HOST=db

DB_PORT=3306

DB_DATABASE=laravel

DB_USERNAME=laravel

DB_PASSWORD=secret

4) запустить миграции
php artisan migrate

5) Запустить сидеры
php artisan db:seed

