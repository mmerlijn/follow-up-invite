<?php

namespace mmerlijn\followUpInvite\Models;

class MijnSaltLocation extends \Illuminate\Database\Eloquent\Model
{
    protected $connection = 'mysql_mijnsalt';
    protected $table='agd_locatie';
    protected $primaryKey="locatieId";
    public $timestamps=false;
}