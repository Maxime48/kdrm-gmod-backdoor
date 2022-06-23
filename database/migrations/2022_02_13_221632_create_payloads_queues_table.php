<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayloadsQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payloads_queues', function (Blueprint $table) {
            $table->id();

            //server assigned to payload
            $table->foreignId('server_id')->constrained(); //queue value

            $table->string('content','10000');
            $table->string('description','500');

            //Response after payload and execution status
            $table->string('response','1000')->nullable(); //queue value
            $table->integer('execution')->default(0); //queue value

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payloads_queues');
    }
}
