<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('media')) {
            Schema::create('media', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name');
                $table->string('extension')->default('jpg');
                $table->unsignedBigInteger('media_folder_id')->index();
                $table->string('title')->nullable();
                $table->string('alt')->nullable();
                $table->string('media_size')->nullable();
                $table->string('caption')->nullable();
                $table->string('description')->nullable();
                $table->integer('height')->nullable();
                $table->integer('width')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
}
