MASTER BUILD PROMPT — Laravel + PostgreSQL ERP (Telebirr + POS + Inventory)
Goal

Generate a production-ready Laravel 11 application for a retail/telecom distributor running in multiple branches, featuring:

Secure auth with RBAC (roles, capabilities, branch scoping)

POS receipts/sales

Inventory & stock movements

Telebirr agent operations (Issue/Loan/Repay/Top-up) with double-entry GL

Terminals (open/close shifts, cash variance)

Transfers between branches

Realtime dashboard, audit logs, reports & compliance

Modern, responsive UI (Inertia + Vue 3 + Tailwind) with a “Green Juicy” enterprise theme

Tech Stack

Backend: Laravel 11, PHP 8.2, Eloquent ORM, Laravel Sanctum (API), Laravel Policies & Gates

Frontend: Inertia.js + Vue 3, Vite, Tailwind CSS (+ HeadlessUI)

DB: PostgreSQL 15; use UUIDs; enforce constraints & indexes; use enum types where noted

Cache/Queues: Redis; Horizon for queue monitoring

Realtime: Laravel Websockets (or Pusher) + Echo

Testing: Pest (unit/feature) + Playwright (E2E)

Deployment: Docker Compose (nginx + php-fpm + queue + websockets + postgres + redis)

Environment

Create .env.example with:

APP_NAME="Najib ERP"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://example.com

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=najib
DB_USERNAME=postgres
DB_PASSWORD=postgres

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
BROADCAST_DRIVER=pusher
WEBSOCKETS_SSL_LOCAL_CERT=
WEBSOCKETS_SSL_LOCAL_PK=

PUSHER_APP_ID=local
PUSHER_APP_KEY=local
PUSHER_APP_SECRET=local
PUSHER_APP_CLUSTER=mt1

High-Level Modules

Auth & RBAC

Users (id, name, email unique, password, is_active, branch_id FK nullable)

Branches (id UUID, name, location, is_main_branch bool)

Roles (id UUID, name unique, description, is_active)

Capabilities (id UUID, key unique like inventory:view_stock, category, description, is_active)

RoleCapabilities (role_id, capability_id)

UserRoleAssignments (id UUID, user_id, role_id, scope_all_branches bool, branch_ids UUID[] nullable, starts_at, ends_at, created_by)

Policy cache table user_policies (user_id, capabilities TEXT[], branch_scope_all bool, branch_ids UUID[], updated_at)

Laravel Policies + Gates resolve permissions from cache (rebuild on role/cap changes).

Middleware EnsureHasCapability checking capability + optional branch scope.

Audit Logs

audit_logs (id bigserial, user_id UUID nullable, action text, entity_type text, entity_id text, details JSONB, ip_address, user_agent, timestamp timestamptz default now()).

Global observer/trait to append audit on create/update/delete + explicit logs for auth & RBAC decisions.

Products & Inventory

products (id UUID, sku unique, name, unit, is_active, reorder_level int default 0)

inventory_items (id bigserial, product_id UUID, branch_id UUID, quantity numeric(14,2) default 0, reserved_quantity numeric(14,2) default 0, reorder_level int, unique(product_id, branch_id))

stock_movements (id bigserial, product_id UUID, branch_id UUID, qty numeric(14,2), type enum: OPENING, RECEIVE, SALE, ADJUST, TRANSFER_OUT, TRANSFER_IN, ref_table text, ref_id text, created_by UUID, created_at)

Generated column or view not required; compute available = quantity - reserved in queries.

Service methods:

openingBalance(branch, product, qty, memo)

receiveStock(branch, product, qty, supplier?, memo)

reserve/unreserve/commit sale lines

transfer between branches (create header + movement pairs OUT/IN; enforce transaction)

POS / Receipts

receipts (id bigserial, branch_id UUID, user_id UUID, total_amount numeric(14,2), paid_amount, change_amount, status enum: DRAFT/POSTED/VOIDED/REFUNDED, created_at, posted_at, memo)

receipt_lines (id bigserial, receipt_id, product_id, qty numeric, price numeric, total numeric)

On POSTED: create GL journal (see GL below) and stock movements (SALE negative).

GL (Double-Entry)

gl_accounts (id bigserial, code unique, name, type enum: ASSET/LIABILITY/EQUITY/INCOME/EXPENSE, is_active)

