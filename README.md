# ERP Hub Teste

Módulo de ERP industrial: Pedido de Venda → NF de Saída → Contas a Receber.

## Stack

- **Backend:** Laravel 11 (PHP 8.4), Sanctum, Spatie Permission, Pest 3, PostgreSQL 16, Redis 7
- **Frontend:** Next.js 16 (App Router), React 19, Tailwind v4, shadcn/ui, RHF + Zod
- **Infra:** Docker, Turborepo, pnpm

## Quickstart

### Pré-requisitos
- Docker
- PHP 8.4 + Composer
- Node.js 20+ + pnpm

### 1. Clone e instale

```bash
git clone https://github.com/djsantosjr/erp-hub-teste.git
cd erp-hub-teste
git checkout feat/teste-tecnico
pnpm install
```

### 2. Suba o banco

```bash
docker compose up -d
```

### 3. Configure o Laravel

```bash
cd apps/api
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
```

### 4. Rode tudo

**Terminal 1 — Laravel:**
```bash
cd apps/api
php artisan serve
```

**Terminal 2 — Next.js:**
```bash
cd apps/web
pnpm dev
```

Acesse: http://localhost:3000

### Usuários demo

| Email | Senha | Empresa | Role |
|-------|-------|---------|------|
| admin@erp.local | admin123 | A + B | platform_admin |
| gestor.a@erp.local | gestor123 | A | operador |
| faturador.b@erp.local | fatura123 | B | comercial_faturador |

## Testes

### Backend (Pest)
```bash
cd apps/api
./vendor/bin/pest
```

### Frontend (Vitest)
```bash
cd apps/web
pnpm test
```

### E2E (Playwright)
```bash
cd apps/web
pnpm e2e
```

## Arquitetura