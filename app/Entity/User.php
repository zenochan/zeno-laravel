<?php

namespace App\Entity;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends \Eloquent
{
  use SoftDeletes;

  public static $TABLE = "t_user";

  protected $table = "t_user";
  protected $dates = ['deleted_at'];
}
