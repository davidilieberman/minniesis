<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $user = Auth::user();
      $user->load('sis_role');
      $role = $user->sis_role->role_code;
      switch ($role) {
        case "FAC":
          return redirect('/faculty');
          break;
        case "STU":
          return redirect('/student');
          break;
        case "RGR":
          return redirect('/registrar');
          break;
        default:
          abort(403);
      }
    }
}
