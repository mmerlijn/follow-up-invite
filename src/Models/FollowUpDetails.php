<?php

namespace mmerlijn\followUpInvite\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUpDetails extends Model
{
    public $casts = [
        'data' => 'array',
    ];

    public function getTable()
    {
        return config('fuinvite.tables.fuidetails');
    }

    public function message()
    {
        return $this->hasOne(FollowUpPatient::class, 'id', 'id')->withTrashed();
    }
}