# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Gastos App is a personal expense tracking application built with Laravel 12 and Filament 3.3 admin panel. Argentine-focused (MercadoPago/Galicia bank support). The UI is entirely in Spanish.

## Common Commands

```bash
# Start full development stack (PHP server, queue listener, logs, Vite)
composer run dev

# Run tests
composer run test

# Build frontend assets for production
npm run build

# Watch frontend assets during development
npm run dev

# Run database migrations
php artisan migrate

# PHP code formatting
./vendor/bin/pint

# Clear transaction cache
php artisan cache:clear

# Generate recurring transactions (run via scheduler)
php artisan transactions:generate-recurring
```

## Architecture

### Tech Stack
- **Backend**: PHP 8.4+, Laravel 12
- **Admin Panel**: Filament 3.3 (all CRUD UI lives here)
- **Frontend**: Tailwind CSS 3.4, Vite 6.2, Livewire, Chart.js 4.4.4 (CDN via `@once`)
- **Database**: SQLite (development), Eloquent ORM
- **AI**: Laravel AI 0.1.2 (financial assistant feature) — requires PHP 8.4
- **PDF Parsing**: PDFParser 2.12 (for Galicia bank statement imports)
- **Deployment**: Docker (3-stage build), Railway

### Domain Model

Core entities:
- **Account** - Financial accounts (checking, savings, cash, credit_card, investment, wallet). Has `current_balance` computed from initial_balance + income - expenses (cached 5 min).
- **Category** - Transaction categories with a `type` field ('income' or 'expense').
- **Transaction** - Individual financial entries. Type is derived from its category's type. Eager-loads `category` by default.
- **CreditCard** - Credit cards with closing/due days, linked to an Account. Tracks `total_debt` and `monthly_payment` via cached computed attributes.
- **InstallmentPurchase** - Cuotas (installments) linked to a CreditCard. Tracks `paid_installments`, `remaining_installments`, `next_payment_date`. Auto-creates a linked `Transaction` on the credit card account when created (via `InstallmentPurchaseService`).
- **TransactionTemplate** / **TransferTemplate** - Reusable templates for quick entry.
- **ImportCategoryRule** - Rules to auto-assign categories when importing transactions (source, field, operator, value, priority).

### Transfers Between Accounts

Transfers create two linked Transaction records (one expense, one income) with the same `reference_id`. The `TransferService` handles this logic atomically. The transfer category is auto-created with name "Transfer".

### Import System

`app/Services/Import/TransactionImportService.php` orchestrates bulk imports:
- Supported sources: `mercadopago` (CSV), `galicia` (PDF)
- Parsers implement `CsvParserInterface`: `MercadoPagoCsvParser`, `GaliciaPdfParser`
- `CategoryMatcherService` applies `ImportCategoryRule` records to auto-assign categories
- Supports preview mode (rollback without committing), duplicate detection via `reference_id`

### AI Financial Assistant

`app/AI/` — Uses Laravel AI to provide a financial assistant widget on the dashboard.
- **Agent**: `FinancialAssistant`
- **Tools**: `CreateTransactionTool`, `GetAccountBalanceTool`, `GetExpensesByCategoryTool`, `ListCategoriesAndAccountsTool`

### Key Directories

```
app/
├── Filament/
│   ├── Resources/          # CRUD: Account, Category, Transaction, TransactionTemplate,
│   │                       #        TransferTemplate, CreditCard, InstallmentPurchase,
│   │                       #        ImportCategoryRule
│   ├── Widgets/            # Dashboard widgets (see below)
│   └── Pages/Dashboard.php # Dashboard with month filter passed to all widgets
├── Models/                 # Eloquent models
├── Services/
│   ├── TransferService.php
│   ├── InstallmentPurchaseService.php  # createLinkedTransaction / syncLinkedTransaction / deleteLinkedTransaction
│   └── Import/             # TransactionImportService + parsers + CategoryMatcherService
├── Observers/              # Cache invalidation: Account, Transaction, Category, InstallmentPurchase
├── AI/                     # FinancialAssistant agent + tools
└── Console/Commands/       # ClearTransactionCache, GenerateRecurringTransactions
```

### Dashboard Widgets (in priority order)

| Widget | Description |
|---|---|
| `FinancialAssistantWidget` | AI chat assistant (priority 0) |
| `BalanceOverview` | Total portfolio balance (priority 1) |
| `ProjectedBalanceWidget` | Balance projection with pending expenses (priority 1.5) |
| `AccountsOverviewWidget` | Per-account balances + month income/expense (priority 2) — single aggregate query |
| `CreditCardsOverviewWidget` | Credit card debt and available credit (priority 2.5) |
| `ExpenseCategoriesWidget` | Doughnut chart, top 10 categories, cached 5 min (priority 3) |
| `TransactionsTable` | Recent transactions (priority 4) |
| `CategoryAveragesWidget` | Average spending per category (priority 5) |
| `QuickTransactionsWidget` | Quick transaction entry form |
| `QuickTransfers` | Quick transfer buttons from TransferTemplate |

