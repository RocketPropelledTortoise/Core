<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'translations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('string_id');
                $table->unsignedInteger('language_id');
                $table->longText('text');
                $table->datetime('date_edition');

                $table->unique(['string_id', 'language_id']);
                $table->foreign('string_id')->references('id')->on('strings');
                $table->foreign('language_id')->references('id')->on('languages');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('translations');
    }
}
