<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
    public function items()
    {
        return $this->hasMany(Item::class);
    }
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }


    public function receivedReviews()
    {
        $buyerReviews = TransactionMessage::whereHas('purchase', function ($query) {
            $query->where('user_id', $this->id);
        })
            ->where('message_type', TransactionMessage::TYPE_REVIEW)
            ->where('sender_id', '!=', $this->id);

        $sellerReviews = TransactionMessage::whereHas('purchase.item', function ($query) {
            $query->where('user_id', $this->id);
        })
            ->where('message_type', TransactionMessage::TYPE_REVIEW)
            ->where('sender_id', '!=', $this->id);

        return $buyerReviews->union($sellerReviews);
    }

    public function givenReviews()
    {
        return TransactionMessage::where('sender_id', $this->id)
            ->where('message_type', TransactionMessage::TYPE_REVIEW);
    }

    public function getAverageRatingAttribute()
    {
        $reviews = TransactionMessage::where('message_type', TransactionMessage::TYPE_REVIEW)
            ->where(function ($query) {
                $query->whereHas('purchase', function ($subquery) {
                    $subquery->where('user_id', $this->id);
                })
                    ->orWhereHas('purchase.item', function ($subquery) {
                        $subquery->where('user_id', $this->id);
                    });
            })
            ->where('sender_id', '!=', $this->id);

        $average = $reviews->avg('rating');
        return $average ? round($average) : null;
    }

    public function getReviewCountAttribute()
    {
        $count = TransactionMessage::where('message_type', TransactionMessage::TYPE_REVIEW)
            ->where(function ($query) {
                $query->whereHas('purchase', function ($subquery) {
                    $subquery->where('user_id', $this->id);
                })
                    ->orWhereHas('purchase.item', function ($subquery) {
                        $subquery->where('user_id', $this->id);
                    });
            })
            ->where('sender_id', '!=', $this->id)
            ->count();

        return $count;
    }
}
