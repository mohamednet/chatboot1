<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('client_devices', function (Blueprint $table) {
            $table->id();
            $table->string('client_facebook_id');
            $table->string('device_type'); // android, ios, smart_tv, roku, etc.
            $table->string('player_type')->nullable(); // 8kvip, iboplayer, tivimate, etc.
            $table->string('login')->nullable();
            $table->string('password')->nullable();
            $table->dateTime('trial_started_at')->nullable();
            $table->dateTime('trial_ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('client_facebook_id')
                  ->references('facebook_id')
                  ->on('clients')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('client_devices');
    }
};
