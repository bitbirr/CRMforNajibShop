# Najib ERP — Laravel 11 Full Scaffold

Below is a production-grade scaffold you can paste into a fresh Laravel 11 repo. It includes:
- Migrations (PostgreSQL, enums, constraints, triggers)
- Models & relationships
- RBAC cache + middleware + policy gates
- Services (GL, Telebirr, Inventory, POS, Transfers)
- Events + Broadcasting (Reverb/Pusher-ready)
- Audit logging trait & observer
- Seeders
- Inertia + Vue 3 + Tailwind “Green Juicy” theme
- Docker Compose (nginx, php-fpm, postgres, redis, horizon, reverb)
- Pest & Playwright tests
- README quickstart

> Tip: Search within this canvas for filenames like `database/migrations/...` or `app/Services/...` to jump.

---

## 0) File Tree (essential parts)

```
.
├── docker/                    
│   ├── nginx.conf
│   └── php.ini
├── docker-compose.yml
├── .env.example
├── composer.json
├── package.json
├── vite.config.js
├── postcss.config.js
├── tailwind.config.js
├── resources/
│   ├── css/app.css
│   ├── js/
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   ├── Pages/
│   │   │   ├── Auth/Login.vue
│   │   │   ├── Dashboard.vue
│   │   │   ├── POS/Index.vue
│   │   │   ├── Inventory/Index.vue
│   │   │   ├── Telebirr/Index.vue
│   │   │   ├── Terminals/Index.vue
│   │   │   ├── Transfers/Index.vue
│   │   │   ├── RBAC/Index.vue
│   │   │   └── Reports/Compliance.vue
│   │   └── Components/{StatCard.vue,ActionTile.vue,DataTable.vue,ConfirmModal.vue,Toast.vue,Drawer.vue}
│   └── views/app.blade.php
├── routes/
│   ├── web.php
│   ├── api.php
│   └── channels.php
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Branch.php
│   │   ├── Role.php
│   │   ├── Capability.php
│   │   ├── UserRoleAssignment.php
│   │   ├── Product.php
│   │   ├── InventoryItem.php
│   │   ├── StockMovement.php
│   │   ├── Receipt.php
│   │   ├── ReceiptLine.php
│   │   ├── GLAccount.php
│   │   ├── GLJournal.php
│   │   ├── GLLine.php
│   │   ├── TelebirrAgent.php
│   │   ├── TelebirrTransaction.php
│   │   ├── Terminal.php
│   │   ├── TerminalSession.php
│   │   ├── TransferHeader.php
│   │   ├── TransferLine.php
│   │   └── AuditLog.php
│   ├── Policies/
│   │   └── GateService.php
│   ├── Http/
│   │   ├── Controllers/{DashboardController.php,POSController.php,InventoryController.php,TelebirrController.php,TerminalController.php,TransferController.php,RBACController.php,AuditController.php}
│   │   ├── Middleware/EnsureHasCapability.php
│   │   └── Requests/{TelebirrPostRequest.php,POSPostRequest.php,InventoryReceiveRequest.php,TransferRequest.php}
│   ├── Services/{RBACService.php,GLService.php,TelebirrService.php,InventoryService.php,POSService.php,TransferService.php,AuditService.php}
│   ├── Observers/{AuditableObserver.php}
│   ├── Traits/{Auditable.php}
│   ├── Broadcasting/{ReceiptPosted.php,TelebirrTransactionPosted.php,AuditLogged.php,StockChanged.php}
│   ├── Console/Commands/RbacRebuild.php
│   └── Providers/AppServiceProvider.php
├── database/
│   ├── migrations/
│   │   ├── 2025_01_01_000000_create_enums.php
│   │   ├── 2025_01_01_000100_create_rbac_tables.php
│   │   ├── 2025_01_01_000200_create_audit_logs.php
│   │   ├── 2025_01_01_000300_create_products_inventory.php
│   │   ├── 2025_01_01_000400_create_pos_tables.php
│   │   ├── 2025_01_01_000500_create_gl_tables.php
│   │   ├── 2025_01_01_000600_create_telebirr_tables.php
│   │   ├── 2025_01_01_000700_create_terminals.php
│   │   ├── 2025_01_01_000800_create_transfers.php
│   │   └── 2025_01_01_000900_policy_cache.php
│   ├── seeders/{DatabaseSeeder.php,DemoSeeder.php}
│   └── factories/{UserFactory.php,...}
├── tests/
│   ├── Pest.php
│   ├── Feature/{AuthTest.php,RBACTest.php,TelebirrServiceTest.php,GLServiceTest.php,InventoryServiceTest.php}
│   └── Unit/{GLBalancingTest.php,StockMovementTest.php}
└── playwright/
    ├── playwright.config.ts
    └── tests/{login.spec.ts,dashboard.spec.ts,telebirr.spec.ts}
```

---

## 1) `.env.example`

```env
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

# Choose your broadcaster: reverb (first-party) or pusher
BROADCAST_DRIVER=reverb

# Reverb (first-party websockets)
REVERB_APP_ID=local
REVERB_APP_KEY=local
REVERB_APP_SECRET=local
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

# If you prefer Pusher instead of Reverb:
PUSHER_APP_ID=local
PUSHER_APP_KEY=local
PUSHER_APP_SECRET=local
PUSHER_APP_CLUSTER=mt1

WEBSOCKETS_SSL_LOCAL_CERT=
WEBSOCKETS_SSL_LOCAL_PK=
```

---

## 2) `composer.json`

