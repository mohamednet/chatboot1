<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('incoming_messages', function (Blueprint $table) {
            $table->id();
            $table->string('facebook_id');
            $table->text('message');
            $table->enum('status', ['unprocessed', 'processing', 'processed'])->default('unprocessed');
            $table->timestamps();

            $table->index(['facebook_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('incoming_messages');
    }
};
