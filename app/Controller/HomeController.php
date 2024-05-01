<?php

namespace LoginManagement\Controller;

use LoginManagement\App\View;

class HomeController
{
    function index(): void 
    {
        View::render("Home/index", [
            "title" => "PHP Login Management"
        ]);
    }
}