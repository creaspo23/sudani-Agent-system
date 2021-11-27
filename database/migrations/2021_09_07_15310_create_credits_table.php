<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditsTable extends Migration
{
    /**
    * Schema table name to migrate
    * @var string
    */
    public $tableName = 'credits';
    
    /**
    * Run the migrations.
    * @table customers
    *
    * @return void
    */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            
            $table->increments('id');
            $table->string('month');
            $table->date('date');
            $table->decimal('bonus');
            $table->longtext('note')->nullable();
            $table->timestamps();

        });
        Schema::enableForeignKeyConstraints();
        \DB::statement('ALTER TABLE credits  AUTO_INCREMENT = 100;');
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