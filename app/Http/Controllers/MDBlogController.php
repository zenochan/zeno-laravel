<?php

namespace App\Http\Controllers;

use App\Entity\MDBlog;
use App\Entity\Result;
use Illuminate\Http\Request;

class MDBlogController extends Controller
{
  private static $pwd = "zenochan";
  private $tag;

  public function viewBlog()
  {
    return view("blog");
  }


  // 获取日志列表
  public function getMDBlog(Request $request)
  {
    $before = $request->query("before");
    $keyword = $request->query("keyword");
    $tag = $request->query("tag");


    $build = MDBlog::where("user_id", "=", 1);
    if ($before && $before != "null") {
      $build->where("created_at", "<", $before);
    }
    if ($keyword && $keyword != "null") {
      $build->where("blog", "like", "%" . $keyword . "%");
    }

    if ($tag && $tag != "null") {
      $this->tag = $tag;
      $build->whereHas("tags", function ($query) {
        $query->where("tag", "=", $this->tag);
      });
    }
    $build->with("tags");


    $build->orderBy("created_at", "desc");
    $build->limit(10);
    $blogs = $build->get();
    \Log::alert($tag);
    $r = Result::success($blogs);
    return $r;
  }

  // 新建MD日志
  public function postMDBlog(Request $request)
  {
    $this->validate($request, ["blog" => "required|min:8"]);

    if ($request->input("password") != MDBlogController::$pwd) {
      return Result::error(1, "口令错误");
    }

    $blog = new MDBlog();
    $blog->blog = $request->input("blog");
    $blog->user_id = 1;
    $blog->saveOrFail();

    return Result::successSingle($blog);
  }

  public function getMDBlogById($id)
  {
    $blog = MDBlog::find($id);
    if ($blog) {
      return Result::successSingle($blog);
    } else {
      return Result::error(2, "日志不存在");
    }
  }

  // 删除日志
  public function deleteMDBlog(Request $request, $id, $pwd)
  {
    if ($pwd != MDBlogController::$pwd) {
      return Result::error(1, "口令错误");
    }

    MDBlog::destroy($id);
    return Result::success(null);
  }

  // 更新日志
  public function patchBlog(Request $request, $id)
  {
    $this->validate($request, ["blog" => "required|min:8"]);

    if ($request->input("password") != MDBlogController::$pwd) {
      return Result::error(1, "口令错误");
    }

    $blog = MDBlog::find($id);
    $blog->blog = $request->input("blog");
    $blog->saveOrFail();

    return Result::successSingle($blog);
  }
}
