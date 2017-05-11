<?php

use App\Entity\MDBlog;
use App\Entity\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMdBlogTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::dropIfExists(MDBlog::$TABLE);
    // 创建表
    Schema::create(MDBlog::$TABLE, function (Blueprint $table) {
      $table->bigIncrements("id");
      $table->unsignedBigInteger("user_id");
      $table->text("blog");
      $table->timestamps();
      $table->softDeletes();
      $table->foreign('user_id')->references('id')->on(User::$TABLE);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    // 创建表
    Schema::drop(MDBlog::$TABLE);
  }
}
