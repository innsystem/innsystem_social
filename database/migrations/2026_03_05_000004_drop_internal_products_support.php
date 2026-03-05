<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('social_posts', 'product_id')) {
            Schema::table('social_posts', function (Blueprint $table) {
                $table->dropConstrainedForeignId('product_id');
            });
        }

        Schema::dropIfExists('products');
    }

    public function down(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('image_path')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::table('social_posts', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('tenant_id')->constrained('products')->nullOnDelete();
        });
    }
};
