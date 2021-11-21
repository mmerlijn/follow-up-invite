<?php

namespace mmerlijn\followUpInvite\Models;

class MijnSaltPatient extends \Illuminate\Database\Eloquent\Model
{
    protected $connection = 'mysql_mijnsalt';
    protected $table = 'app_contact';
    protected $primaryKey = "contactId";
    public $timestamps = false;
    protected $dates = [
        'gbdatum'
    ];

    public function getNaamAttribute($value)
    {
        $naam = strtolower($this->geslacht) == 'm' ? "Dhr. " : "Mw. ";
        if ($this->voorletters) {
            $naam .= $this->voorletters . " ";
        }
        if ($this->achternaam) {
            if ($this->tussenvoegsel_a) {
                $naam .= $this->tussenvoegsel_a . " ";
            }
            $naam .= $this->achternaam . " - ";
        }
        if ($this->tussenvoegsel_e) {
            $naam .= $this->tussenvoegsel_e . " ";
        }
        return $naam . $this->eigennaam;
    }

    public function getAdresAttribute($value)
    {
        return trim($this->straat . " " . $this->huisnr . " " . $this->huisnr_toevoegsel);
    }
}