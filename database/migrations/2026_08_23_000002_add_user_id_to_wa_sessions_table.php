<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function getConnection(): ?string
    {
        return config('laravel-whatsapp.database.connection');
    }

    protected function table(): string
    {
        return config('laravel-whatsapp.database.prefix', '').'wa_sessions';
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->table($this->table(), function (Blueprint $table) {
            // Nullable: pre-existing sessions created before this column
            // existed have no owner until claimed (see SessionsController::claim()).
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->table($this->table(), function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
