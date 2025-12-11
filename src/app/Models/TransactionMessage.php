<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionMessage extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    // メッセージタイプの定数
    const TYPE_TEXT = 'text';          // 通常のメッセージ
    const TYPE_COMPLETION = 'completion'; // 取引完了通知
    const TYPE_REVIEW = 'review';      // 評価

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
