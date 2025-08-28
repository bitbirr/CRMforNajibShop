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
$t->decimal('total_amount',18,2);
$t->decimal('paid_amount',18,2)->default(0);
$t->decimal('change_amount',18,2)->default(0);
$t->enum('status',[ 'DRAFT','POSTED','VOIDED','REFUNDED' ])->default('DRAFT');
$t->timestampsTz();
$t->foreign('branch_id')->references('id')->on('branches');
$t->foreign('user_id')->references('id')->on('users');
});


Schema::create('receipt_lines', function (Blueprint $t) {
$t->bigIncrements('id');
$t->unsignedBigInteger('receipt_id');
$t->uuid('product_id');
$t->decimal('qty',18,2);
$t->decimal('price',18,2);
$t->decimal('total',18,2);
$t->index('receipt_id');
$t->foreign('receipt_id')->references('id')->on('receipts')->cascadeOnDelete();
$t->foreign('product_id')->references('id')->on('products');
});
}
public function down(): void
{
Schema::dropIfExists('receipt_lines');
Schema::dropIfExists('receipts');
}
};