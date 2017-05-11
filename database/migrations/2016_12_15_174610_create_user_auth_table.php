<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserAuthTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('t_user_auth', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger("user_id");
      $table->text("identity_type");
      $table->text("identifier");
      $table->text("credential");
      $table->timestamps();
      $table->softDeletes();
      $table->foreign('user_id')->references('id')->on('t_user');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('t_user_auth');
  }
}
