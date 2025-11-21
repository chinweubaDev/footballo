<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Models\Fixture;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Get today's tips (max 5)
        $tipsOfTheDay = Prediction::tipsOfTheDay()
            ->today()
            ->with('fixture')
            ->limit(5)
            ->get()
            ->groupBy('fixture.league');

        // Get featured predictions (max 15)
        $featuredPredictions = Prediction::featured()
            ->today()
            ->with('fixture')
            ->limit(15)
            ->get();

        return view('home', compact('tipsOfTheDay', 'featuredPredictions'));
    }
}
