<?php

namespace mmerlijn\followUpInvite\Http\Controllers;


use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FollowUpInviteController extends Controller
{

    public function __construct()
    {
    }

    public function index($type = 'fundus')
    {
        return view('fuinvite::fui.index');
    }

}
