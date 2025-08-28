<?php
namespace App\Traits;


use App\Models\AuditLog;


trait Auditable
{
public static function bootAuditable()
{
static::created(function($model){ $model->writeAudit('created'); });
static::updated(function($model){ $model->writeAudit('updated'); });
static::deleted(function($model){ $model->writeAudit('deleted'); });
}


protected function writeAudit(string $action): void
{
AuditLog::create([
'user_id' => auth()->id(),
'action' => $action,
'entity_type' => static::class,
'entity_id' => (string)($this->getKey()),
'details' => request()->all(),
'ip_address' => request()->ip(),
'user_agent' => request()->userAgent(),
]);
}
}