<?php
namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Services\RBACService;


class RbacRebuild extends Command
{
protected $signature = 'rbac:rebuild {userId?}';
protected $description = 'Rebuild cached user policies';
public function handle(): int
{
RBACService::rebuild($this->argument('userId'));
$this->info('RBAC cache rebuilt');
return self::SUCCESS;
}
}