```json
{
  "name": "najib/erp",
  "type": "project",
  "require": {
    "php": ">=8.2",
    "laravel/framework": "^11.0",
    "laravel/sanctum": "^4.0",
    "laravel/horizon": "^5.24",
    "laravel/reverb": "^1.0",
    "ramsey/uuid": "^4.7"
  },
  "require-dev": {
    "pestphp/pest": "^3.0",
    "pestphp/pest-plugin-laravel": "^3.0",
    "nunomaduro/collision": "^8.0"
  },
  "scripts": {
    "post-root-package-install": [
      "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
    ],
    "post-create-project-cmd": [
      "@php artisan key:generate --ansi"
    ]
  }
}
```

> Note: Horizon 5.x works on Laravel 11; Reverb is used for first‑party websockets.

---

## 3) Docker Compose & config

### `docker-compose.yml`
```yaml
services:
  app:
    build: .
    image: najib-erp-php
    container_name: php
    volumes:
      - ./:/var/www/html
      - ./docker/php.ini:/usr/local/etc/php/conf.d/custom.ini
    depends_on: [postgres, redis]

  queue:
    image: najib-erp-php
    command: php artisan horizon
    restart: unless-stopped
    volumes:
      - ./:/var/www/html
    depends_on: [app, redis]

  reverb:
    image: najib-erp-php
    command: php artisan reverb:start --host=0.0.0.0 --port=8080
    environment:
      - REVERB_HOST=0.0.0.0
    ports:
      - "8080:8080"
    volumes:
      - ./:/var/www/html
    depends_on: [app]

  nginx:
    image: nginx:alpine
    ports: ["80:80"]
    volumes:
      - ./:/var/www/html
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on: [app]

  postgres:
    image: postgres:15
    environment:
      POSTGRES_DB: najib
      POSTGRES_USER: postgres
      POSTGRES_PASSWORD: postgres
    ports: ["5432:5432"]
    volumes:
      - pgdata:/var/lib/postgresql/data

  redis:
    image: redis:7
    ports: ["6379:6379"]

volumes:
  pgdata: {}
```

### `docker/nginx.conf`
```nginx
server {
  listen 80;
  server_name _;
  root /var/www/html/public;

  index index.php index.html;
  location / {
    try_files $uri $uri/ /index.php?$query_string;
  }
  location ~ \.php$ {
    include fastcgi_params;
    fastcgi_pass app:9000;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param PATH_INFO $fastcgi_path_info;
  }
}
```

---

## 4) PostgreSQL Enums & Core Migrations

### `database/migrations/2025_01_01_000000_create_enums.php`
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("CREATE TYPE tx_type AS ENUM ('ISSUE','REPAY','LOAN','TOPUP','SALE','TRANSFER','ADJUST')");
        DB::statement("CREATE TYPE channel AS ENUM ('CBE','EBIRR','COOPAY','ABASINIYA','AWASH','DASHEN','TELEBIRR','ESAHAL','H_CASH')");
        DB::statement("CREATE TYPE receipt_status AS ENUM ('DRAFT','POSTED','VOIDED','REFUNDED')");
        DB::statement("CREATE TYPE term_status AS ENUM ('OPEN','CLOSED')");
        DB::statement("CREATE TYPE transfer_status AS ENUM ('DRAFT','SENT','RECEIVED','CANCELLED')");
    }
    public function down(): void
    {
        DB::statement("DROP TYPE IF EXISTS transfer_status");
        DB::statement("DROP TYPE IF EXISTS term_status");
        DB::statement("DROP TYPE IF EXISTS receipt_status");
        DB::statement("DROP TYPE IF EXISTS channel");
        DB::statement("DROP TYPE IF EXISTS tx_type");
    }
};
```

### `database/migrations/2025_01_01_000100_create_rbac_tables.php`
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('name');
            $t->string('location')->nullable();
            $t->boolean('is_main_branch')->default(false);
            $t->timestamps();
        });

        Schema::table('users', function (Blueprint $t) {
            $t->uuid('branch_id')->nullable()->after('password');
            $t->boolean('is_active')->default(true);
            $t->foreign('branch_id')->references('id')->on('branches');
        });

        Schema::create('roles', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('name')->unique();
            $t->string('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('capabilities', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('key')->unique();
            $t->string('category');
            $t->string('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('role_capabilities', function (Blueprint $t) {
            $t->uuid('role_id');
            $t->uuid('capability_id');
            $t->primary(['role_id','capability_id']);
            $t->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $t->foreign('capability_id')->references('id')->on('capabilities')->cascadeOnDelete();
        });

        Schema::create('user_role_assignments', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('user_id');
            $t->uuid('role_id');
            $t->boolean('scope_all_branches')->default(false);
            $t->timestampTz('starts_at')->nullable();
            $t->timestampTz('ends_at')->nullable();
            $t->uuid('created_by')->nullable();
            $t->timestamps();
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $t->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });

        // Add Postgres uuid[] branch_ids
        DB::statement("ALTER TABLE user_role_assignments ADD COLUMN branch_ids uuid[] NULL");

        Schema::create('user_policies', function (Blueprint $t) {
            $t->uuid('user_id')->primary();
            $t->jsonb('capabilities'); // array of strings
            $t->boolean('branch_scope_all')->default(false);
            $t->jsonb('branch_ids')->nullable(); // array of uuids
            $t->timestamp('updated_at')->useCurrent();
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_policies');
        Schema::dropIfExists('user_role_assignments');
        Schema::dropIfExists('role_capabilities');
        Schema::dropIfExists('capabilities');
        Schema::dropIfExists('roles');
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('branch_id');
            $t->dropColumn(['branch_id','is_active']);
        });
        Schema::dropIfExists('branches');
    }
};
```

