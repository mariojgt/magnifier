<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create a polymorphic table beteween the model and the media
        Schema::create('model_media', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->foreignId('media_id')->constrained('media');
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
        Schema::dropIfExists('model_media');
    }
};
