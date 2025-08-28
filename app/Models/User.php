<?php
namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
use HasApiTokens, Notifiable;


protected $fillable = ['id','name','email','password','is_active','branch_id'];
protected $casts = [ 'id' => 'string', 'is_active' => 'boolean' ];
public $incrementing = false;
protected $keyType = 'string';


public function branch(){ return $this->belongsTo(Branch::class); }
public function assignments(){ return $this->hasMany(UserRoleAssignment::class); }
}