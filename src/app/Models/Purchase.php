<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function hasPartnerReviewed()
    {
        $partnerId = $this->isBuyer() ? $this->item->user_id : $this->user_id;
        return $this->reviews()
            ->where('sender_id', $partnerId)
            ->exists();
    }

    public function isInProgress()
    {
        return $this->transaction_status === self::STATUS_IN_PROGRESS;
    }

    public function isBuyerCompleted()
    {
        return $this->transaction_status === self::STATUS_BUYER_COMPLETED;
    }

    public function isCompleted()
    {
        return $this->transaction_status === self::STATUS_COMPLETED;
    }

    public function canUserReview()
    {
        if ($this->hasUserReviewed()) {
            return false;
        }

        if ($this->isBuyer()) {
            return true;
        }

        if ($this->isSeller()) {
            return $this->isBuyerCompleted() || $this->isCompleted();
        }

        return false;
    }
}
