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