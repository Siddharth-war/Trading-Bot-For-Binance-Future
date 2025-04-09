<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        /*$this->middleware('auth');*/
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(): Renderable
    {
        return view('home');
    }

    /**
     * @return Factory|View|Application
     */
    public function about_us(): Factory|View|Application
    {
        return view('about-us');
    }

    /**
     * @return Factory|View|Application
     */
    public function terms_and_conditions(): Factory|View|Application
    {
        return view('terms-and-conditions');
    }

    /**
     * @return Factory|View|Application
     */
    public function privacy_policy(): Factory|View|Application
    {
        return view('privacy-policy');
    }
}

