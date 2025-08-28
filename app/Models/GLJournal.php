<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;


class GLJournal extends Model
{
protected $fillable = ['tx_type','channel','branch_id','agent_id','ref_no','description','posted_at','created_by','reversal_of','value_date','memo'];
public function lines(){ return $this->hasMany(GLLine::class,'journal_id'); }
}