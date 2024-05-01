<?php 

namespace LoginManagement\Middleware;

interface Middleware
{
    function before(): void;
}