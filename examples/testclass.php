<?php

class test
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

require_once './RPC_SERVER/Server.SC_RPC.php';

$server = new Telepath\Server;
$server->loadClass('test');
$server->handle();