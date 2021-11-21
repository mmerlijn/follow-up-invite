<?php

namespace mmerlijn\followUpInvite\Models;

class MijnSaltRequester extends \Illuminate\Database\Eloquent\Model
{
    protected $connection = 'mysql_mijnsalt';
    protected $table='app_arts';
    protected $primaryKey="artsId";
    public $timestamps=false;
}