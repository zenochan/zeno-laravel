<?php

namespace App\Entity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MDBlog extends \Eloquent
{
  use SoftDeletes;

  static public $TABLE = "t_md_blog";
  protected $table = "t_md_blog";
  protected $dates = ['deleted_at'];

  public function tags()
  {
    return $this->belongsToMany("App\Entity\Tag", 't_tag_blog', 'blog_id', 'tag_id');
  }
}
