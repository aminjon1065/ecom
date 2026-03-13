# E-Commerce Platform (Ecom)

Современная, масштабируемая e-commerce платформа, созданная с использованием новейшей экосистемы Laravel и React.

## 🚀 Технологический стек

### Backend
- **Фреймворк:** [Laravel 12](https://laravel.com)
- **Язык:** PHP 8.4+
- **Аутентификация:** Laravel Fortify & Socialite
- **Права доступа:** Spatie Laravel Permission
- **Мониторинг:** Laravel Pulse & Pail
- **Утилиты:**
  - `intervention/image` (Обработка изображений)
  - `maatwebsite/excel` (Экспорт в Excel)
  - `barryvdh/laravel-dompdf` (Генерация PDF)

### Frontend
- **Ядро:** [React 19](https://react.dev) + [TypeScript](https://www.typescriptlang.org/)
- **Связующее звено:** [Inertia.js v2](https://inertiajs.com)
- **Стилизация:** [Tailwind CSS v4](https://tailwindcss.com)
- **Компоненты:** Radix UI (Headless), Lucide React (Иконки), Sonner (Уведомления)
- **Текстовый редактор:** Lexical
- **Роутинг:** Laravel Wayfinder (Типобезопасные маршруты)

### Инструменты разработки
- **Сборщик:** Vite (с компрессией и React Compiler)
- **Тестирование:** Pest PHP
- **Качество кода:** Laravel Pint (PHP), ESLint + Prettier (JS/TS)

---

## 🛠 Установка

### Требования
- PHP 8.4+
- Composer
- Node.js & NPM

### Быстрая установка
Проект включает в себя скрипт для автоматической установки зависимостей, настройки окружения, генерации ключей и миграции базы данных.

1. **Клонируйте репозиторий:**
   ```bash
   git clone <repository-url>
   cd ecom
   ```

2. **Запустите скрипт установки:**
   ```bash
   composer run setup
   ```
   *Эта команда выполняет `composer install`, копирует `.env`, генерирует ключ приложения, запускает миграции и устанавливает NPM-зависимости.*

---

## 💻 Разработка

Для запуска сервера разработки используйте единую команду. Она одновременно запускает сервер Laravel, обработчик очередей, Pail (для логов) и Vite.

```bash
composer run dev
```

### Режим SSR (Server-Side Rendering)
Если вам нужно протестировать SSR локально:
```bash
composer run dev:ssr
```

---

## ✅ Качество кода и тестирование

### PHP (Backend)
Запуск тестов с помощью Pest:
```bash
php artisan test
```

Исправление стиля кода с помощью Pint:
```bash
./vendor/bin/pint
```

### React/TypeScript (Frontend)
Проверка и исправление кода:
```bash
npm run lint
```

Проверка типов TypeScript:
```bash
npm run types
```

---

## 📂 Структура проекта

- **`app/`**: Основная логика на PHP (модели, контроллеры).
- **`resources/js/`**: React-приложение.
  - **`pages/`**: Компоненты страниц для Inertia.
  - **`components/ui/`**: Переиспользуемые UI-компоненты (кнопки, поля ввода и т.д.).
  - **`lib/`**: Вспомогательные функции.
- **`routes/`**: Маршруты (web, API, консольные).
- **`tests/`**: Тесты (Feature и Unit).

## 📄 Лицензия

Этот проект является программным обеспечением с открытым исходным кодом, распространяемым по [лицензии MIT](https://opensource.org/licenses/MIT).
