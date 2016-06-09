<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddBaseFields extends Migration
{
    public function fieldTableFields(Blueprint $table)
    {
        $table->increments('id');
        $table->string('name');
        $table->integer('weight');

        $table->timestamps();

        $table->unsignedInteger('revision_id');
        $table->foreign('revision_id')->references('id')->on('revisions');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'field_integer',
            function (Blueprint $table) {
                $this->fieldTableFields($table);
                $table->integer('value');
            }
        );

        Schema::create(
            'field_double',
            function (Blueprint $table) {
                $this->fieldTableFields($table);
                $table->double('value');
            }
        );

        Schema::create(
            'field_boolean',
            function (Blueprint $table) {
                $this->fieldTableFields($table);
                $table->boolean('value');
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
        Schema::drop('field_integer');
        Schema::drop('field_double');
        Schema::drop('field_boolean');
    }
}
