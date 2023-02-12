<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArtikelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('artikels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('slug');
            $table->string('creator')->nullable();
            $table->string('category')->nullable();
            $table->string('image')->nullable();
            $table->string('source')->nullable();
            $table->text('tags')->nullable();
            $table->text('desc_short')->nullable();
            $table->text('desc_long')->nullable();
            $table->text('notes')->nullable();
            $table->integer('count_like')->default(0);
            $table->integer('count_view')->default(0);
            $table->integer('count_share_fb')->default(0);
            $table->integer('count_share_tw')->default(0);
            $table->integer('count_share_wa')->default(0);
            $table->integer('count_share_tl')->default(0);
            $table->integer('count_share_link')->default(0);
            $table->boolean('is_active')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
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
        Schema::dropIfExists('artikels');
    }
}
