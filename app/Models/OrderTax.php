<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTax extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'tax_id',
        'price',                
    ];
    protected $table = 'order_tax';
    protected $appends = ['taxName'];

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id', 'id');
    }

    public function getTaxNameAttribute()
    {
        if ($this->relationLoaded('tax')) {
            return $this->tax?->name;
        }

        $tax = Tax::find($this->attributes['tax_id']);

        return $tax?->name;
    }
    

}
