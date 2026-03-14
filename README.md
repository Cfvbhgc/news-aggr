# NewsAggr - API агрегатора новостей

REST API агрегатор новостей, построенный на **Slim Framework 4** с **MySQL 8**. Приложение собирает статьи из RSS-лент, категоризирует их и предоставляет удобный API для получения, фильтрации и поиска новостей.

## Стек технологий

- **PHP 8.2** (Slim Framework 4)
- **MySQL 8.0**
- **Docker** (PHP-FPM + Nginx + MySQL)
- **Laminas Feed** (парсинг RSS)
- **PHP-DI** (внедрение зависимостей)

## Возможности

- Парсинг RSS-лент из различных источников (BBC, TechCrunch, Habr и др.)
- Категоризация статей (технологии, наука, бизнес, мировые новости, развлечения)
- Полнотекстовый поиск по статьям
- Фильтрация по категории, источнику, диапазону дат
- Пагинация результатов
- CRUD-операции для управления лентами
- Ручной и автоматический (cron) запуск парсинга
- CORS-заголовки для кросс-доменных запросов
- Rate limiting (ограничение частоты запросов)

## Быстрый старт

### Требования

- Docker и Docker Compose

### Запуск

1. Клонируйте репозиторий:

```bash
git clone https://github.com/cfvbhgc/news-aggr.git
cd news-aggr
```

2. Скопируйте файл окружения:

```bash
cp .env.example .env
```

3. Запустите Docker-контейнеры:

```bash
docker-compose up -d
```

4. Дождитесь инициализации базы данных (около 15 секунд), затем проверьте работу API:

```bash
curl http://localhost:8080/api/categories
```

5. Для ручного запуска парсинга RSS:

```bash
docker-compose exec app php bin/fetch-feeds.php
```

## API-эндпоинты

### Статьи

| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/api/articles` | Список статей с пагинацией и фильтрами |
| GET | `/api/articles/{id}` | Одна статья по ID |

**Параметры фильтрации** (`GET /api/articles`):

| Параметр | Тип | Описание |
|----------|-----|----------|
| `page` | int | Номер страницы (по умолчанию 1) |
| `per_page` | int | Элементов на странице (по умолчанию 20, макс. 100) |
| `category_id` | int | Фильтр по ID категории |
| `feed_id` | int | Фильтр по ID ленты |
| `search` | string | Полнотекстовый поиск |
| `date_from` | string | Дата начала (YYYY-MM-DD) |
| `date_to` | string | Дата окончания (YYYY-MM-DD) |

### Категории

| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/api/categories` | Список всех категорий |

### Ленты (Feeds)

| Метод | URL | Описание |
|-------|-----|----------|
| GET | `/api/feeds` | Список всех лент |
| POST | `/api/feeds` | Добавить новую ленту |
| PUT | `/api/feeds/{id}` | Обновить ленту |
| DELETE | `/api/feeds/{id}` | Удалить ленту |
| POST | `/api/feeds/{id}/fetch` | Запустить парсинг ленты вручную |

**Тело запроса** (`POST /api/feeds`):

```json
{
    "name": "Example Feed",
    "url": "https://example.com/rss",
    "category_id": 1,
    "is_active": 1
}
```

## Примеры запросов

```bash
# Получить все статьи
curl http://localhost:8080/api/articles

# Поиск статей
curl "http://localhost:8080/api/articles?search=AI&category_id=1"

# Фильтр по дате
curl "http://localhost:8080/api/articles?date_from=2026-03-01&date_to=2026-03-31"

# Добавить новую ленту
curl -X POST http://localhost:8080/api/feeds \
  -H "Content-Type: application/json" \
  -d '{"name":"New Feed","url":"https://example.com/rss","category_id":1}'

# Запустить парсинг конкретной ленты
curl -X POST http://localhost:8080/api/feeds/1/fetch
```

## Структура проекта

```
news-aggr/
├── bin/
│   └── fetch-feeds.php        # CLI-скрипт для парсинга RSS
├── config/
│   ├── container.php          # DI-контейнер
│   ├── database.php           # Конфигурация БД
│   └── routes.php             # Маршруты API
├── database/
│   ├── schema.sql             # Схема базы данных
│   └── seed.sql               # Начальные данные
├── docker/
│   ├── Dockerfile             # PHP 8.2 + Nginx + Cron
│   ├── entrypoint.sh          # Скрипт запуска
│   └── nginx.conf             # Конфигурация Nginx
├── public/
│   └── index.php              # Точка входа
├── src/
│   ├── Controllers/
│   │   ├── ArticleController.php
│   │   ├── CategoryController.php
│   │   └── FeedController.php
│   ├── Middleware/
│   │   ├── CorsMiddleware.php
│   │   ├── JsonResponseMiddleware.php
│   │   └── RateLimitMiddleware.php
│   ├── Models/
│   │   ├── ArticleModel.php
│   │   ├── CategoryModel.php
│   │   └── FeedModel.php
│   └── Services/
│       └── RssParserService.php
├── .env.example
├── .gitignore
├── composer.json
├── docker-compose.yml
└── README.md
```

## Автоматический парсинг

Cron-задача настроена в Docker-контейнере и запускает парсинг RSS-лент каждые 30 минут. Логи доступны в `/var/log/cron.log` внутри контейнера.

## Лицензия

MIT
