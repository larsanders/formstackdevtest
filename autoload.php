<?php
function autoLoader($class)
{
    $class = "/vagrant/src/" . str_replace('\\', '/', $class) . ".php";
    require_once($class);
}

spl_autoload_register('autoLoader');