### `database/migrations/2025_01_01_000200_create_audit_logs.php`
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->uuid('user_id')->nullable();
            $t->text('action');
            $t->string('entity_type');
            $t->string('entity_id');
            $t->jsonb('details')->nullable();
            $t->string('ip_address')->nullable();
            $t->text('user_agent')->nullable();
            $t->timestampTz('timestamp')->useCurrent();
            $t->index(['entity_type','entity_id']);
            $t->index('timestamp');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
```

### `database/migrations/2025_01_01_000300_create_products_inventory.php`
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('sku')->unique();
            $t->string('name');
            $t->string('unit');
            $t->boolean('is_active')->default(true);
            $t->integer('reorder_level')->default(0);
            $t->timestamps();
        });

        Schema::create('inventory_items', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->uuid('product_id');
            $t->uuid('branch_id');
            $t->decimal('quantity',14,2)->default(0);
            $t->decimal('reserved_quantity',14,2)->default(0);
            $t->integer('reorder_level')->default(0);
            $t->timestamps();
            $t->unique(['product_id','branch_id']);
            $t->foreign('product_id')->references('id')->on('products');
            $t->foreign('branch_id')->references('id')->on('branches');
            $t->index(['branch_id']);
        });

        Schema::create('stock_movements', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->uuid('product_id');
            $t->uuid('branch_id');
            $t->decimal('qty',14,2);
            $t->enum('type',[ 'OPENING','RECEIVE','SALE','ADJUST','TRANSFER_OUT','TRANSFER_IN' ]);
            $t->string('ref_table')->nullable();
            $t->string('ref_id')->nullable();
            $t->uuid('created_by');
            $t->timestamp('created_at')->useCurrent();
            $t->index(['branch_id','created_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('products');
    }
};
```

### `database/migrations/2025_01_01_000400_create_pos_tables.php`
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->uuid('branch_id');
            $t->uuid('user_id');
            $t->decimal('total_amount',14,2);
            $t->decimal('paid_amount',14,2)->default(0);
            $t->decimal('change_amount',14,2)->default(0);
            $t->enum('status',[ 'DRAFT','POSTED','VOIDED','REFUNDED' ]);
            $t->timestamp('created_at')->useCurrent();
            $t->timestamp('posted_at')->nullable();
            $t->text('memo')->nullable();
            $t->index(['branch_id','created_at']);
        });

        Schema::create('receipt_lines', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->bigInteger('receipt_id');
            $t->uuid('product_id');
            $t->decimal('qty',14,2);
            $t->decimal('price',14,2);
            $t->decimal('total',14,2);
            $t->index('receipt_id');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('receipt_lines');
        Schema::dropIfExists('receipts');
    }
};
```

### `database/migrations/2025_01_01_000500_create_gl_tables.php`
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gl_accounts', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('code')->unique();
            $t->string('name');
            $t->enum('type',[ 'ASSET','LIABILITY','EQUITY','INCOME','EXPENSE' ]);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('gl_journals', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->enum('tx_type',[ 'ISSUE','REPAY','LOAN','TOPUP','SALE','TRANSFER','ADJUST' ]);
            $t->enum('channel',[ 'CBE','EBIRR','COOPAY','ABASINIYA','AWASH','DASHEN','TELEBIRR','ESAHAL','H_CASH' ])->nullable();
            $t->uuid('branch_id');
            $t->bigInteger('agent_id')->nullable();
            $t->string('ref_no')->nullable();
            $t->text('description')->nullable();
            $t->timestampTz('posted_at')->nullable();
            $t->uuid('created_by');
            $t->bigInteger('reversal_of')->nullable();
            $t->decimal('total_debit',14,2)->default(0);
            $t->decimal('total_credit',14,2)->default(0);
            $t->date('value_date')->nullable();
            $t->text('memo')->nullable();
            $t->timestamps();
            $t->index(['branch_id','posted_at']);
        });

        Schema::create('gl_lines', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->bigInteger('journal_id');
            $t->bigInteger('account_id');
            $t->decimal('debit',14,2)->default(0);
            $t->decimal('credit',14,2)->default(0);
            $t->integer('line_no');
            $t->text('narration')->nullable();
            $t->index('journal_id');
            $t->index('account_id');
            $t->check('debit >= 0');
            $t->check('credit >= 0');
        });

        Schema::create('idempotency_keys', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->bigInteger('journal_id')->nullable();
            $t->timestamp('created_at')->useCurrent();
        });

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION validate_gl_balance() RETURNS trigger AS $$
DECLARE sdebit NUMERIC; scredit NUMERIC; BEGIN
  SELECT COALESCE(SUM(debit),0), COALESCE(SUM(credit),0) INTO sdebit, scredit FROM gl_lines WHERE journal_id = NEW.id;
  IF sdebit <> scredit THEN RAISE EXCEPTION 'GL journal % not balanced: % vs %', NEW.id, sdebit, scredit; END IF;
  UPDATE gl_journals SET total_debit = sdebit, total_credit = scredit WHERE id = NEW.id; RETURN NEW; END; $$ LANGUAGE plpgsql;

CREATE TRIGGER trg_validate_gl_balance AFTER INSERT OR UPDATE ON gl_journals
FOR EACH ROW EXECUTE FUNCTION validate_gl_balance();
SQL);
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS trg_validate_gl_balance ON gl_journals; DROP FUNCTION IF EXISTS validate_gl_balance();");
        Schema::dropIfExists('idempotency_keys');
        Schema::dropIfExists('gl_lines');
        Schema::dropIfExists('gl_journals');
        Schema::dropIfExists('gl_accounts');
    }
};
```

