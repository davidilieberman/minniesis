<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckSisRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {

      if (!Auth::check()) {
        return redirect('/login');
      }

      $user = $request->user();
      $user->load('sis_role');
      $matched = ($role == $user->sis_role->role_code);
      $msg = $matched ? "matched" : "not matched";

      if(!$matched) {
        abort(403, 'Access Denied');
      }

      echo ('Matched: '.$user->sis_role->role_code. " || ".$role." || ". $msg. "<br/>");

      return $next($request);
    }
}
