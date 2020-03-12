<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimpleModelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('simple_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->boolean('publish')->default(false);
            $table->string('something')->nullable();
            $table->timestamps();
        });

        Schema::create('belongs_to_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('has_one_model_id')->nullable();
            $table->timestamps();
        });

        Schema::create('has_one_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });

        Schema::create('many_to_many', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('has_one_model_id')->nullable();
            $table->unsignedBigInteger('belongs_to_model_id')->nullable();
            $table->string('pivot')->nullable();
            $table->timestamps();
        });

        Schema::create('morph_to_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('morph_to_relation_id');
            $table->string('morph_to_relation_type');
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
        Schema::dropIfExists('simple_models');
        Schema::dropIfExists('belongs_to_models');
        Schema::dropIfExists('has_one_models');
        Schema::dropIfExists('morph_to_models');
        Schema::dropIfExists('many_to_many');
    }
}