gl_journals (id bigserial, tx_type enum: ISSUE/REPAY/LOAN/TOPUP/SALE/TRANSFER/ADJUST, channel enum: CBE/EBIRR/COOPAY/ABASINIYA/AWASH/DASHEN/TELEBIRR/ESAHAL/H_CASH nullable, branch_id UUID, agent_id bigint nullable, ref_no text, description text, posted_at timestamptz, created_by UUID, reversal_of bigint nullable, total_debit numeric(14,2), total_credit numeric(14,2), value_date date, memo text)

gl_lines (id bigserial, journal_id, account_id, debit numeric(14,2) default 0, credit numeric(14,2) default 0, line_no int, narration text)

DB constraint: sum(debit)=sum(credit), all >=0; trigger validate journal on insert/update.

Idempotency table idempotency_keys (key text PK, journal_id bigint, created_at); service prevents double posting.

Telebirr

telebirr_agents (id bigserial, short_code text unique, name, phone, location, status enum: Active/Dormant/Inactive)

telebirr_transactions (id bigserial, tx_id text unique, tx_type enum: ISSUE/REPAY/LOAN/TOPUP, channel enum, branch_id UUID, agent_id bigint nullable, amount numeric(14,2), journal_id bigint, description text, created_at timestamptz default now())

Service class TelebirrService with methods:

postIssue(agent_short_code, channel, amount, branch_id, description, idempotency_key)

postRepay(agent_short_code, channel, amount, branch_id, description, idempotency_key)

postLoan(agent_short_code, amount, branch_id, description, idempotency_key)

postTopup(channel, bank_gl_id?, amount, branch_id, description, idempotency_key)

Each method: validates permissions + branch, resolves agent by short_code, wraps in DB transaction, writes gl_journals and gl_lines, inserts telebirr_transactions, logs audit, broadcasts events. Enforce idempotency.

Terminals

terminals (id UUID, branch_id, name, is_active)

terminal_sessions (id bigserial, terminal_id UUID, user_id UUID, open_time, close_time nullable, opening_cash numeric, closing_cash numeric, variance numeric, status enum: OPEN/CLOSED, notes)

Only one OPEN per terminal; on closing compute variance.

Transfers

transfer_headers (id UUID, from_branch_id, to_branch_id, created_by, status enum: DRAFT/SENT/RECEIVED/CANCELLED, created_at)

transfer_lines (id bigserial, header_id, product_id, qty numeric)

When SENT: stock OUT from from_branch; when RECEIVED: stock IN to to_branch.

Dashboard & Reports

Realtime dashboard endpoint /api/dashboard/summary returning:

daily_sales, monthly_sales, total_customers, low_stock_count, telebirr_agents_count

telebirr distributor balance (computed from GL accounts mapping)

today_issued, today_repayments, loans_outstanding

channel balances (by GL accounts)

Pending repayments: query top agents with positive AR balance (view or query).

Activity table: paginated audit_logs with filters.

Broadcast receipts/telebirr/audit events for live updates.

Compliance & Validation

Compliance checks: expired role assignments, excessive capabilities, frequent denials.

Validation dashboard: customer/product validation states.

Database — PostgreSQL Migrations (outline)

Use Laravel migrations; for enums use raw SQL (DB::statement) to create Postgres enum types:

create type tx_type as enum ('ISSUE','REPAY','LOAN','TOPUP','SALE','TRANSFER','ADJUST');

create type channel as enum ('CBE','EBIRR','COOPAY','ABASINIYA','AWASH','DASHEN','TELEBIRR','ESAHAL','H_CASH');

Create UUIDs via Ramsey\Uuid or Str::uuid().

Add necessary unique indexes and FKs.

Add check constraints (e.g., debit >= 0, credit >= 0).

Eloquent Models & Relationships (examples)

User hasMany UserRoleAssignment, belongsTo Branch.

Role belongsToMany Capability via RoleCapability.

InventoryItem belongsTo Product & Branch; StockMovement belongsTo Product/Branch.

GLJournal hasMany GLLines.

TelebirrTransaction belongsTo Agent & Journal.

Policies check hasCapability($key, $branchId) via cached user_policies.

RBAC Resolution

Command php artisan rbac:rebuild {userId?} to populate user_policies from roles/assignments.

