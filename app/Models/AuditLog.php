<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AuditLog extends Model
{
public $timestamps = false;
protected $fillable = ['user_id','action','entity_type','entity_id','details','ip_address','user_agent'];
protected $casts = ['details'=>'array'];
}