<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ExhibitionRequest;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Favorite;
use App\Models\Comment;
use App\Http\Requests\CommentRequest;






class ItemController extends Controller
{
    public function show($id)
    {
        $item = Item::with([
            'comments.user.profile',
            'user',
            'categories',
            'purchase',
            'condition'
        ])->findOrFail($id);
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
    public function addComment(CommentRequest $request)
    {

        Comment::create([
            'user_id' => auth()->id(),
            'item_id' => $request->item_id,
            'comment' => $request->comment,
        ]);

        return redirect()->back()->with('success', 'コメントを投稿しました');
    }
}
