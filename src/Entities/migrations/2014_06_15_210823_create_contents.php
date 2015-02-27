<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateContents extends Migration
{

    public function fieldTableFields(Blueprint $table)
    {
        $table->increments('id');
        $table->string('name');
        $table->integer('weight');

        $table->timestamps();

        $table->integer('revision_id');
        $table->foreign('revision_id')->references('id')->on('languages');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'contents',
            function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
            }
        );

        Schema::create(
            'revisions',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('language_id');
                $table->integer('content_id');
                $table->timestamps();

                $table->foreign('language_id')->references('id')->on('languages');
                $table->foreign('content_id')->references('id')->on('contents');
            }
        );

        Schema::create(
            'field_string',
            function (Blueprint $table) {
                $this->fieldTableFields($table);
                $table->string('value');
            }
        );

        Schema::create(
            'field_text',
            function (Blueprint $table) {
                $this->fieldTableFields($table);
                $table->text('value');
            }
        );

        Schema::create(
            'field_date',
            function (Blueprint $table) {
                $this->fieldTableFields($table);
                $table->date('value');
            }
        );

        Schema::create(
            'field_datetime',
            function (Blueprint $table) {
                $this->fieldTableFields($table);
                $table->datetime('value');
            }
        );

        Schema::create(
            'field_entity',
            function (Blueprint $table) {
                $this->fieldTableFields($table);
                $table->integer('value');
                $table->foreign('value')->references('id')->on('contents');
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
        Schema::drop('field_text');
        Schema::drop('field_date');
        Schema::drop('field_datetime');
        Schema::drop('field_entity');
        Schema::drop('revisions');
        Schema::drop('contents');
    }
}
