<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        $Items = Item::with('user')->get();
        $Items = Item::with('purchase')->get();
        return view('index', compact('users'));
    }
}
