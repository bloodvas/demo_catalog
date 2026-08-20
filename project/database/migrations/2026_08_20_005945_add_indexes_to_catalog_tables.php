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
        Schema::table('groups', function (Blueprint $table) {
            $table->index('id_parent');
            $table->index('name');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('id_group');
            $table->index('name');
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->index('id_product');
            $table->index('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropIndex(['id_parent']);
            $table->dropIndex(['name']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['id_group']);
            $table->dropIndex(['name']);
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->dropIndex(['id_product']);
            $table->dropIndex(['price']);
        });
    }
};
