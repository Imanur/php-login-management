<?php

namespace Programmerphp\Loginmanagement\Config;

use PHPUnit\Framework\TestCase;
use Programmerphp\Loginmanagement\Config\View;

class ViewTest extends TestCase
{


    public function testRender()
    {

        View::render('Home/index', [
            'PHP Login Management'
        ]);

        $this->expectOutputRegex('[PHP Login Management]');
        $this->expectOutputRegex('[html]');
        $this->expectOutputRegex('[body]');
        $this->expectOutputRegex('[Login]');
        $this->expectOutputRegex('[Register]');
    }
}
