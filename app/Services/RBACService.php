<?php
namespace App\Services;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;


class RBACService
{
public static function rebuild(?string $userId = null): void
{
$users = DB::table('users')->when($userId, fn($q)=>$q->where('id',$userId))->pluck('id');
foreach($users as $uid){
$assignments = DB::table('user_role_assignments')
->where('user_id',$uid)
->where(function($q){
$q->whereNull('starts_at')->orWhere('starts_at','<=',now());
})
->where(function($q){
$q->whereNull('ends_at')->orWhere('ends_at','>=',now());
})
->get();


$roleIds = $assignments->pluck('role_id');
$caps = DB::table('role_capabilities')->whereIn('role_id',$roleIds)
->join('capabilities','capabilities.id','=','role_capabilities.capability_id')
->where('capabilities.is_active',true)
->pluck('capabilities.key')->unique()->values();


$branchAll = $assignments->contains(fn($a)=>$a->scope_all_branches);
$branchIds = $assignments->flatMap(function($a){
$arr = $a->branch_ids ? json_decode(json_encode($a->branch_ids), true) : [];
return $arr ?: [];
})->unique()->values();


DB::table('user_policies')->upsert([
'user_id'=>$uid,
'capabilities'=>json_encode($caps),
'branch_scope_all'=>$branchAll,
'branch_ids'=>json_encode($branchIds),
'updated_at'=>now(),
], ['user_id']);


Cache::forget("user_policies:{$uid}");
}
}
}