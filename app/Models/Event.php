<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\RequestData;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'user_id',
        'type',
        'event_link',
        'address',
        'category_id',
        'start_time',
        'end_time',
        'image',
        'gallery',
        'people',
        'lat',
        'lang',
        'description',
        'security',
        'status',
        'event_status',
        'is_deleted',
        'scanner_id',
        'tags',
    ];

    protected $table = 'events';

    // Keep the mobile API response shape unchanged; admin/public views read
    // this field directly instead of serialising it.
    protected $hidden = ['event_link'];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];
    protected $appends = ['imagePath','rate','totalTickets','soldTickets'];

    public function category()
    {
        return $this->hasOne('App\Models\Category', 'id', 'category_id');
    }
    
    public function organization()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function scanners()
    {
        return $this->belongsToMany('App\Models\User', 'event_scanner', 'event_id', 'scanner_id');
    }

    public function syncScanners(array $scannerIds): void
    {
        $ids = collect($scannerIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $this->scanners()->sync($ids);
    }

    public function ticket()
    {
        return $this->hasMany('App\Models\Ticket', 'event_id', 'id');
    }
    
    public function getImagePathAttribute()
    {
        return url('images/upload') . '/';
    }

    public function getTotalTicketsAttribute()
    {
        $setting = app(RequestData::class)->setting();
        $timezone = $setting->timezone ?? config('app.timezone');
        $date = Carbon::now($timezone);
        return Ticket::where([['event_id',$this->attributes['id']],['is_deleted',0],['status',1],['end_time', '>=',$date->format('Y-m-d H:i:s')],['start_time', '<=',$date->format('Y-m-d H:i:s')]])->sum('quantity');
    }

    public function getSoldTicketsAttribute()
    {
        return  Order::where('event_id',$this->attributes['id'])->sum('quantity');
    }    

    public function getRateAttribute()
    {
        $aggregate = Review::where('event_id', $this->attributes['id'])
            ->selectRaw('SUM(rate) as total_rate, COUNT(*) as review_count')
            ->first();

        $count = (int) ($aggregate->review_count ?? 0);

        return $count > 0 ? round((float) ($aggregate->total_rate ?? 0) / $count) : 0;
    }

    public function scopeDurationData($query,$start , $end){
        $data =  $query->whereBetween('start_time', [ $start,  $end]);
        return $data;
    }
}
