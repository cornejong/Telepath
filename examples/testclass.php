<?php

class testClass
{
    public $variable = "this value";

    public function reverse($value)
    {
        return strrev($value);
    }

    public function shuff($array)
    {
        shuffle($array);
        return $array;
    }

    public function ret($a1, $a2, $a3, $a4)
    {
        return "Return the values: {$a1}, {$a2}, {$a3}, {$a4}";
    }
}

require_once '../Telepath/Server.Telepath.php';

$server = new Telepath\Server;
$server->loadClass('testClass');
$server->handle();