### `database/migrations/2025_01_01_000600_create_telebirr_tables.php`
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('telebirr_agents', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('short_code')->unique();
            $t->string('name');
            $t->string('phone')->nullable();
            $t->string('location')->nullable();
            $t->enum('status', ['Active','Dormant','Inactive'])->default('Active');
            $t->timestamps();
        });

        Schema::create('telebirr_transactions', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('tx_id')->unique();
            $t->enum('tx_type',[ 'ISSUE','REPAY','LOAN','TOPUP' ]);
            $t->enum('channel',[ 'CBE','EBIRR','COOPAY','ABASINIYA','AWASH','DASHEN','TELEBIRR','ESAHAL','H_CASH' ])->nullable();
            $t->uuid('branch_id');
            $t->bigInteger('agent_id')->nullable();
            $t->decimal('amount',14,2);
            $t->bigInteger('journal_id');
            $t->text('description')->nullable();
            $t->timestampTz('created_at')->useCurrent();
            $t->index(['branch_id','created_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('telebirr_transactions');
        Schema::dropIfExists('telebirr_agents');
    }
};
```

### `database/migrations/2025_01_01_000700_create_terminals.php`
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('terminals', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('branch_id');
            $t->string('name');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('terminal_sessions', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->uuid('terminal_id');
            $t->uuid('user_id');
            $t->timestampTz('open_time');
            $t->timestampTz('close_time')->nullable();
            $t->decimal('opening_cash',14,2)->default(0);
            $t->decimal('closing_cash',14,2)->default(0);
            $t->decimal('variance',14,2)->default(0);
            $t->enum('status',[ 'OPEN','CLOSED' ]);
            $t->text('notes')->nullable();
            $t->index(['terminal_id','status']);
        });

        // One OPEN per terminal
        DB::statement("CREATE UNIQUE INDEX term_one_open ON terminal_sessions(terminal_id) WHERE status='OPEN'");
    }
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS term_one_open");
        Schema::dropIfExists('terminal_sessions');
        Schema::dropIfExists('terminals');
    }
};
```

### `database/migrations/2025_01_01_000800_create_transfers.php`
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transfer_headers', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('from_branch_id');
            $t->uuid('to_branch_id');
            $t->uuid('created_by');
            $t->enum('status',[ 'DRAFT','SENT','RECEIVED','CANCELLED' ])->default('DRAFT');
            $t->timestamp('created_at')->useCurrent();
            $t->index(['from_branch_id','to_branch_id']);
        });

        Schema::create('transfer_lines', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->uuid('header_id');
            $t->uuid('product_id');
            $t->decimal('qty',14,2);
            $t->index('header_id');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('transfer_lines');
        Schema::dropIfExists('transfer_headers');
    }
};
```

### `database/migrations/2025_01_01_000900_policy_cache.php`
```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('policy_rebuild_jobs', function (Blueprint $t) {
            $t->id();
            $t->uuid('user_id')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('policy_rebuild_jobs');
    }
};
```

---

## 5) Models (samples)

### `app/Models/User.php`
```php
<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['id','name','email','password','is_active','branch_id'];
    protected $casts = [ 'id' => 'string', 'is_active' => 'boolean' ];
    public $incrementing = false;
    protected $keyType = 'string';

    public function branch(){ return $this->belongsTo(Branch::class); }
    public function assignments(){ return $this->hasMany(UserRoleAssignment::class); }
}
```

### `app/Models/GLJournal.php`
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GLJournal extends Model
{
    protected $fillable = ['tx_type','channel','branch_id','agent_id','ref_no','description','posted_at','created_by','reversal_of','value_date','memo'];
    public function lines(){ return $this->hasMany(GLLine::class,'journal_id'); }
}
```

(Other models follow standard relations as per schema.)

---

## 6) RBAC Gate + Cache

### `app/Policies/GateService.php`
```php
<?php
namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GateService
{
    public static function hasCapability(User $user, string $capability, ?string $branchId = null): bool
    {
        $cache = Cache::remember("user_policies:{$user->id}", 300, function() use ($user){
            return DB::table('user_policies')->where('user_id',$user->id)->first();
        });
        if(!$cache){ return false; }
        $caps = collect(json_decode($cache->capabilities ?? '[]', true));
        $branchAll = (bool)($cache->branch_scope_all ?? false);
        $branchIds = collect(json_decode($cache->branch_ids ?? '[]', true));
        if(!$caps->contains($capability)) return false;
        if($branchAll) return true;
        if($branchId===null) return false;
        return $branchIds->contains($branchId);
    }
}
```

### `app/Http/Middleware/EnsureHasCapability.php`
```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Policies\GateService;

class EnsureHasCapability
{
    public function handle(Request $request, Closure $next, string $capability, string $branchParam = 'branch_id')
    {
        $user = $request->user();
        $branchId = $request->input($branchParam) ?? $request->route($branchParam);
        if(!$user || !GateService::hasCapability($user, $capability, $branchId)){
            throw new AccessDeniedHttpException('You are not authorized for this action / branch.');
        }
        return $next($request);
    }
}
```

