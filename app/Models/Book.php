<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'description',
        'total_copies',
        'available_copies',
        'category_id',
        'rack_id',
    ];

    public static function booted(): void
    {
        static::created(
            fn(Model $model) => $model->updateQuietly([
                'available_copies' => $model->total_copies
            ])
        );
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function rack()
    {
        return $this->belongsTo(Rack::class);
    }

    public function transactions()
    {
        return $this->hasMany(BookTransaction::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_copies', '>', 0);
    }

    public function updateAvailability()
    {
        $this->available_copies = $this->total_copies - $this->transactions()
            ->whereNull('returned_date')
            ->count();
        $this->save();
    }
}
