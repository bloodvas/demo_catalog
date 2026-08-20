# Каталог товаров — Laravel + Vue 3 + Inertia.js

Интернет-каталог с иерархическими группами, фильтрацией, сортировкой и пагинацией.

## Стек

- **Backend:** PHP 8.4, Laravel 13, PostgreSQL
- **Frontend:** Vue 3, Inertia.js, Bootstrap 5, Vite
- **Infrastructure:** Docker Compose (Nginx, PHP-FPM, Postgres, Node)

## Системные требования

- Docker и Docker Compose
- Минимум 1 ГБ оперативной памяти для Docker

## Быстрый старт

```bash
# 1. Собрать и запустить все сервисы
docker compose up --build

# 2. Заполнить базу тестовыми данными
docker compose exec app php artisan db:seed

# 3. Открыть каталог
# http://localhost:8400
```

> **Примечание:** первый запуск может занять 2-5 минут — компиляция Docker-образов и установка npm-пакетов.

## Подробная инструкция по запуску

### Шаг 1: Проверка окружения

```bash
docker --version
docker compose version
```

### Шаг 2: Настройка окружения (если нужно)

Файл `.env.example` содержит базовую конфигурацию. Для локальной разработки ничего менять не нужно — всё настроено на работу через Docker.

Если вы хотите изменить настройки базы данных или приложения:

```bash
cp .env.example .env
# отредактируйте .env
```

### Шаг 3: Запуск Docker-сервисов

```bash
# Собрать образы и запустить все сервисы в фоне
docker compose up --build -d
```

Доступные сервисы:

| Сервис | Порт | Описание |
|--------|------|----------|
| `web` | 8400 | Nginx — раздача статики + прокси запросов |
| `app` | — | PHP-FPM — Laravel-приложение |
| `db` | 8300 | PostgreSQL — база данных |
| `node` | 5173 | Vite — горячая перезагрузка фронтенда |

Проверить статус:

```bash
docker compose ps
```

### Шаг 4: Заполнение базы данных

```bash
# Создать таблицы (миграции)
docker compose exec app php artisan migrate

# Заполнить тестовыми данными (10 групп, ~150 товаров)
docker compose exec app php artisan db:seed
```

### Шаг 5: Открыть каталог

Откройте в браузере: **http://localhost:8400**

### Остановка проекта

```bash
# Остановить все сервисы (данные сохранятся)
docker compose down

# Полная остановка с удалением контейнеров (данные базы сохранятся)
docker compose down --rmi local
```

## Архитектура

```
┌─────────────────────────────────────────────────────┐
│                    Nginx :8400                       │
│                 (статика + прокси)                    │
├─────────────────────────────────────────────────────┤
│                  PHP-FPM :9000                       │
│                                                     │
│  CatalogController (API layer)                      │
│  └── CatalogService (business logic)               │
│      ├── GetGroupTree()      → recursive tree      │
│      ├── GetProducts()       → filter + paginate   │
│      ├── GetBreadcrumbs()    → path builder        │
│      └── GetProduct()        → detail + breadcrumbs│
│                                                     │
│  ProductFilter (query filters)                      │
│  ├── name (LIKE)                                     │
│  ├── group_id (recursive CTE)                       │
│  ├── price (min/max range)                          │
│  └── sort (field + direction)                       │
│                                                     │
│  Models: Group, Product, Price                      │
├─────────────────────────────────────────────────────┤
│              Vue 3 SPA (Vite HMR)                    │
│  Pages: Catalog.vue                                 │
│  Components: GroupTree, FilterBar, Breadcrumbs      │
│  Layout: AppLayout (sidebar + content)              │
├─────────────────────────────────────────────────────┤
│              PostgreSQL :5432                        │
└─────────────────────────────────────────────────────┘
```

## API Endpoints

### Группы

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/groups` | Дерево групп первого уровня |
| `GET` | `/api/groups/:id/breadcrumbs` | Путь от корня до группы |

### Товары

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/products` | Фильтрация, сортировка, пагинация |
| `GET` | `/api/products/:id` | Детали товара + хлебные крошки |

### POST /api/products — Параметры

```json
{
  "page": 1,
  "per_page": 12,
  "sort": {
    "field": "price|name|created_at",
    "direction": "asc|desc"
  },
  "group_id": 1,
  "name": "смартфон",
  "price": {
    "min": 100,
    "max": 50000
  }
}
```

