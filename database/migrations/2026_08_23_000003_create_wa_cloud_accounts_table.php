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
        return config('laravel-whatsapp.database.prefix', '').'wa_cloud_accounts';
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->create($this->table(), function (Blueprint $table) {
            $table->string('id')->primary(); // user-chosen slug, e.g. "acc-main"
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('label');
            $table->string('phone_number_id');
            $table->string('business_account_id')->nullable();
            $table->text('access_token'); // encrypted cast
            $table->text('app_secret'); // encrypted cast
            $table->string('verify_token')->nullable();
            $table->string('api_version')->default('v21.0');
            $table->string('base_host')->default('graph.facebook.com');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->dropIfExists($this->table());
    }
};
