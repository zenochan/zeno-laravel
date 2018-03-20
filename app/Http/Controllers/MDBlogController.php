<?php

namespace App\Http\Controllers;

use App\Entity\MDBlog;
use App\Entity\Result;
use Illuminate\Http\Request;
use function MongoDB\BSON\toJSON;

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
    }else{
      $build->whereDoesntHave("tags", function ($query) {
        $query->where("tag", "=", "Crash");
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

  // App 异常上报 + Crash tag
  public function createBlogFromCrash(Request $request)
  {
    $param = json_decode($request->getContent());
    $blogContent =
      "#### 应用\n"
      . "> \n"
      . "__名称__:$param->appName($param->packageName)  \n"
      . "__版本__:$param->versionName(build $param->versionCode)\n\n"

      . "#### 设备\n"
      . "> \n"
      . "__IMEI__: " . @$param->imei . "  \n"
      . "__手机__: " . @$param->phoneNumber . "  \n"
      . "__厂商__: $param->manufacturer  \n"
      . "__型号__: $param->mode  \n"
      . "__版本__: $param->version($param->sdkInt)\n\n"

      . "#### 异常\n"
      . "> \n"
      . "__类型__: $param->type  \n"
      . "__信息__: $param->message  \n"
      . "__时间__: $param->time  \n"
      . "```\n$param->stack\n\n```"

      . "#### 用户\n"
      . "```json\n"
      . json_encode(@$param->account, 384)
      . "\n```\n\n";


    $blog = new MDBlog();
    $blog->blog = $blogContent;
    $blog->user_id = 1;
    $blog->saveOrFail();

    $tagCtr = new TagController();
    $tagCtr->addTag($blog->id, "Crash");

    return $blog;
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
