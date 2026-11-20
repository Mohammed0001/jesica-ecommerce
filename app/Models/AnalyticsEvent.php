<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['visitor_id', 'type', 'product_id', 'order_id', 'quantity', 'created_at'];

    protected $casts = ['created_at' => 'datetime'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
