<?php

namespace App\Http\Controllers;

use App\Entity\Tag;
use App\Entity\Result;

class TagController extends Controller
{
  public function addTag($blogId, $tag)
  {
    $tagObj = Tag::where("tag", "=", $tag)->first();
    if (!$tagObj) {
      $tagObj = new Tag();
      $tagObj->tag = $tag;
      $tagObj->saveOrFail();
      $tagObj->blogs()->attach($blogId);
    } else if (\DB::table("t_tag_blog")->where("tag_id", "=", $tagObj->id)->where("blog_id", "=", "$blogId")->count() == 0) {
      $tagObj->blogs()->attach($blogId);
    } else {
      return Result::error(2300, "已添加过该标签", [$tagObj]);
    }

    return Result::successSingle($tagObj);
  }

  function removeTag($blogId, $tagId)
  {
    return Result::successSingle(\DB::table("t_tag_blog")->where("tag_id", "=", $tagId)->where("blog_id", "=", "$blogId")->delete());
  }

  function tags()
  {
    $tags = Tag::leftJoin("t_tag_blog", "t_tag.id", "=", "t_tag_blog.tag_id")
      ->leftJoin("t_md_blog", "t_md_blog.id", "=", "t_tag_blog.blog_id")
      ->selectRaw("t_tag.*,count(t_tag_blog.id) as count")
      ->whereNull("t_md_blog.deleted_at")
      ->groupBy("t_tag.id")
      ->orderBy("count", 'desc')
      ->get()
      ->filter(function ($tag) {
        return $tag->count > 0;
      });

    return Result::success($tags);
  }
}
