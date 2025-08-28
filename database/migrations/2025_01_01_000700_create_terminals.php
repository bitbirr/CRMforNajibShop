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