### `app/Services/RBACService.php`
```php
<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class RBACService
{
    public static function rebuild(?string $userId = null): void
    {
        $users = DB::table('users')->when($userId, fn($q)=>$q->where('id',$userId))->pluck('id');
        foreach($users as $uid){
            $assignments = DB::table('user_role_assignments')
                ->where('user_id',$uid)
                ->where(function($q){
                    $q->whereNull('starts_at')->orWhere('starts_at','<=',now());
                })
                ->where(function($q){
                    $q->whereNull('ends_at')->orWhere('ends_at','>=',now());
                })
                ->get();

            $roleIds = $assignments->pluck('role_id');
            $caps = DB::table('role_capabilities')->whereIn('role_id',$roleIds)
                ->join('capabilities','capabilities.id','=','role_capabilities.capability_id')
                ->where('capabilities.is_active',true)
                ->pluck('capabilities.key')->unique()->values();

            $branchAll = $assignments->contains(fn($a)=>$a->scope_all_branches);
            $branchIds = $assignments->flatMap(function($a){
                $arr = $a->branch_ids ? json_decode(json_encode($a->branch_ids), true) : [];
                return $arr ?: [];
            })->unique()->values();

            DB::table('user_policies')->upsert([
                'user_id'=>$uid,
                'capabilities'=>json_encode($caps),
                'branch_scope_all'=>$branchAll,
                'branch_ids'=>json_encode($branchIds),
                'updated_at'=>now(),
            ], ['user_id']);

            Cache::forget("user_policies:{$uid}");
        }
    }
}
```

### `app/Console/Commands/RbacRebuild.php`
```php
<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RBACService;

class RbacRebuild extends Command
{
    protected $signature = 'rbac:rebuild {userId?}';
    protected $description = 'Rebuild cached user policies';
    public function handle(): int
    {
        RBACService::rebuild($this->argument('userId'));
        $this->info('RBAC cache rebuilt');
        return self::SUCCESS;
    }
}
```

---

## 7) Audit Logging

### `app/Traits/Auditable.php`
```php
<?php
namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function($model){ $model->writeAudit('created'); });
        static::updated(function($model){ $model->writeAudit('updated'); });
        static::deleted(function($model){ $model->writeAudit('deleted'); });
    }

    protected function writeAudit(string $action): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => static::class,
            'entity_id' => (string)($this->getKey()),
            'details' => request()->all(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
```

### `app/Models/AuditLog.php`
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AuditLog extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id','action','entity_type','entity_id','details','ip_address','user_agent'];
    protected $casts = ['details'=>'array'];
}
```

---

## 8) Services (transactional)

### `app/Services/GLService.php`
```php
<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;

class GLService
{
    /** @return int journal id */
    public static function post(string $txType, ?string $channel, string $branchId, ?int $agentId, string $refNo, string $description, array $lines, ?string $idempotencyKey=null): int
    {
        return DB::transaction(function() use ($txType,$channel,$branchId,$agentId,$refNo,$description,$lines,$idempotencyKey){
            if($idempotencyKey){
                $existing = DB::table('idempotency_keys')->where('key',$idempotencyKey)->first();
                if($existing && $existing->journal_id){ return (int)$existing->journal_id; }
            }
            $jid = DB::table('gl_journals')->insertGetId([
                'tx_type'=>$txType,
                'channel'=>$channel,
                'branch_id'=>$branchId,
                'agent_id'=>$agentId,
                'ref_no'=>$refNo,
                'description'=>$description,
                'posted_at'=>now(),
                'created_by'=>auth()->id(),
                'value_date'=>now()->toDateString(),
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);
            $i=1; $sumD=0; $sumC=0;
            foreach($lines as $ln){
                [$acc,$debit,$credit,$narr] = $ln; $sumD+=$debit; $sumC+=$credit;
                DB::table('gl_lines')->insert([
                    'journal_id'=>$jid,'account_id'=>$acc,'debit'=>$debit,'credit'=>$credit,'line_no'=>$i++,'narration'=>$narr
                ]);
            }
            if($sumD != $sumC){ throw new \RuntimeException('GL not balanced'); }
            if($idempotencyKey){ DB::table('idempotency_keys')->insert(['key'=>$idempotencyKey,'journal_id'=>$jid]); }
            return $jid;
        });
    }
}
```

### `app/Services/TelebirrService.php`
```php
<?php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Services\GLService;
use App\Events\TelebirrTransactionPosted;
use App\Policies\GateService;

class TelebirrService
{
    public static function postIssue(string $agentShortCode, string $channel, float $amount, string $branchId, string $description, string $key): int
    {
        self::authorize('telebirr:issue',$branchId);
        return self::post('ISSUE',$agentShortCode,$channel,$amount,$branchId,$description,$key);
    }
    public static function postRepay(string $agentShortCode, string $channel, float $amount, string $branchId, string $description, string $key): int
    {
        self::authorize('telebirr:repay',$branchId);
        return self::post('REPAY',$agentShortCode,$channel,$amount,$branchId,$description,$key);
    }
    public static function postLoan(string $agentShortCode, float $amount, string $branchId, string $description, string $key): int
    {
        self::authorize('telebirr:loan',$branchId);
        return self::post('LOAN',$agentShortCode,null,$amount,$branchId,$description,$key);
    }
    public static function postTopup(string $channel, ?int $bankGlId, float $amount, string $branchId, string $description, string $key): int
    {
        self::authorize('telebirr:topup',$branchId);
        // Example: debit bank, credit distributor float
        $jid = GLService::post('TOPUP',$channel,$branchId,null,'TOPUP-'.uniqid(),$description,[
            [ $bankGlId, $amount, 0, 'Bank debit' ],
            [ self::gl('TELEBIRR_FLOAT'), 0, $amount, 'Float top-up' ],
        ], $key);
        event(new TelebirrTransactionPosted(['tx_type'=>'TOPUP','branch_id'=>$branchId,'amount'=>$amount,'journal_id'=>$jid]));
        DB::table('telebirr_transactions')->insert([
            'tx_id'=>'TB'.uniqid(),'tx_type'=>'TOPUP','channel'=>$channel,'branch_id'=>$branchId,'agent_id'=>null,
            'amount'=>$amount,'journal_id'=>$jid,'description'=>$description
        ]);
        return $jid;
    }