### Response

```json
{
  "data": [...],
  "current_page": 1,
  "last_page": 5,
  "per_page": 12,
  "total": 58
}
```

## Структура проекта

```
project/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── CatalogController.php     # API контроллер
│   │   ├── Requests/
│   │   │   └── ProductFilterRequest.php  # валидация запроса
│   ├── Filters/
│   │   ├── Filter.php                     # базовый фильтр
│   │   └── ProductFilter.php             # фильтры товаров
│   ├── Models/
│   │   ├── Group.php                      # рекурсивная группа
│   │   ├── Product.php                    # товар
│   │   └── Price.php                      # цена
│   ├── Services/
│   │   └── CatalogService.php            # бизнес-логика
│   └── Traits/
│       └── HasFilter.php                 # scope filter
├── database/
│   ├── migrations/                       # таблицы: groups, products, prices
│   └── seeders/                          # GroupSeeder, ProductSeeder, PriceSeeder
├── resources/
│   ├── js/
│   │   ├── app.js                       # Inertia entry
│   │   ├── pages/
│   │   │   └── Catalog.vue              # главная страница
│   │   ├── layouts/
│   │   │   └── AppLayout.vue            # layout + sidebar
│   │   └── components/
│   │       ├── GroupTree.vue            # дерево групп
│   │       ├── FilterBar.vue            # фильтры и сортировка
│   │       └── Breadcrumbs.vue          # хлебные крошки
│   └── css/
│       └── app.css                      # Bootstrap 5 + кастом
└── routes/
    ├── api.php                          # API маршруты
    └── web.php                          # Inertia route (/)
```

## Тесты

```bash
# Запустить все тесты
docker compose exec app php artisan test

# Запустить только unit-тесты
docker compose exec app php artisan test --testsuite=Unit

# Запустить только feature-тесты
docker compose exec app php artisan test --testsuite=Feature
```

## Нагрузочное тестирование

### Быстрый чек запросов к БД

```bash
# Включить логирование SQL-запросов в Laravel
# (в .env: DB_CONNECTION=pgsql, APP_DEBUG=true)

# Проверить план запроса напрямую в PostgreSQL
docker compose exec db psql -U postgres -d catalog -c "
EXPLAIN ANALYZE
WITH RECURSIVE subgroups AS (
    SELECT id FROM groups WHERE id = 1
    UNION ALL
    SELECT g.id FROM groups g INNER JOIN subgroups s ON g.id_parent = s.id
)
SELECT id FROM subgroups;
"
```

### Генерация индексов (если их нет)

```bash
docker compose exec app php artisan make:migration add_indexes_to_catalog_tables
```

### Тестирование с Apache Bench

```bash
# Тест API endpoint: 100 запросов, 10 параллельных
docker compose exec web ab -n 100 -c 10 http://localhost/api/products
```

## Примечания

- Цены хранятся как `DECIMAL(10,2)` — достаточно для товарного каталога
- Рекурсивные группы решаются через PostgreSQL CTE (`WITH RECURSIVE`)
- Фильтрация через паттерн Filter — каждый фильтр в отдельном методе
- Сортировка передается как объект `{ field, direction }` для гибкости

## Устранение распространённых проблем

### Vite не подключается

Если в консоли браузера ошибка `CORS` или `NS_ERROR_CONNECTION_REFUSED` на `http://localhost:5173/@vite/client`:

1. Убедитесь, что контейнер `node` запущен: `docker compose ps`
2. Перезапустите контейнер с Vite: `docker compose restart node`
3. Очистите кэш Vite в браузере (Ctrl+Shift+R / Cmd+Shift+R)

### Базовые данные не загружены

```bash
docker compose exec app php artisan db:seed
```

### Ошибка миграций

```bash
docker compose exec app php artisan migrate:fresh --seed
```

## Ключевые решения

- **Service Layer:** бизнес-логика вынесена в `CatalogService`, контроллеры только передают запросы
- **Filter Pattern:** каждый критерий фильтра — отдельный метод в `ProductFilter`, легко расширять
- **Recursive CTE:** PostgreSQL-запрос для обхода дерева групп без N+1 запросов
- **JOIN vs Eager Loading:** для сортировки по цене используется `LEFT JOIN` с таблицей `prices`, так как `COALESCE` и сортировка требуют прямого доступа к полю
- **Bootstrap 5:** через npm + Vite, иконки Bootstrap Icons для UI
