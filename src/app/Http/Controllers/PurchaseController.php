<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    public function show($id)
    {
        $item = Item::findOrFail($id);
        $user = Auth::user();
        return view('purchase', compact('item', 'user'));
    }
}
