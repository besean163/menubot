<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('days_menu', 'dishes');
        Schema::table('dishes', function (Blueprint $table) {
            $table
                ->dropColumn('list')
                ->after('date', function (Blueprint $table) {
                    $table->integer('categoryId');
                    $table->string('sourceId');
                    $table->string('name');
                    $table->float('weight');
                    $table->string('weightDimension');
                    $table->float('price');
                    $table->float('calories');
                    $table->json('ingredients');
                });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn('categoryId');
            $table->dropColumn('sourceId');
            $table->dropColumn('name');
            $table->dropColumn('weight');
            $table->dropColumn('weightDimension');
            $table->dropColumn('price');
            $table->dropColumn('calories');
            $table->dropColumn('ingredients');
            $table->json('list');
        });
        Schema::rename('dishes', 'days_menu');
    }
};
