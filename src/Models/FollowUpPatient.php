<?php

namespace mmerlijn\followUpInvite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use mmerlijn\followUpInvite\Database\Factories\FollowUpPatientFactory;

class FollowUpPatient extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'last_appointment_at' => 'date:Y-m-d',
        'last_test_at' => 'date:Y-m-d',
        'last_invitation_at' => 'date:Y-m-d',
        'last_reminder_invitation_at' => 'date:Y-m-d',
        'last_recurring_invitation_at' => 'date:Y-m-d',
        'next_invitation_at' => 'date:Y-m-d',
        'next_test_at' => 'date:Y-m-d',
        'stop' => 'date:Y-m-d',
        'wait_until' => 'date:Y-m-d',
        'reason' => 'array',
        'last_test' => 'array',

    ];
    //protected $dates = ['stop', 'wait_until'];

    protected $fillable = [
        'type', 'patient_id', 'mijnsalt_id', 'last_appointment_at', 'last_test_at', 'wait_until', 'stop', 'provider',
        'last_invitation_at', 'last_visit_location', 'next_test_at', 'days_between', 'next_invitation_at', 'last_reminder_invitation_at', 'requester'
    ];


    public function getTable()
    {
        return config('fuinvite.tables.fuipatients');
    }

    public function patient()
    {
        return $this->belongsTo(config('fuinvite.models.patient'));
    }

    public function mijnsaltpatient()
    {
        return $this->belongsTo(MijnSaltPatient::class, 'mijnsalt_id', 'contactId');
    }

    public function location()
    {
        return $this->belongsTo(MijnSaltLocation::class, 'last_visit_location', 'locatieId');
    }

    public function aanvrager()
    {
        return $this->belongsTo(MijnSaltRequester::class, 'requester', 'agbcode');
    }

    public function detail()
    {
        return $this->hasOne(FollowUpDetails::class, 'id', 'id');
    }

    protected static function newFactory()
    {
        return FollowUpPatientFactory::new();
    }

    public function setStopAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['stop'] = null;
        } else {
            $this->attributes['stop'] = $value;
        }
    }

    public function setWaitUntilAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['wait_until'] = null;
        } else {
            $this->attributes['wait_until'] = $value;
        }
    }

}