<?php


namespace mmerlijn\followUpInvite\View\Components;


use Illuminate\View\Component;

class GuestLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        if(view()->exists('layouts.guest')){
            return view('layouts.guest');
        }
        return view('fuinvite::layouts.guest');
    }
}