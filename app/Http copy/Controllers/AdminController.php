<?php

namespace App\Http\Controllers;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'vip_users' => User::where('is_vip', true)->count(),
            'total_predictions' => Prediction::count(),
            'total_fixtures' => Fixture::count(),
            'pending_transactions' => Transaction::pending()->count(),
            'completed_transactions' => Transaction::completed()->count(),
        ];

        $recent_transactions = Transaction::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_transactions'));
    }

    public function fixtures()
    {
        $fixtures = Fixture::orderBy('match_date', 'desc')->paginate(20);
        return view('admin.fixtures', compact('fixtures'));
    }

    public function createFixture()
    {
        return view('admin.create-fixture');
    }

    public function storeFixture(Request $request)
    {
        $request->validate([
            'home_team' => 'required|string|max:255',
            'away_team' => 'required|string|max:255',
            'league' => 'required|string|max:255',
            'match_date' => 'required|date',
            'match_time' => 'required',
        ]);

        Fixture::create($request->all());

        return redirect()->route('admin.fixtures')->with('success', 'Fixture created successfully!');
    }

    public function predictions()
    {
        $predictions = Prediction::with('fixture')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.predictions', compact('predictions'));
    }

    public function createPrediction()
    {
        $fixtures = Fixture::upcoming()->orderBy('match_date')->get();
        return view('admin.create-prediction', compact('fixtures'));
    }

    public function storePrediction(Request $request)
    {
        $request->validate([
            'fixture_id' => 'required|exists:fixtures,id',
            'type' => 'required|in:tipsoftheday,featured,vip',
            'category' => 'required|string',
            'prediction' => 'required|string',
            'odds' => 'required|numeric|min:0',
            'probability' => 'required|integer|min:0|max:100',
            'tip' => 'nullable|string',
            'is_vip' => 'boolean',
        ]);

        Prediction::create($request->all());

        return redirect()->route('admin.predictions')->with('success', 'Prediction created successfully!');
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function transactions()
    {
        $transactions = Transaction::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.transactions', compact('transactions'));
    }
}
