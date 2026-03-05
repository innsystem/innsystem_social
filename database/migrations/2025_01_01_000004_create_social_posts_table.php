<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->enum('platform', ['instagram', 'facebook', 'both']);
            $table->string('meta_post_id')->nullable()->comment('ID do post retornado pela Meta API');
            $table->text('caption');
            $table->string('image_url');
            $table->enum('status', ['pending', 'published', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'platform', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_posts');
    }
};
