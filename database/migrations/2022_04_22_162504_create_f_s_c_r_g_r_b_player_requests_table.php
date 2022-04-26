<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFSCRGRBPlayerRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('f_s_c_r_g_r_b_player_requests', function (Blueprint $table) {
            $table->id();
            $table->string('players_json')->nullable();
            $table->string('PlayerRequestKey');
            $table->foreignId('server_id')->constrained();
            $table->integer('RequestValidFor_Seconds');
            $table->integer('used')->default(0);
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
        Schema::dropIfExists('f_s_c_r_g_r_b_player_requests');
    }
}
