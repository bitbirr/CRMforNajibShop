<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use App\Events\{ReceiptPosted,StockChanged};


class POSService
{
public static function postReceipt(int $receiptId, string $branchId, string $key): void
{
DB::transaction(function() use($receiptId,$branchId,$key){
$r = DB::table('receipts')->where('id',$receiptId)->lockForUpdate()->first();
if(!$r || $r->status!=='DRAFT'){ throw new \RuntimeException('Invalid receipt'); }
$lines = DB::table('receipt_lines')->where('receipt_id',$receiptId)->get();
// GL: debit cash, credit sales
$cash = self::gl('CASH_BOX'); $sales = self::gl('SALES');
$jid = GLService::post('SALE',null,$branchId,null,'SALE-'.$receiptId,'POS Sale',[ [ $cash,$r->paid_amount,0,'Cash' ], [ $sales,0,$r->total_amount,'Revenue' ] ],$key);


// stock movements
foreach($lines as $ln){
DB::table('inventory_items')->where(['product_id'=>$ln->product_id,'branch_id'=>$branchId])
->update([ 'quantity'=>DB::raw('quantity - '.$ln->qty) ]);
DB::table('stock_movements')->insert([
'product_id'=>$ln->product_id,'branch_id'=>$branchId,'qty'=>-$ln->qty,'type'=>'SALE',
'ref_table'=>'receipts','ref_id'=>$receiptId,'created_by'=>auth()->id(),
]);
}
DB::table('receipts')->where('id',$receiptId)->update(['status'=>'POSTED','posted_at'=>now()]);
event(new ReceiptPosted(['receipt_id'=>$receiptId,'journal_id'=>$jid]));
event(new StockChanged(['branch_id'=>$branchId]));
});
}
private static function gl(string $code): int { return (int) (DB::table('gl_accounts')->where('code',$code)->value('id') ?? 0); }
}