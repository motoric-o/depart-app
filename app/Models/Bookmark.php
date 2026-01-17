<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    //
    protected $fillable = ['user_id', 'bookmarkable_id', 'bookmarkable_type'];

    /**
     * Get the parent bookmarkable model (schedule or booking).
     */
    public function bookmarkable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who bookmarked the item.
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'user_id');
    }
}
