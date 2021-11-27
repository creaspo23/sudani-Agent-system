<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuppliersTable extends Migration
{
    /**
    * Schema table name to migrate
    * @var string
    */
    public $tableName = 'suppliers';
    
    /**
    * Run the migrations.
    * @table suppliers
    *
    * @return void
    */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 145);
            $table->string('phone', 30);
            $table->unsignedInteger('account_id');
            
            $table->index(["account_id"], 'suppliers_accounts1_idx');
            $table->timestamps();
            
            
            $table->foreign('account_id', 'suppliers_accounts1_idx')
            ->references('id')->on('accounts')
            ->onDelete('no action')
            ->onUpdate('no action');
        });
    }
    
    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}