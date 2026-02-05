# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Gastos App is a personal expense tracking application built with Laravel 12 and Filament 3.3 admin panel. The UI is primarily Spanish.

## Common Commands

```bash
# Start full development stack (PHP server, queue listener, logs, Vite1)
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
```

## Architecture

### Tech Stack
- **Backend**: PHP 8.2+, Laravel 12
- **Admin Panel**: Filament 3.3 (all CRUD UI lives here)
- **Frontend**: Tailwind CSS, Vite, Livewire
- **Database**: SQLite (development), Eloquent ORM

### Domain Model

The core entities are:
- **Account** - Financial accounts (checking, savings, cash, credit card, investment, wallet). Has `current_balance` computed from initial_balance + income - expenses.
- **Category** - Transaction categories with a `type` field ('income' or 'expense')
- **Transaction** - Individual financial entries. The transaction type is derived from its category's type.
- **TransactionTemplate** / **TransferTemplate** - Reusable templates for quick entry

### Transfers Between Accounts

Transfers create two linked Transaction records (one expense, one income) with the same `reference_id`. The `TransferService` handles this logic atomically. The transfer category is auto-created with name "Transfer".

### Key Directories

- `app/Filament/Resources/` - Filament CRUD resources (Account, Category, Transaction, templates)
- `app/Filament/Widgets/` - Dashboard widgets (balance overview, quick transactions, expense charts)
- `app/Filament/Pages/Dashboard.php` - Main dashboard configuration
- `app/Models/` - Eloquent models with relationships and scopes
- `app/Services/TransferService.php` - Business logic for account transfers
- `app/Observers/` - Model event observers

### Useful Model Scopes

Transaction model provides: `income()`, `expense()`, `thisMonth()`, `transfers()`
Account model provides: `includedInTotals()`, `ofType($type)`