    private static function post(string $type, string $agentShortCode, ?string $channel, float $amount, string $branchId, string $description, string $key): int
    {
        $agent = DB::table('telebirr_agents')->where('short_code',$agentShortCode)->first();
        if(!$agent){ throw new \InvalidArgumentException('Agent not found'); }

        // Map example accounts (replace with real chart of accounts)
        $distFloat = self::gl('TELEBIRR_FLOAT');
        $agentAR   = self::gl('AGENT_AR');
        $cashBox   = self::gl('CASH_BOX');

        $ref = $type.'-'.$agent->id.'-'.uniqid();
        $lines = match($type){
            'ISSUE' => [ [ $agentAR, $amount, 0, 'Agent receivable' ], [ $distFloat, 0, $amount, 'Reduce float' ] ],
            'REPAY' => [ [ $distFloat, $amount, 0, 'Float restored' ], [ $agentAR, 0, $amount, 'Reduce AR' ] ],
            'LOAN'  => [ [ $agentAR, $amount, 0, 'Agent loan' ], [ $cashBox, 0, $amount, 'Cash out' ] ],
            default => throw new \RuntimeException('Unsupported')
        };

        $jid = GLService::post($type,$channel,$branchId,$agent->id,$ref,$description,$lines,$key);

        DB::table('telebirr_transactions')->insert([
            'tx_id'=>$ref,'tx_type'=>$type,'channel'=>$channel,'branch_id'=>$branchId,
            'agent_id'=>$agent->id,'amount'=>$amount,'journal_id'=>$jid,'description'=>$description
        ]);
        event(new TelebirrTransactionPosted(['tx_type'=>$type,'branch_id'=>$branchId,'amount'=>$amount,'journal_id'=>$jid,'agent_id'=>$agent->id]));
        return $jid;
    }

    private static function gl(string $code): int
    {
        return (int) (DB::table('gl_accounts')->where('code',$code)->value('id') ?? 0);
    }

    private static function authorize(string $capability, string $branchId): void
    {
        $u = auth()->user();
        if(!$u || !\App\Policies\GateService::hasCapability($u, $capability, $branchId)){
            abort(403,'Not allowed');
        }
    }
}
```

### `app/Services/InventoryService.php` (excerpt)
```php
<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public static function receiveStock(string $branchId, string $productId, float $qty, ?string $supplier, ?string $memo): void
    {
        DB::transaction(function() use($branchId,$productId,$qty,$memo){
            DB::table('inventory_items')->updateOrInsert(
                ['product_id'=>$productId,'branch_id'=>$branchId],
                DB::raw("quantity = COALESCE(quantity,0)+$qty, updated_at = NOW()")
            );
            DB::table('stock_movements')->insert([
                'product_id'=>$productId,'branch_id'=>$branchId,'qty'=>$qty,'type'=>'RECEIVE',
                'ref_table'=>'manual','ref_id'=>null,'created_by'=>auth()->id(),
            ]);
        });
    }
}
```

(Implement `openingBalance`, `transfer`, reserve/commit similarly.)

### `app/Services/POSService.php` (excerpt)
```php
<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use App\Events\{ReceiptPosted,StockChanged};

class POSService
{
    public static function postReceipt(int $receiptId, string $branchId, string $key): void
    {
        DB::transaction(function() use($receiptId,$branchId,$key){
            $r = DB::table('receipts')->where('id',$receiptId)->lockForUpdate()->first();
            if(!$r || $r->status!=='DRAFT'){ throw new \RuntimeException('Invalid receipt'); }
            $lines = DB::table('receipt_lines')->where('receipt_id',$receiptId)->get();
            // GL: debit cash, credit sales
            $cash = self::gl('CASH_BOX'); $sales = self::gl('SALES');
            $jid = GLService::post('SALE',null,$branchId,null,'SALE-'.$receiptId,'POS Sale',[ [ $cash,$r->paid_amount,0,'Cash' ], [ $sales,0,$r->total_amount,'Revenue' ] ],$key);

            // stock movements
            foreach($lines as $ln){
                DB::table('inventory_items')->where(['product_id'=>$ln->product_id,'branch_id'=>$branchId])
                    ->update([ 'quantity'=>DB::raw('quantity - '.$ln->qty) ]);
                DB::table('stock_movements')->insert([
                    'product_id'=>$ln->product_id,'branch_id'=>$branchId,'qty'=>-$ln->qty,'type'=>'SALE',
                    'ref_table'=>'receipts','ref_id'=>$receiptId,'created_by'=>auth()->id(),
                ]);
            }
            DB::table('receipts')->where('id',$receiptId)->update(['status'=>'POSTED','posted_at'=>now()]);
            event(new ReceiptPosted(['receipt_id'=>$receiptId,'journal_id'=>$jid]));
            event(new StockChanged(['branch_id'=>$branchId]));
        });
    }
    private static function gl(string $code): int { return (int) (DB::table('gl_accounts')->where('code',$code)->value('id') ?? 0); }
}
```

---

## 9) Events & Broadcasting

### `app/Broadcasting/TelebirrTransactionPosted.php`
```php
<?php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\Channel;

