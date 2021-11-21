<?php


namespace mmerlijn\followUpInvite\View\Components;

use Illuminate\View\Component;


class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        if(view()->exists('layouts.app'))
        {
            return view('layouts.app');
        }
        return view('fuinvite::layouts.app');

    }
}