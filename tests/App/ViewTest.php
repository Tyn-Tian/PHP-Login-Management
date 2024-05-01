<?php

use LoginManagement\App\View;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    public function testViewHome()
    {
        View::render("Home/index", [
            "title" => "PHP Login Management"
        ]);

        $this->expectOutputRegex("[PHP Login Management]");
        $this->expectOutputRegex("[html]");
        $this->expectOutputRegex("[body]");
        $this->expectOutputRegex("[Register]");
    }
}