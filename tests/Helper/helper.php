<?php

namespace LoginManagement\App {
    function header(string $value)
    {
        echo $value;
    }
}

namespace LoginManagement\Service {
    function setcookie(string $name, string $value) 
    {
        echo "$name: $value";
    }
}