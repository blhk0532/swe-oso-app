<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Minimal Task model used by the Calendar widget.
 * Fields expected by the widget: id, name, start, end
 */
class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'start',
        'end',
    ];

    /**
     * Cast start/end to datetimes so FullCalendar receives ISO strings.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];
}
