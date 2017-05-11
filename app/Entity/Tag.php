<?php

namespace App\Entity;

class Tag extends \Eloquent
{
  //
  protected $table = "t_tag";
  public $timestamps = false;

  public function blogs()
  {
    return $this->belongsToMany("App\Entity\MDBlog", 't_tag_blog', 'tag_id', 'blog_id');
  }
}
