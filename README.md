# Каталог товаров — Laravel + Vue 3 + Inertia.js

Интернет-каталог с иерархическими группами, фильтрацией, сортировкой и пагинацией.

## Стек

- **Backend:** PHP 8.4, Laravel 13, PostgreSQL
- **Frontend:** Vue 3, Inertia.js, Bootstrap 5, Vite
- **Infrastructure:** Docker Compose (Nginx, PHP-FPM, Postgres, Node)

## Быстрый старт

```bash
docker compose up --build
```

Открыть http://localhost:8400

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
│   │   └── Middleware/
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
docker compose exec app php artisan test
```

## Примечания

- Цены хранятся как `DECIMAL(10,2)` — достаточно для товарного каталога
- Рекурсивные группы решаются через PostgreSQL CTE (`WITH RECURSIVE`)
- Фильтрация через паттерн Filter — каждый фильтр в отдельном методе
- Сортировка передается как объект `{ field, direction }` для гибкости
