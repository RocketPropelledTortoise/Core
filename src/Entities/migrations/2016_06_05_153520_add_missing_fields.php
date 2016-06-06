<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddMissingFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'contents',
            function (Blueprint $table) {
                $table->string('type')->nullable();
                $table->boolean('published')->default(true);
            }
        );

        Schema::table(
            'revisions',
            function (Blueprint $table) {
                $table->boolean('published')->default(true);
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
        Schema::table(
            'contents',
            function (Blueprint $table) {
                $table->removeColumn('type');
                $table->removeColumn('published');
            }
        );

        Schema::table(
            'revisions',
            function (Blueprint $table) {
                $table->removeColumn('published');
            }
        );
    }
}
