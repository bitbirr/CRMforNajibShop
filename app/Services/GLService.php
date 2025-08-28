<?php
namespace App\Services;


use Illuminate\Support\Facades\DB;


class GLService
{
/** @return int journal id */
public static function post(string $txType, ?string $channel, string $branchId, ?int $agentId, string $refNo, string $description, array $lines, ?string $idempotencyKey=null): int
{
return DB::transaction(function() use ($txType,$channel,$branchId,$agentId,$refNo,$description,$lines,$idempotencyKey){
if($idempotencyKey){
$existing = DB::table('idempotency_keys')->where('key',$idempotencyKey)->first();
if($existing && $existing->journal_id){ return (int)$existing->journal_id; }
}
$jid = DB::table('gl_journals')->insertGetId([
'tx_type'=>$txType,
'channel'=>$channel,
'branch_id'=>$branchId,
'agent_id'=>$agentId,
'ref_no'=>$refNo,
'description'=>$description,
'posted_at'=>now(),
'created_by'=>auth()->id(),
'value_date'=>now()->toDateString(),
'created_at'=>now(),
'updated_at'=>now(),
]);
$i=1; $sumD=0; $sumC=0;
foreach($lines as $ln){
[$acc,$debit,$credit,$narr] = $ln; $sumD+=$debit; $sumC+=$credit;
DB::table('gl_lines')->insert([
'journal_id'=>$jid,'account_id'=>$acc,'debit'=>$debit,'credit'=>$credit,'line_no'=>$i++,'narration'=>$narr
]);
}
if($sumD != $sumC){ throw new \RuntimeException('GL not balanced'); }
if($idempotencyKey){ DB::table('idempotency_keys')->insert(['key'=>$idempotencyKey,'journal_id'=>$jid]); }
return $jid;
});
}
}