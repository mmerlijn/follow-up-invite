<?php
return [
    'models' => [
        'user' => \App\Models\User::class,
        'messages' => \App\Models\msg\Message::class,
        'patient' => \App\Models\msg\Patient::class,
        'appointment' => \App\Models\pa\Appointment::class,
        'room' => \App\Models\pa\Room::class,
        'location' => \App\Models\pa\Location::class,
        'requester' => \App\Models\tool\Aanvrager::class,
    ],
    'tables' => [
        'fuipatients' => 'fui_patients',
        'fuidetails' => 'fui_details',

    ],
    'tests' => [
        'fundus' => [
            'providers' => ['' => '', 1 => 'GEEN', '3' => 'HZNK', 7 => 'SAG', 6 => 'SEZ'],
            'name' => 'fundus',
            'activity_id' => 6,
            'invite' => true,
            'invite_reminder' => true,
            'invite_days_before' => 56,
            'invite_reminder_after_days' => 42,
            'invite_letter' => 'fuinvite::pdf.fundus.invite',
            'invite_letter_reminder' => 'fuinvite::pdf.fundus.invite-reminder',
        ]
    ]
];