<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration {
public function up(): void
{
Schema::create('branches', function (Blueprint $t) {
$t->uuid('id')->primary();
$t->string('name');
$t->string('location')->nullable();
$t->boolean('is_main_branch')->default(false);
$t->timestamps();
});


Schema::table('users', function (Blueprint $t) {
$t->uuid('branch_id')->nullable()->after('password');
$t->boolean('is_active')->default(true);
$t->foreign('branch_id')->references('id')->on('branches');
});


Schema::create('roles', function (Blueprint $t) {
$t->uuid('id')->primary();
$t->string('name')->unique();
$t->string('description')->nullable();
$t->boolean('is_active')->default(true);
$t->timestamps();
});


Schema::create('capabilities', function (Blueprint $t) {
$t->uuid('id')->primary();
$t->string('key')->unique();
$t->string('category');
$t->string('description')->nullable();
$t->boolean('is_active')->default(true);
$t->timestamps();
});


Schema::create('role_capabilities', function (Blueprint $t) {
$t->uuid('role_id');
$t->uuid('capability_id');
$t->primary(['role_id','capability_id']);
$t->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
$t->foreign('capability_id')->references('id')->on('capabilities')->cascadeOnDelete();
});


Schema::create('user_role_assignments', function (Blueprint $t) {
$t->uuid('id')->primary();
$t->uuid('user_id');
$t->uuid('role_id');
});
}
};