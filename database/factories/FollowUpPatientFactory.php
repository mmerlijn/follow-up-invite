<?php
namespace mmerlijn\followUpInvite\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use mmerlijn\followUpInvite\Models\FollowUpPatient;

class FollowUpPatientFactory extends Factory
{

    protected $model = FollowUpPatient::class;
    /**
     * @inheritDoc
     */
    public function definition()
    {
        return [
            'type'=>'fundus',
            'provider'=>6,
        ];
    }


}