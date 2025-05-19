<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    protected $fillable = [
        'name',
        'rxcui'
        // 'base_names',
        // 'dosage_forms'
    ];
    protected $casts = [
        'base_names' => 'array',
        'dosage_forms' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_drugs');
    }
}
