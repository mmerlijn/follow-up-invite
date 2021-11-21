<?php
namespace mmerlijn\followUpInvites\tests\Feature;


use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use mmerlijn\forms\tests\TestCase;
use mmerlijn\forms\tests\User;

class BasicRouteTest extends TestCase
{
    use InteractsWithViews;
    public function test_forms_index_route_works()
    {
        $this->get(route('forms.index'))
            ->assertSee("Hallo");
    }



}