class TelebirrTransactionPosted implements ShouldBroadcast
{
    use InteractsWithSockets;
    public array $payload;
    public function __construct(array $payload){ $this->payload = $payload; }
    public function broadcastOn(){ return new Channel('telebirr'); }
    public function broadcastAs(){ return 'TelebirrTransactionPosted'; }
}
```

(Similar for `ReceiptPosted`, `AuditLogged`, `StockChanged`.)

---

## 10) Routes (capability middleware)

### `routes/web.php`
```php
<?php
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['web','auth'])->group(function(){
    Route::get('/', fn()=>Inertia::render('Dashboard/Index'));
    Route::get('/pos', fn()=>Inertia::render('POS/Index'))->middleware('cap:sales:checkout');
    Route::get('/inventory', fn()=>Inertia::render('Inventory/Index'))->middleware('cap:inventory:view_stock');
    Route::get('/telebirr', fn()=>Inertia::render('Telebirr/Index'))->middleware('cap:telebirr:issue');
    // ...
});
```

### `routes/api.php`
```php
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{DashboardController,AuditController,TelebirrController,InventoryController,POSController};

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/dashboard/summary',[DashboardController::class,'summary']);
    Route::get('/audit',[AuditController::class,'index']);
    Route::post('/telebirr/issue',[TelebirrController::class,'issue'])->middleware('cap:telebirr:issue');
    Route::post('/telebirr/repay',[TelebirrController::class,'repay'])->middleware('cap:telebirr:repay');
    Route::post('/telebirr/loan',[TelebirrController::class,'loan'])->middleware('cap:telebirr:loan');
    Route::post('/telebirr/topup',[TelebirrController::class,'topup'])->middleware('cap:telebirr:topup');
});
```

---

## 11) Inertia + Vue 3 + Tailwind (Green Juicy Theme)

### `resources/css/app.css`
```css
@tailwind base;
@tailwind components;
@tailwind utilities;

:root{
  --gj-green:#14b85a; /* juicy primary */
  --gj-green-700:#0e8a44;
  --gj-mint:#a7f3d0;
  --gj-ink:#0f172a;
}

.btn{ @apply rounded-2xl px-4 py-2 font-semibold shadow transition hover:shadow-lg; }
.btn-primary{ @apply text-white; background: var(--gj-green); }
.card{ @apply rounded-2xl shadow p-4 bg-white; }
.badge-low{ @apply text-xs font-semibold px-2 py-1 rounded bg-red-100 text-red-700; }
```

### `resources/js/app.js`
```js
import './bootstrap'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})
```

### `resources/views/app.blade.php`
```blade
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>{{ config('app.name') }}</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800">
  @inertia
</body>
</html>
```

### `resources/js/Pages/Dashboard.vue`
```vue
<script setup>
import StatCard from '../Components/StatCard.vue'
</script>
<template>
  <div class="grid md:grid-cols-3 gap-4">
    <StatCard title="Today Sales" :value="'$ 0.00'" />
    <StatCard title="Telebirr Issued" :value="'$ 0.00'" />
    <StatCard title="Low Stock" :value="0" />
  </div>
</template>
```

(Other pages: POS cart, Telebirr tabs with forms, Inventory table with low-stock badges.)

---

## 12) Seeders

### `database/seeders/DemoSeeder.php`
```php
<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [ ['id'=>Str::uuid(),'name'=>'Main','is_main_branch'=>true], ['id'=>Str::uuid(),'name'=>'Hamda Hotel'], ['id'=>Str::uuid(),'name'=>'Chinaksan'] ];
        DB::table('branches')->insert($branches);

        $caps = [
            ['id'=>Str::uuid(),'key'=>'sales:checkout','category'=>'sales'],
            ['id'=>Str::uuid(),'key'=>'sales:void','category'=>'sales'],
            ['id'=>Str::uuid(),'key'=>'sales:refund','category'=>'sales'],
            ['id'=>Str::uuid(),'key'=>'inventory:view_stock','category'=>'inventory'],
            ['id'=>Str::uuid(),'key'=>'inventory:receive','category'=>'inventory'],
            ['id'=>Str::uuid(),'key'=>'inventory:transfer','category'=>'inventory'],
            ['id'=>Str::uuid(),'key'=>'telebirr:issue','category'=>'telebirr'],
            ['id'=>Str::uuid(),'key'=>'telebirr:repay','category'=>'telebirr'],
            ['id'=>Str::uuid(),'key'=>'telebirr:loan','category'=>'telebirr'],
            ['id'=>Str::uuid(),'key'=>'telebirr:topup','category'=>'telebirr'],
            ['id'=>Str::uuid(),'key'=>'management:users','category'=>'management'],
            ['id'=>Str::uuid(),'key'=>'management:roles','category'=>'management'],
        ];
        DB::table('capabilities')->insert($caps);

        $roles = [ ['id'=>Str::uuid(),'name'=>'admin'], ['id'=>Str::uuid(),'name'=>'manager'], ['id'=>Str::uuid(),'name'=>'sales'], ['id'=>Str::uuid(),'name'=>'inventory'], ['id'=>Str::uuid(),'name'=>'finance'] ];
        DB::table('roles')->insert($roles);

        // role -> all caps (admin)
        $adminId = $roles[0]['id'];
        foreach($caps as $c){ DB::table('role_capabilities')->insert([ 'role_id'=>$adminId,'capability_id'=>$c['id'] ]); }

        // demo GL
        DB::table('gl_accounts')->insert([
            ['code'=>'CASH_BOX','name'=>'Cash Box','type'=>'ASSET'],
            ['code'=>'SALES','name'=>'Sales Revenue','type'=>'INCOME'],
            ['code'=>'TELEBIRR_FLOAT','name'=>'Telebirr Float','type'=>'ASSET'],
            ['code'=>'AGENT_AR','name'=>'Agent Receivable','type'=>'ASSET']
        ]);
    }
}
```

---

## 13) Tests

### `tests/Unit/GLBalancingTest.php`
```php
<?php
it('ensures GLService posts balanced journal', function(){
    $jid = \App\Services\GLService::post('ADJUST',null,Str::uuid(),null,'TST','Test',[ [1,100,0,''], [2,0,100,''] ]);
    expect($jid)->toBeInt();
})->group('gl');
```

### `playwright/tests/login.spec.ts`
```ts
import { test, expect } from '@playwright/test'

