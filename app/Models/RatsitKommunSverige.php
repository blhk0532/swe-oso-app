<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatsitKommunSverige extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ratsit_kommuner_sverige';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kommun',
        'post_nummer',
        'personer_total',
        'ratsit_link',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'kommun' => 'string',
        'post_nummer' => 'string',
        'personer_total' => 'integer',
        'ratsit_link' => 'string',
    ];
}
