# AGENTS.md — Regras para IA neste projeto

Este arquivo documenta as regras e contextos usados para orientar a IA (Claude) durante o desenvolvimento deste projeto.

## Contexto do Projeto

ERP industrial modular em Laravel 11 + Next.js 16. Multi-tenant por idempresa. RBAC com Spatie Permission team-scoped. Testes com Pest 3 e Vitest.

## Regras Gerais

- Sempre usar Laravel 11 idiomático — sem helpers deprecated, sem facades desnecessárias
- Sempre usar PHP 8.3+ features (readonly, enums, named args, match)
- Sempre tipar explicitamente — sem mixed, sem any no TypeScript
- Sempre usar Eloquent — sem SQL concatenado
- Sempre usar DB::transaction() em operações que envolvem múltiplas tabelas
- Nunca usar $_POST, $_SESSION ou globals PHP
- Nunca usar mysql_query ou funções deprecated

## Regras de Multi-tenant

- Todo Model de negócio usa o trait BelongsToEmpresa
- Nunca fazer query sem escopo de empresa
- Sempre verificar idempresa na Policy antes de qualquer ação
- O global scope é automático — não precisa where('idempresa') manual

## Regras de Testes

- Testes Pest sempre com RefreshDatabase
- Sempre usar banco erp_hub_test para testes
- Helpers reutilizáveis: criarEmpresa(), criarUsuario(), criarPedidoComItens()
- Nunca testar com banco de produção

## Regras do Frontend

- Sempre usar 'use client' em componentes com estado
- Sempre usar React Hook Form + Zod para formulários
- Nunca usar any no TypeScript
- Sempre usar Zod v3 (não v4 — incompatível com @hookform/resolvers)
- Interceptors do Axios injetam X-Empresa-Id automaticamente

## Arqueologia do Legado PHP

As seguintes regras foram portadas do legado PHP 7.4 (§10 do enunciado):

- **R1** (L065): `total = round(($preco - $desun) * $qtd, 2)` → implementado em PedidoItem e PedidoController
- **R2** (L132-L142): frete rateado proporcional com resíduo no último item → FaturarPedidoAction
- **R3** (L165-L171): parcelas com resíduo na última → FaturarPedidoAction

## O que foi Descartado do Legado

- mysql_query → Eloquent + PostgreSQL
- SQL concatenado → prepared statements automáticos do Eloquent
- $_POST direto → FormRequest com validação
- Sem transação → DB::transaction()
- $_SESSION para tenant → Sanctum + middleware
- Sem RBAC → Spatie Permission + PedidoPolicy
- Audit manual → trait AuditsChanges + AuditLog::record()