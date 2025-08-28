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
$t->timestampsTz();
$t->foreign('branch_id')->references('id')->on('branches');
});


Schema::create('terminal_sessions', function (Blueprint $t) {
$t->bigIncrements('id');
$t->uuid('terminal_id');
$t->uuid('opened_by');
$t->uuid('closed_by')->nullable();
$t->enum('status',[ 'OPEN','CLOSED' ])->default('OPEN');
$t->timestampTz('opened_at')->useCurrent();
$t->timestampTz('closed_at')->nullable();
$t->text('notes')->nullable();
$t->index(['terminal_id','status']);
$t->foreign('terminal_id')->references('id')->on('terminals')->cascadeOnDelete();
$t->foreign('opened_by')->references('id')->on('users');
$t->foreign('closed_by')->references('id')->on('users')->nullOnDelete();
});


// One OPEN per terminal (partial unique index)
DB::statement("CREATE UNIQUE INDEX term_one_open ON terminal_sessions(terminal_id) WHERE status='OPEN'");
}
public function down(): void
{
DB::statement("DROP INDEX IF EXISTS term_one_open");
Schema::dropIfExists('terminal_sessions');
Schema::dropIfExists('terminals');
}
};