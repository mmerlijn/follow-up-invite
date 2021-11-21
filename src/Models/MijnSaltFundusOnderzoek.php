<?php

namespace mmerlijn\followUpInvite\Models;

class MijnSaltFundusOnderzoek extends \Illuminate\Database\Eloquent\Model
{
    protected $connection = 'mysql_mijnsalt';
    protected $table = 'fun_onderzoek';
    protected $primaryKey = "onderzoekId";
    public $timestamps = false;

    protected $dates = [
        'datumonderzoek'
    ];
    
}