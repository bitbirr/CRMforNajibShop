<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
public function up(): void
{
Schema::create('policy_rebuild_jobs', function (Blueprint $t) {
$t->id();
$t->uuid('user_id')->nullable();
$t->timestamps();
});
}
public function down(): void
{
Schema::dropIfExists('policy_rebuild_jobs');
}
};