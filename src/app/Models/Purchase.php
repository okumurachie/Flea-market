<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stripe\FinancialConnections\Transaction;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
        'payment_method',
        'post_code',
        'destination',
        'last_message_at',
        'transaction_status',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_BUYER_COMPLETED = 'buyer_completed';
    const STATUS_COMPLETED = 'completed';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function messages()
    {
        return $this->hasMany(TransactionMessage::class, 'purchase_id');
    }

    public function textMessages()
    {
        return $this->hasMany(TransactionMessage::class)->where('message_type', TransactionMessage::TYPE_TEXT);
    }

    public function reviews()
    {
        return $this->hasMany(TransactionMessage::class)->where('message_type', TransactionMessage::TYPE_REVIEW);
    }

    public function lastMessage()
    {
        return $this->hasOne(TransactionMessage::class)->latestOfMany();
    }

    public function getPartnerAttribute()
    {
        $currentUserId = auth()->id();
        if ($this->user_id === $currentUserId) {
            return $this->item->user;
        } else {
            return $this->user;
        }
    }

    public function isBuyer()
    {
        return $this->user_id === auth()->id();
    }

    public function isSeller()
    {
        return $this->item->user_id === auth()->id();
    }


    public function hasUserReviewed()
    {
        return $this->reviews()
            ->where('sender_id', auth()->id())
            ->exists();
    }

    /**
     * 相手が評価済みかどうか
     */
    public function hasPartnerReviewed()
    {
        $partnerId = $this->isBuyer() ? $this->item->user_id : $this->user_id;
        return $this->reviews()
            ->where('sender_id', $partnerId)
            ->exists();
    }

    /**
     * 取引が進行中かどうか
     */
    public function isInProgress()
    {
        return $this->transaction_status === self::STATUS_IN_PROGRESS;
    }

    /**
     * 購入者が完了済みかどうか
     */
    public function isBuyerCompleted()
    {
        return $this->transaction_status === self::STATUS_BUYER_COMPLETED;
    }

    /**
     * 取引が完全に完了しているかどうか
     */
    public function isCompleted()
    {
        return $this->transaction_status === self::STATUS_COMPLETED;
    }

    /**
     * 現在のユーザーが評価可能かどうか
     */
    public function canUserReview()
    {
        // すでに評価済みなら不可
        if ($this->hasUserReviewed()) {
            return false;
        }

        // 購入者の場合：いつでも評価可能
        if ($this->isBuyer()) {
            return true;
        }

        // 出品者の場合：購入者が完了した後のみ評価可能
        if ($this->isSeller()) {
            return $this->isBuyerCompleted() || $this->isCompleted();
        }

        return false;
    }
}
