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