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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'telLogin',
                'login',
                'password',
                'type'
            ]);
            $table->after('telId', function (Blueprint $table) {
                $table->string('firstName');
                $table->string('lastName');
            });
            $table->renameColumn('telId', 'telegramId');
            $table->renameColumn('name', 'telegramName');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('firstName');
            $table->dropColumn('lastName');
            $table->renameColumn('telegramName', 'name');
            $table->renameColumn('telegramId', 'telId');
            $table->string('password');
            $table->string('telLogin');
            $table->string('type');
            $table->string('login');
        });
    }
};
