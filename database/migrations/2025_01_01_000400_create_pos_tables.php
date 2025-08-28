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