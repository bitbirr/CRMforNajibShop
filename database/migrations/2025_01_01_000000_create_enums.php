<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


return new class extends Migration {
public function up(): void
{
DB::statement("CREATE TYPE tx_type AS ENUM ('ISSUE','REPAY','LOAN','TOPUP','SALE','TRANSFER','ADJUST')");
DB::statement("CREATE TYPE channel AS ENUM ('CBE','EBIRR','COOPAY','ABASINIYA','AWASH','DASHEN','TELEBIRR','ESAHAL','H_CASH')");
DB::statement("CREATE TYPE receipt_status AS ENUM ('DRAFT','POSTED','VOIDED','REFUNDED')");
DB::statement("CREATE TYPE term_status AS ENUM ('OPEN','CLOSED')");
DB::statement("CREATE TYPE transfer_status AS ENUM ('DRAFT','SENT','RECEIVED','CANCELLED')");
}
public function down(): void
{
DB::statement("DROP TYPE IF EXISTS transfer_status");
DB::statement("DROP TYPE IF EXISTS term_status");
DB::statement("DROP TYPE IF EXISTS receipt_status");
DB::statement("DROP TYPE IF EXISTS channel");
DB::statement("DROP TYPE IF EXISTS tx_type");
}
};