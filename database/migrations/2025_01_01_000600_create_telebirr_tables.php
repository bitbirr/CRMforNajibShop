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