<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use app\Http\Controllers\GuzzleClient;

Route::get('/', function () {
  preg_match_all("/满(\d+)减(\d+)元/", "满3000减100元,满4000减220元", $matchs);
  $r = [];

  for ($i = 0; $i < count($matchs[0]); $i++) {
    $r[$i] = ["mian" => $matchs[1][$i], "jian" => $matchs[2][$i]];
  }
  dump($r);
});

Route::get("/info", function () {
  return view('info');
})->name("php 版本");

Route::get("/bd", function () {
  $url = 'http://api.map.baidu.com/direction/v2/riding?origin=40.01116,116.339303&destination=39.936404,116.452562&ak=8aPsM6Ff2Wr54IqSe6Vme2T9OiU549hG';
  return json_encode(GuzzleClient::get($url), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
})->name("百度路线");


Route::get("/wiki", function () {
  return view("wiki.apidoc");
})->name("Wiki 文档");

Route::get("/route", function () {
  Artisan::call("route:list");
  $routes = preg_split('/\n/', Artisan::output());

  // 获取 keys
  $keys = preg_split('/\|/', $routes[1]);
  array_splice($keys, 0, 2);
  array_splice($keys, count($keys) - 1, 1);
  foreach ($keys as $key => $value) {
    $keys[$key] = strtolower(trim($value));
  }

  // 删除无效的row
  array_splice($routes, 0, 3);
  array_splice($routes, count($routes) - 2);

  $result = [];

  foreach ($routes as $route) {
    $route = preg_split('/ \|/', $route);
    array_splice($route, 0, 1);
    array_splice($route, count($route) - 1, 1);

    $obj = [];
    foreach ($keys as $key => $value) {
      $obj[$value] = trim($route[$key]);
    }

    array_push($result, $obj);
  }

  return $result;
})->name("路由信息");

