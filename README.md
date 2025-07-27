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
- HTTP заголовок `X-Retention-Days` с временем жизни в днях
- HTTP заголовок `X-Expires-At` с датой истечения срока

### Скачивание файла
```bash
curl http://localhost:8000/file/{token} -O
```

### Удаление файла
```bash
curl http://localhost:8000/delete/{delete_token}
```

## Автоочистка файлов

Время жизни файлов рассчитывается динамически по формуле:

```
min_age = 30 days
max_age = 1 year  
max_size = 512.0 MiB
retention = min_age + (min_age - max_age) * pow((file_size / max_size - 1), 3)
```

**Принцип:** чем больше файл, тем меньше время хранения.
- Малые файлы (< 512 МиБ): до 1 года
- Большие файлы (> 512 МиБ): минимум 30 дней

Для ручной очистки просроченных файлов:

```bash
php artisan cleanup:files
```

## Быстрое развертывание через Docker

Автоматическая установка всего окружения одной командой:

```bash
docker-compose up -d
```

После развертывания сайт будет доступен по адресу: http://localhost:8080

Docker автоматически выполнит:
- Установку зависимостей
- Настройку .env файла
- Генерацию ключа приложения
- Создание SQLite базы
- Выполнение миграций
- Настройку прав доступа

## Технические детали

- **Framework:** Laravel 12
- **PHP:** 8.3+
- **База данных:** SQLite
- **Хранение файлов:** storage/app/uploads/
- **Максимальный размер файла:** 50MB
- **TTL файлов:** Динамическое (30 дней - 1 год по формуле)

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
