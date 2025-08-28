<?php
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