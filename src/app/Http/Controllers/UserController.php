<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Profile;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        $Items = Item::with('user')->get();
        $Items = Item::with('purchase')->get();
        return view('index', compact('users'));
    }
    public function profile()
    {
        $user = Auth::user();
        $profile = $user->profile ?? null;
        return view('profile', compact('user', 'profile'));
    }
}
