<?php
// Example: debit bank, credit distributor float
$jid = GLService::post('TOPUP',$channel,$branchId,null,'TOPUP-'.uniqid(),$description,[
[ $bankGlId, $amount, 0, 'Bank debit' ],
[ self::gl('TELEBIRR_FLOAT'), 0, $amount, 'Float top-up' ],
], $key);
event(new TelebirrTransactionPosted(['tx_type'=>'TOPUP','branch_id'=>$branchId,'amount'=>$amount,'journal_id'=>$jid]));
DB::table('telebirr_transactions')->insert([
'tx_id'=>'TB'.uniqid(),'tx_type'=>'TOPUP','channel'=>$channel,'branch_id'=>$branchId,'agent_id'=>null,
'amount'=>$amount,'journal_id'=>$jid,'description'=>$description
]);
return $jid;
}


private static function post(string $type, string $agentShortCode, ?string $channel, float $amount, string $branchId, string $description, string $key): int
{
$agent = DB::table('telebirr_agents')->where('short_code',$agentShortCode)->first();
if(!$agent){ throw new \InvalidArgumentException('Agent not found'); }


// Map example accounts (replace with real chart of accounts)
$distFloat = self::gl('TELEBIRR_FLOAT');
$agentAR = self::gl('AGENT_AR');
$cashBox = self::gl('CASH_BOX');


$ref = $type.'-'.$agent->id.'-'.uniqid();
$lines = match($type){
'ISSUE' => [ [ $agentAR, $amount, 0, 'Agent receivable' ], [ $distFloat, 0, $amount, 'Reduce float' ] ],
'REPAY' => [ [ $distFloat, $amount, 0, 'Float restored' ], [ $agentAR, 0, $amount, 'Reduce AR' ] ],
'LOAN' => [ [ $agentAR, $amount, 0, 'Agent loan' ], [ $cashBox, 0, $amount, 'Cash out' ] ],
default => throw new \RuntimeException('Unsupported')
};


$jid = GLService::post($type,$channel,$branchId,$agent->id,$ref,$description,$lines,$key);


DB::table('telebirr_transactions')->insert([
'tx_id'=>$ref,'tx_type'=>$type,'channel'=>$channel,'branch_id'=>$branchId,
'agent_id'=>$agent->id,'amount'=>$amount,'journal_id'=>$jid,'description'=>$description
]);
event(new TelebirrTransactionPosted(['tx_type'=>$type,'branch_id'=>$branchId,'amount'=>$amount,'journal_id'=>$jid,'agent_id'=>$agent->id]));
return $jid;
}


private static function gl(string $code): int
{
return (int) (DB::table('gl_accounts')->where('code',$code)->value('id') ?? 0);
}


private static function authorize(string $capability, string $branchId): void
{
$u = auth()->user();
if(!$u || !\App\Policies\GateService::hasCapability($u, $capability, $branchId)){
abort(403,'Not allowed');
}
}
}