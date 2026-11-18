<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('image_url', 500)->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['category_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
