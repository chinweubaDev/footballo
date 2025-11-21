<?php

namespace App\Http\Controllers;

use App\Models\Prediction;
use App\Models\Fixture;
use Illuminate\Http\Request;

class PredictionController extends Controller
{
    public function index()
    {
        $predictions = Prediction::with('fixture')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('predictions.index', compact('predictions'));
    }

    public function expertTips()
    {
        $predictions = Prediction::featured()
            ->with('fixture')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('predictions.expert-tips', compact('predictions'));
    }

    public function category($category)
    {
        $categories = [
            '1x2' => '1X2 Predictions',
            'double_chance' => 'Double Chance',
            'over_1_5' => 'Over 1.5 Goals',
            'over_2_5' => 'Over 2.5 Goals',
            'draw' => 'Draw Predictions',
            'bts' => 'Both Teams to Score'
        ];

        $title = $categories[$category] ?? 'Predictions';

        $predictions = Prediction::byCategory($category)
            ->with('fixture')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('predictions.category', compact('predictions', 'title', 'category'));
    }

    public function vipTips()
    {
        $this->middleware('auth');
        
        if (!auth()->user()->isVipActive()) {
            return redirect()->route('pricing')->with('error', 'You need VIP access to view VIP tips.');
        }

        $predictions = Prediction::vip()
            ->with('fixture')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('predictions.vip-tips', compact('predictions'));
    }
}
