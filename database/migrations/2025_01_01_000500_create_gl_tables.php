<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // GL Accounts
        Schema::create('gl_accounts', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('code')->unique();
            $t->string('name');
            $t->enum('type', ['ASSET','LIABILITY','EQUITY','INCOME','EXPENSE']);
            $t->boolean('is_active')->default(true);
            $t->timestampsTz();
        });

        // Journals (headers)
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
            $t->decimal('total_debit',18,2)->default(0);
            $t->decimal('total_credit',18,2)->default(0);
            $t->date('value_date')->nullable();
            $t->text('memo')->nullable();
            $t->timestampsTz();
            $t->index(['branch_id','posted_at']);
        });

        // Lines (details)
        Schema::create('gl_lines', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->bigInteger('journal_id');
            $t->bigInteger('account_id');
            $t->decimal('debit',18,2)->default(0);
            $t->decimal('credit',18,2)->default(0);
            $t->integer('line_no');
            $t->text('narration')->nullable();
            $t->index('journal_id');
            $t->index('account_id');
            // Optional check constraints (Postgres only)
            $t->check('debit >= 0');
            $t->check('credit >= 0');
        });

        // Idempotency keys for external callbacks / posting
        Schema::create('idempotency_keys', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->bigInteger('journal_id')->nullable();
            $t->timestampTz('created_at')->useCurrent();
            $t->index('journal_id');
        });

        // FKs
        Schema::table('gl_journals', function (Blueprint $t) {
            $t->foreign('branch_id')->references('id')->on('branches');
            $t->foreign('created_by')->references('id')->on('users');
        });
        Schema::table('gl_lines', function (Blueprint $t) {
            $t->foreign('journal_id')->references('id')->on('gl_journals')->cascadeOnDelete();
            $t->foreign('account_id')->references('id')->on('gl_accounts');
        });
        Schema::table('idempotency_keys', function (Blueprint $t) {
            $t->foreign('journal_id')->references('id')->on('gl_journals')->nullOnDelete();
        });

        // Optional: DB constraint to ensure journal totals match line sums (Postgres)
        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION validate_gl_balance() RETURNS trigger AS $$
BEGIN
  PERFORM 1 FROM gl_journals j
  WHERE j.id = NEW.id
    AND (j.total_debit, j.total_credit) = (
      SELECT COALESCE(SUM(debit),0), COALESCE(SUM(credit),0) FROM gl_lines WHERE journal_id = j.id
    );
  IF NOT FOUND THEN
    RAISE EXCEPTION 'GL journal % not balanced with its lines', NEW.id;
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_validate_gl_balance ON gl_journals;
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
