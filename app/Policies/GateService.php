<?php
namespace App\Policies;


use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


class GateService
{
public static function hasCapability(User $user, string $capability, ?string $branchId = null): bool
{
$cache = Cache::remember("user_policies:{$user->id}", 300, function() use ($user){
return DB::table('user_policies')->where('user_id',$user->id)->first();
});
if(!$cache){ return false; }
$caps = collect(json_decode($cache->capabilities ?? '[]', true));
$branchAll = (bool)($cache->branch_scope_all ?? false);
$branchIds = collect(json_decode($cache->branch_ids ?? '[]', true));
if(!$caps->contains($capability)) return false;
if($branchAll) return true;
if($branchId===null) return false;
return $branchIds->contains($branchId);
}
}