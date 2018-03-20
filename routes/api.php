<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

DB::listen(function ($sql) {
  Log::info($sql->sql);
});

Route::group(["middleware" => "api"], function () {
  Route::get("blogs", "MDBlogController@getMDBlog")->name("日志列表");
  Route::get("blogs/{id}", "MDBlogController@getMDBlogById")->name("日志详情");
  Route::post("blogs", "MDBlogController@postMDBlog")->name("新增日志");
  Route::patch("blogs/{id}", "MDBlogController@patchBlog")->name("更新日志");
  Route::delete("blogs/{id}/{pwd}", "MDBlogController@deleteMDBlog")->name("删除日志");
  Route::post("crash", "MDBlogController@createBlogFromCrash")->name("记录崩溃日志");

  Route::post("blogs/{blogId}/tags/{tag}", "TagController@addTag")->name("添加标签");
  Route::delete("blogs/{blogId}/tags/{tagId}", "TagController@removeTag")->name("删除标签");

  Route::get("tags", "TagController@tags")->name("所有标签");

  // 微信相关
  Route::group(["prefix" => "wechat"], function () {
    Route::post("js-sdk-sign", "WechatSignController@jssdkSign")->name("js sdk 签名");

    // 微信小程序
    Route::group(["prefix" => "weapp"], function () {
      Route::post("login", "WeappController@login")->name("小程序登录");
    });

    // 微信企业号
    Route::group(["prefix" => "ee"], function () {
      Route::get("callback", "WechatEnterpriseController@callback")->name("企业号回调");
      Route::post("callback", "WechatEnterpriseController@receiveMsg")->name("企业号消息");
    });
  });
});

