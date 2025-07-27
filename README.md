# Laravel File Hosting

Минималистичный файло-хостинг на Laravel 12 с функциями загрузки, скачивания и удаления файлов.

## Установка

### 1. Клонирование репозитория
```bash
git clone https://github.com/shirletka/uniads_test.git
cd uniads_test
```

### 2. Установка зависимостей
```bash
composer install
```

### 3. Настройка окружения
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Создание базы данных
```bash
touch database/database.sqlite
```

### 5. Выполнение миграций
```bash
php artisan migrate
```

### 6. Создание необходимых директорий
```bash
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chmod -R 775 storage bootstrap/cache
```

### 7. Запуск сервера
```bash
php artisan serve
```

Сайт будет доступен по адресу: http://localhost:8000

## API Документация

### Загрузка файла
```bash
curl -F "file=@filename.txt" http://localhost:8000
```

**Ответ:**
- URL для скачивания файла
- HTTP заголовок `X-Delete` с токеном для удаления

### Скачивание файла
```bash
curl http://localhost:8000/file/{token} -O
```

### Удаление файла
```bash
curl http://localhost:8000/delete/{delete_token}
```

## Автоочистка файлов

Файлы автоматически удаляются через 7 дней. Для ручной очистки:

```bash
php artisan cleanup:files
```

## Docker (опционально)

```bash
docker-compose up -d
```

## Технические детали

- **Framework:** Laravel 12
- **PHP:** 8.3+
- **База данных:** SQLite
- **Хранение файлов:** storage/app/uploads/
- **Максимальный размер файла:** 50MB
- **TTL файлов:** 7 дней

## Структура проекта

```
├── app/
│   ├── Http/Controllers/UploadController.php  # Основная логика
│   ├── Models/Upload.php                      # Модель для метаданных
│   └── Console/Commands/CleanupFiles.php      # Команда очистки
├── database/
│   └── migrations/                            # Миграции БД
├── routes/web.php                             # Маршруты API
└── storage/app/uploads/                       # Загруженные файлы
```

## Особенности

- ✅ Простой API для загрузки через curl
- ✅ Безопасные токены для доступа к файлам
- ✅ Автоматическое удаление старых файлов
- ✅ HTML-гайд на главной странице
- ✅ Без регистрации и авторизации
- ✅ Минималистичный дизайн

---

**Задание выполнил:** Аширбеков Багдаулет для UniAds
