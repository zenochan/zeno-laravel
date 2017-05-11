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

Route::get('/', function () {
  return view('welcome');
});

Route::get("/info", function () {
  return view('info');
})->name("php 版本");

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

