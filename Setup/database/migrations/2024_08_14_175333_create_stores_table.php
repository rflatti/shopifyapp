<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->bigIncrements('table_id');
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->bigInteger('id')->nullable();
            $table->string('myshopify_domain')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('zip')->nullable();
            $table->string('access_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
