<?php
namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Policies\GateService;


class EnsureHasCapability
{
public function handle(Request $request, Closure $next, string $capability, string $branchParam = 'branch_id')
{
$user = $request->user();
$branchId = $request->input($branchParam) ?? $request->route($branchParam);
if(!$user || !GateService::hasCapability($user, $capability, $branchId)){
throw new AccessDeniedHttpException('You are not authorized for this action / branch.');
}
return $next($request);
}
}