test('login works', async ({ page }) => {
  await page.goto('http://localhost')
  await page.fill('input[name=email]','admin@example.com')
  await page.fill('input[name=password]','password')
  await page.click('button:has-text("Login")')
  await expect(page.locator('text=Dashboard')).toBeVisible()
})
```

---

## 14) README (Quickstart)

```md
# Najib ERP

## Setup

```bash
cp .env.example .env
composer install
npm i
php artisan key:generate

# start infra
docker compose up -d

# migrate & seed
php artisan migrate --seed

# start queues & websockets (or use Supervisor in prod)
php artisan horizon
php artisan reverb:start

# dev assets
npm run dev
```

### RBAC
```bash
php artisan rbac:rebuild             # all users
php artisan rbac:rebuild <user-uuid> # single user
```

### Broadcasting
- Reverb (default): set `BROADCAST_DRIVER=reverb` and start `php artisan reverb:start`.
- Pusher: set `BROADCAST_DRIVER=pusher` and fill pusher envs; use `laravel-echo` on frontend.

### Acceptance Checklist
- [ ] Login blocks inactive users
- [ ] Sidebar reflects capabilities; run `rbac:rebuild` after changes
- [ ] Telebirr flows create balanced GL & audit; idempotency enforced
- [ ] POS posts stock and GL; receipts broadcast
- [ ] Inventory receive/transfer adjust quantities & movements
- [ ] Dashboard KPIs live via events
- [ ] Terminals allow single open session (partial unique index)
```

---

## 15) Frontend: Example Telebirr Page (tabs & forms)

### `resources/js/Pages/Telebirr/Index.vue`
```vue
<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
const form = ref({ branch_id:'', agent_short_code:'', channel:'TELEBIRR', amount:0, description:'' })
function submit(type){
  router.post(`/api/telebirr/${type}`, form.value, { preserveScroll:true })
}
</script>
<template>
  <div class="space-y-4">
    <div class="card">
      <div class="flex gap-2">
        <input v-model="form.branch_id" placeholder="Branch UUID" class="border rounded p-2 grow"/>
        <input v-model="form.agent_short_code" placeholder="Agent Code" class="border rounded p-2"/>
        <input v-model.number="form.amount" type="number" step="0.01" class="border rounded p-2 w-32"/>
      </div>
      <div class="mt-3 flex gap-2">
        <button class="btn btn-primary" @click="submit('issue')">Issue</button>
        <button class="btn" style="background:var(--gj-mint)" @click="submit('repay')">Repay</button>
        <button class="btn" @click="submit('loan')">Loan</button>
        <button class="btn" @click="submit('topup')">Top-up</button>
      </div>
    </div>
  </div>
</template>
```

---

This scaffold is intentionally lean but production‑oriented: strict constraints, idempotency, DB triggers, cached RBAC, domain services with transactions, broadcast events, Dockerized infra, and a modern UI shell ready for your enterprise “Green Juicy” theme.



---

## Appendix — Windows fix for `ext-pcntl` (Horizon) and Dockerfile

You’re seeing:

> `laravel/horizon` requires `ext-pcntl` → missing on Windows CLI

`pcntl` is not available on native Windows PHP. Use one of these paths:

### Option A — Use Docker (recommended)
Add a Dockerfile that compiles required PHP extensions (pcntl, pdo_pgsql, bcmath, intl, zip) and enables Redis.

**Dockerfile** (place at project root):
```dockerfile
FROM php:8.2-fpm-alpine

# System deps
RUN apk add --no-cache git curl bash icu-dev libzip-dev libpq-dev oniguruma-dev autoconf g++ make

# PHP extensions
RUN docker-php-ext-install intl bcmath pcntl pdo pdo_pgsql zip
RUN pecl install redis \
 && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
```

**docker/php.ini** (if not present):
```ini
memory_limit=512M
post_max_size=32M
upload_max_filesize=32M
max_execution_time=120
opcache.enable=1
opcache.jit=1255
opcache.jit_buffer_size=64M
```

**Build & install via container**:
```bash
# (Re)build PHP image so pcntl is available
docker compose build app queue reverb

# Install composer deps *inside* the container
docker compose run --rm app composer install

# Generate key and run setup commands inside container
docker compose run --rm app php artisan key:generate
docker compose up -d
php artisan migrate --seed
php artisan horizon:install && php artisan horizon
php artisan reverb:start
```

### Option B — WSL2 (Ubuntu) instead of native Windows
```powershell
wsl --install -d Ubuntu
```
Then inside Ubuntu:
```bash
sudo apt update && sudo apt install -y php8.2-cli php8.2-pgsql php8.2-xml php8.2-mbstring php8.2-bcmath php8.2-zip php-redis composer
php -m | grep pcntl   # should print pcntl
composer install
```
Run the Laravel commands from the WSL shell (queues/reverb work great here).

### Option C — Skip Horizon locally
If you want to continue **without** Horizon on Windows:
1) Temporarily remove `"laravel/horizon": "^5.24"` from `composer.json` and run `composer install`.  
2) Set queues to the worker only:
   ```env
   QUEUE_CONNECTION=redis
   ```
3) Start a worker:
   ```bash
   php artisan queue:work
   ```
You can still use Horizon in Docker/production where `pcntl` is available.

### Option D — Temporary install ignoring the check (not recommended)
```bash
composer install --ignore-platform-req=ext-pcntl
```
This installs packages but Horizon won’t run on native Windows (no `pcntl`). Prefer Options A or B.
