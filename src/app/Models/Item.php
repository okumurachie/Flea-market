<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


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
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function condition()
    {
        return $this->belongsTo(Condition::class);
    }
    public function purchase()
    {
        return $this->hasOne(Purchase::class);
    }
}
