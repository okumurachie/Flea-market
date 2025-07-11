<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ExhibitionRequest;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Favorite;



class ItemController extends Controller
{
    public function show($id)
    {
        $item = Item::with(['user', 'purchase'])->findOrFail($id);
        return view('detail', compact('item'));
    }

    public function toggleFavorite(Request $request)
    {
        $user = auth()->user();
        $itemId = $request->input('item_id');

        $favorite = Favorite::where('user_id', $user->id)
            ->where('item_id', $itemId)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $status = 'removed';
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'item_id' => $itemId,
            ]);
            $status = 'added';
        }

        $count = Favorite::where('item_id', $itemId)->count();

        return response()->json([
            'status' => $status,
            'count' => $count
        ]);
    }
}
