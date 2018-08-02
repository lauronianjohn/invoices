<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForeignVersion2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::enableForeignKeyConstraints();
        Schema::table('invoices', function($table)
        {
            $table->foreign('form_name_id')
                    ->references('id')->on('formnames')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
        });
        Schema::table('invoice_input', function($table)
        {
            $table->foreign('form_name_id')
                    ->references('id')->on('formnames')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
        });
        Schema::table('form_data', function($table)
        {
            $table->foreign('formname_id')
                  ->references('id')->on('formnames')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('foreign_keys');
    }
}