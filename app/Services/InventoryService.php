<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;


class InventoryService
{
public static function receiveStock(string $branchId, string $productId, float $qty, ?string $supplier, ?string $memo): void
{
DB::transaction(function() use($branchId,$productId,$qty,$memo){
DB::table('inventory_items')->updateOrInsert(
['product_id'=>$productId,'branch_id'=>$branchId],
DB::raw("quantity = COALESCE(quantity,0)+$qty, updated_at = NOW()")
);
DB::table('stock_movements')->insert([
'product_id'=>$productId,'branch_id'=>$branchId,'qty'=>$qty,'type'=>'RECEIVE',
'ref_table'=>'manual','ref_id'=>null,'created_by'=>auth()->id(),
]);
});
}
}