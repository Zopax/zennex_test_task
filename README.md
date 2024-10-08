## Установка и запуск приложения

1. Скачайте проект zennex_test_task .
```
git clone https://github.com/Zopax/zennex_test_task.git
```

2. После установки проекта рекумендуется устноавить и обновить зависимости composer.
```
composer install
composer update
```

3. После установки и обновления зависимостей composer запустите docker-compose для инициализации базы данных внутри контейнера docker.
В зависимости от используемой ОС команда может отличаться (пример на системе ubuntu команда пишется docker compose up -d).
```
docker-compose up -d
```

4. Для подклучения к БД и приминеия миграций нужно скопировать файл .env.example и переменовать его в .env.
После чего применить миграции  и пожелания сиды(файл .env рекомендуется не редактировать строки связанные с подключением к бд, т.к. там уже настроено правильное подключение).
```
php artisan migrate
```
Или
```
php artisan migrate --seed
```

5. Запустите приложение.
```
php artisan serve
```

Что бы посмотреть доступные маршруты, в папке вашег опроекта откройте консоль и введите:
```
php artisan route:list
```

## Документация

Чтобы открыть документацию данной API (то есть swagger) нужно перейти по следующего URL адресу:
```
http://127.0.0.1:8000/api/documentation
```
