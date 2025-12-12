<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatMessageReauest;
use App\Models\Purchase;
use App\Models\TransactionMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\TransactionCompletedMail;

class ChatController extends Controller
{
    public function show(Purchase $purchase)
    {
        $user = auth()->user();
        if ($purchase->user_id !== $user->id && $purchase->item->user_id !== $user->id) {
            abort(403, '権限がありません');
        }
        $purchase->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $purchase->messages()
            ->where('message_type', '!=', TransactionMessage::TYPE_REVIEW)
            ->orderBy('created_at', 'asc')
            ->get();

        $transactions = Purchase::where('user_id', AUth::id())
            ->orWhereHas('item', fn($query) => $query->where('user_id', Auth::id()))
            ->orderByDesc('last_message_at')
            ->get();

        return view('chat', compact('purchase', 'messages', 'transactions', 'user'));
    }

    public function store(ChatMessageReauest $request, Purchase $purchase)
    {
        $data = $request->validated();

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/images/ChatImages/', $fileName);
            $imagePath = 'storage/images/ChatImages/' . $fileName;
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

        return redirect()->route('chat.show', $purchase->id);
    }

    public function update(Request $request, TransactionMessage $message)
    {
        try {
            $this->authorize('update', $message);

            $validated = $request->validate([
                'body' => 'required|max:400',
            ]);

            $newMessageText = $validated['body'];
            $message->update(['message' => $newMessageText]);

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(TransactionMessage $message)
    {
        try {
            $this->authorize('delete', $message);

            $message->delete();

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function complete(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'is_seller' => 'required|boolean'
        ]);

        $currentUserId = auth()->id();

        if ($validated['is_seller']) {
            if ($purchase->item->user_id !== $currentUserId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } else {
            if ($purchase->user_id !== $currentUserId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        DB::beginTransaction();
        try {
            TransactionMessage::create([
                'purchase_id' => $purchase->id,
                'sender_id' => $currentUserId,
                'message_type' => TransactionMessage::TYPE_REVIEW,
                'message' => '',
                'rating' => $validated['rating'],
                'is_read' => false,
            ]);

            if ($validated['is_seller']) {
                $purchase->transaction_status = 'completed';
            } else {
                $purchase->transaction_status = 'buyer_completed';

                $seller = $purchase->item->user;

                try {
                    Mail::to($seller->email)->send(new TransactionCompletedMail($purchase));
                } catch (\Exception $mailError) {
                    Log::error('Mail sending error:' .  $mailError->getMessage());
                }
            }
            $purchase->save();

            DB::commit();

            return response()->json([
                'message' => 'Rating submitted successfully',
                'status' => $purchase->transaction_status
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rating submission error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to submit rating'], 500);
        }
    }
}
