<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meta_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->text('access_token')->comment('Page Access Token (criptografado)');
            $table->string('meta_user_id')->nullable()->comment('ID do usuário Meta que autorizou');
            $table->string('page_id')->nullable();
            $table->string('page_name')->nullable();
            $table->string('instagram_account_id')->nullable();
            $table->string('instagram_username')->nullable();
            $table->timestamp('expires_at')->nullable()->comment('Tokens de página não expiram; user tokens duram 60 dias');
            $table->timestamps();

            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meta_tokens');
    }
};
