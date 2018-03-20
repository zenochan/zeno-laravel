<?php

namespace App\Http\Middleware;

use app\Http\Controllers\GuzzleClient;
use Closure;
use Illuminate\Http\Request;

class EnableCross
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request $request
   * @param  \Closure $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next)
  {
    $response = $next($request);
    $response->header("Access-Control-Allow-Origin", "*");
    $response->header("Access-Control-Allow-Headers", "X-XSRF-TOKEN");
    $response->header("Access-Control-Allow-Methods", "GET,PUT,POST,PATCH,DELETE,OPTIONS");

    return $response;
  }
}
