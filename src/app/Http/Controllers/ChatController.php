<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatMessageReauest;
use App\Models\Purchase;
use App\Models\TransactionMessage;
use Illuminate\Http\Request;
use App\Http\Requests\ChatMessageRequest;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function show(Purchase $purchase)
    {
        $user = auth()->user();
        if ($purchase->user_id !== $user->id && $purchase->item->user_id !== $user->id) {
            abort(403, '権限がありません');
        }

        $messages = $purchase->messages()->orderBy('created_at', 'asc')->get();
        $transactions = Purchase::where('user_id', AUth::id())
            ->orWhereHas('item', fn($query) => $query->where('user_id', Auth::id()))
            ->orderByDesc('last_message_at')
            ->get();

        return view('chat', compact('purchase', 'messages', 'transactions'));
    }

    public function store(ChatMessageReauest $request, Purchase $purchase)
    {
        $data = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/images/ChatImages/', $fileName);
            $imagePath['chat_image'] = 'storage/images/ChatImages/' . $fileName;
        }

        TransactionMessage::create([
            'purchase_id'  => $purchase->id,
            'sender_id'    => auth()->id(),
            'message_type' => TransactionMessage::TYPE_TEXT,
            'message'      => $data['text'],
            'chat_image'   => $imagePath,
            'is_read'      => false,
        ]);

        $purchase->update(['last_message_at' => now()]);

        return redirect()->route('chat.show', $purchase->id)
            ->with('message', 'メッセージを送信しました')
            ->withInput();
    }
}
