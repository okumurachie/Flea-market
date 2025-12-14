<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'sender_id',
        'message_type',
        'message',
        'chat_image',
        'rating',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    const TYPE_TEXT = 'text';
    const TYPE_COMPLETION = 'completion';
    const TYPE_REVIEW = 'review';

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isTextMessage()
    {
        return $this->message_type === self::TYPE_TEXT;
    }

    public function isCompletionMessage()
    {
        return $this->message_type === self::TYPE_COMPLETION;
    }

    public function isReviewMessage()
    {
        return $this->message_type === self::TYPE_REVIEW;
    }
}
