<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CraeteTableTagBlog extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('t_tag_blog', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->bigInteger("tag_id")->unsigned()->comment("标签 id");
      $table->bigInteger("blog_id")->unsigned()->comment("博客 id");
      $table->foreign("tag_id")->references("id")->on("t_tag");
      $table->foreign("blog_id")->references("id")->on("t_md_blog");
      $table->unique(["tag_id", "blog_id"], "tag_blog");
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('t_tag_blog');
  }
}
