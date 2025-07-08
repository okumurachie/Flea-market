<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class Item extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'item_name',
        'category_id',
        'condition_id',
        'bland',
        'description',
        'price',
        'item_image',
        'shipping_status'
    ];
    protected $casts = [
        'display_date',
        'deleted_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
    public function condition()
    {
        return $this->belongsTo(Condition::class);
    }
    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }
    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
}
