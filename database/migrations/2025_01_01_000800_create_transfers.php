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
$t->timestampTz('created_at')->useCurrent();
$t->index(['from_branch_id','to_branch_id']);
$t->foreign('from_branch_id')->references('id')->on('branches');
$t->foreign('to_branch_id')->references('id')->on('branches');
$t->foreign('created_by')->references('id')->on('users');
});


Schema::create('transfer_lines', function (Blueprint $t) {
$t->bigIncrements('id');
$t->uuid('header_id');
$t->uuid('product_id');
$t->decimal('qty',18,2);
$t->index('header_id');
$t->foreign('header_id')->references('id')->on('transfer_headers')->cascadeOnDelete();
$t->foreign('product_id')->references('id')->on('products');
});
}
public function down(): void
{
Schema::dropIfExists('transfer_lines');
Schema::dropIfExists('transfer_headers');
}
};