Rebuild on: role change, capability update, new assignment, revoke.

Gate helper: Gate::define('cap', fn(User $u, string $key, ?string $branchId) => Policy::check($u,$key,$branchId));

Sidebar/menus are driven by capabilities list; show/hide accordingly.

Services (transactional)

InventoryService, POSService, TelebirrService, TransferService, GLService, RBACService, AuditService.

All write operations use DB transactions and raise domain events for broadcasting.

Controllers & Routes

Web (Inertia) routes for dashboard, POS, inventory, telebirr, terminals, transfers, RBAC admin.

API routes (Sanctum) for AJAX: /api/dashboard/summary, /api/audit, /api/inventory/low, /api/telebirr/post-issue etc.

Form Requests for validation. Middleware for capability checks per route (e.g., ->middleware('cap:telebirr:issue')).

Frontend (Inertia + Vue 3)

Theme: “Green Juicy” enterprise look, accessible, animated micro-interactions.

Pages:

Login (Somali-inspired geometric background; 3D tilting card; email & password with autocomplete attributes; demo-fill button)

Dashboard (summary cards, quick actions, realtime activity table with pagination & search)

POS (cart, payments)

Inventory (stock table, opening balance, receive stock, low stock badges)

Telebirr (tabs: Issue/Loan/Repay/Top-up, Agents CRUD, Activity; responsive animated cards)

Terminals (open/close session)

Transfers (create/send/receive; details modal)

RBAC (roles, capabilities, assignments)

Reports/Compliance

Components: StatCard, ActionTile, Drawer, DataTable (server-paginated), ConfirmModal, Toast.

Realtime

Install laravel-websockets; configure Echo + Pusher driver.

Broadcast events:

ReceiptPosted, TelebirrTransactionPosted, AuditLogged, StockChanged

Dashboard subscribes and refreshes affected widgets.

Seeding (demo)

Branches: Main, Hamda Hotel, Chinaksan.

Roles: admin, manager, sales, inventory, finance.

Capabilities examples:

sales:checkout, sales:void, sales:refund

inventory:view_stock, inventory:receive, inventory:transfer

telebirr:issue, telebirr:repay, telebirr:loan, telebirr:topup

management:users, management:roles

Create demo users; assign branch & roles; run rbac:rebuild.

Security

Strong validation on all write endpoints; branchId required when applicable.

Authorization via policies/middleware per capability.

Idempotency: every Telebirr and GL posting accepts idempotency_key, stored and enforced.

CSRF for web, Sanctum for SPA/API.

Input audit (ip, user-agent).

DB constraints prevent inconsistent GL and stock.

Performance

Cache user_policies, sidebar menus, dashboard KPIs (short TTL) with Redis.

Eager load relationships; paginate large tables.

Proper DB indexes on FKs & frequently filtered fields (branch_id, created_at, status).

Queue heavy tasks (rebuild policies, reports).

Testing

Pest tests for services (GL balancing, stock movements, RBAC).

HTTP tests for auth & RBAC middleware.

Playwright E2E: login, dashboard loads, quick actions, Telebirr forms, inventory receive.

Deliverables

Full Laravel project with migrations, seeders, factories.

Service classes with transactions & events.

Policies, middleware, and rbac:rebuild command.

Inertia + Vue 3 pages with responsive, animated UI.

Docker compose for local prod-like run.

README with setup:

cp .env.example .env

composer install && npm i

php artisan key:generate

docker compose up -d

php artisan migrate --seed

php artisan websockets:serve (or supervisor)

npm run dev (or build)

Acceptance Criteria

Login requires correct credentials; inactive users blocked.

Sidebar shows only modules the user has capability for; changing roles/capabilities updates menus after rbac:rebuild (or automatic listener).

Telebirr Issue/Loan/Repay/Top-up create GL balanced journals, transactions, audit logs; idempotent by key.

POS posts receipts, affects stock and GL.

Inventory receive/transfer updates quantities and movements properly.

Dashboard shows live KPIs; activity table updates in realtime.

Terminals enforce single open session per terminal.

All write operations authorized & validated with helpful error messages.

Notes for the generator

Use strict types, DTOs or request objects for service inputs.

Prefer Repository/Service structure to keep controllers thin.

Wrap domain operations in DB::transaction.

Write clean migrations with indexes & constraints; use Postgres enums where specified.