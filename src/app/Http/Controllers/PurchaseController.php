<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    public function show($id)
    {
        // $id に URL から渡された item_id が入ります
        $item = Item::findOrFail($id);
        return view('purchase.show', compact('item'));
    }
}