### Model Scopes

**Transaction**: `income()`, `expense()`, `thisMonth()`, `forMonth($month, $year)`, `transfers()`, `excludeTransfers()`, `betweenDates($start, $end)`, `forAccount($accountId)`

**Account**: `includedInTotals()`, `ofType($type)`

**TransactionTemplate**: `active()`, `recurring()`, `autoCreate()`, `pendingThisMonth()`

**TransferTemplate**: `active()` (ordered by `order`)

**CreditCard**: `active()`

**InstallmentPurchase**: `active()`, `completingThisMonth()`

**ImportCategoryRule**: `active()`, `forSource($source)`, `ordered()`

### Caching Strategy

- **Account balance** (`account_balance_{id}`): 5 min, cleared by `TransactionObserver` + `AccountObserver`
- **Category select lists** (`categories_select`): 1 hour, cleared by `CategoryObserver`
- **Transfer category lookup** (`transfer_income_category`): 1 hour
- **Expense chart data** (`expense_categories_chart_{Y-m}`): 5 min, cleared by `TransactionObserver`
- **Credit card debt/monthly** (`credit_card_debt_{id}`, `credit_card_monthly_{id}`): 5 min, cleared by `InstallmentPurchaseObserver`
- **Cached statics**: `Category::getTransferCategory()`, `Category::getTransferCategoryId()`

### Performance Patterns

- **AccountsOverviewWidget**: Single query with `SUM(CASE WHEN ...)` aggregates — never loop per account
- **Chart widgets**: `GROUP BY DATE(date)` aggregate then fill gaps in PHP
- **Eager loading**: `Transaction` globally eager-loads `category` via `$with`. Widgets use `modifyQueryUsing(fn($q) => $q->with(...))`
- **Observers handle all cache invalidation** — do not forget to clear caches when adding new computed attributes

### Database Schema Highlights

- `accounts`: name, account_type (enum), color, icon, initial_balance, include_in_totals
- `transactions`: title, amount, date, category_id, account_id, reference_id (links transfer pairs)
- `categories`: name, type (income/expense)
- `credit_cards`: account_id, credit_limit, closing_day, due_day
- `installment_purchases`: credit_card_id, transaction_id (FK nullable, nullOnDelete), total_amount, installment_amount, installments_count, paid_installments, first_payment_date
- `import_category_rules`: source, field, operator (equals/contains/starts_with/ends_with/not_equals), value, category_id, priority
- `transaction_templates`: supports recurring (monthly/weekly/yearly) with auto_create flag
- `transfer_templates`: from/to accounts, icon, color, display order

### InstallmentPurchase → Transaction Integration

When an `InstallmentPurchase` is created, `InstallmentPurchaseService::createLinkedTransaction()` auto-creates an expense `Transaction` on the linked credit card account:
- Amount = `total_amount`, date = `first_payment_date`, category = purchase's category
- `transaction_id` saved back via `$purchase->updateQuietly(...)` to avoid observer loop
- Guard: early return with `Log::warning` if `creditCard->account_id` is null
- Sync on update (dirty check on title/store/total_amount/first_payment_date/category_id)
- Delete on purchase delete
- `nullOnDelete` FK: if transaction manually deleted, `transaction_id` becomes NULL without crashing

### Account ViewRecord (ViewAccount)

`AccountResource/Pages/ViewAccount.php` — custom view with month selector:
- Header actions: Edit, Ver Transacciones (filtered to account), Nueva Transacción
- **credit_card** accounts show a summary panel with debt, monthly payment, available credit, and installment pills
- Charts use **Chart.js 4.4.4** (loaded via CDN `@once` tag), initialized with Alpine.js `x-init`:
  - Bar chart: income vs expense for last 7 days of selected month
  - Line chart: 6-month trend (income, expense, balance)
- Chart.js CDN script placed after `</x-filament-panels::page>` to ensure it loads before Alpine's `x-init`

### Transaction Resource Filters

Filters displayed above table (`FiltersLayout::AboveContent`, 4 columns):
- Account (multi-select, preload, searchable)
- Type (income/expense)
- Category (multi-select, preload, searchable)
- Month (custom date range filter with `indicateUsing`)

Filter URL param format for multi-select: `tableFilters[account_id][values][0]`

### Deployment

**Docker 3-stage build** (`Dockerfile`):
1. `composer:latest` — installs PHP vendor deps (`--no-scripts --ignore-platform-reqs`)
2. `node:22-slim` — copies vendor (needed for Filament Tailwind preset), runs `npm run build`
3. `php:8.4-cli` — installs PHP extensions (intl, zip, pdo_sqlite need `libicu-dev`, `libzip-dev`, `libsqlite3-dev`, `pkg-config`), copies vendor + built assets

**Railway env variables required**: `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `DB_CONNECTION=sqlite`, `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`, `LOG_CHANNEL=stderr`

**SQLite caveat**: data resets on every Railway redeploy (ephemeral storage). Acceptable for testing; use PostgreSQL for production.
