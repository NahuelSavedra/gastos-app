<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = ['title', 'amount', 'type', 'category_id', 'date'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }}
