<?php

/* Client Side */

/* Include the client Telepath class */
require_once '../Telepath/Client.Telepath.php';

/* Basic Setup */
$class = new Telepath\Client('testClass');
/* Additional Paramerters */
$class->setServerProtocol("https");
$class->setServerAddress("localhost");
$class->setServerPort(80);
$class->setServerDir("/applications/Telepath");

/* After altering the server information, run the compileServerLocation() method */
$class->compileServiceLocation();
/* Also you van pass the url to this function to bypass the previous functions */
/* But make sure to include the php file in the location! */
$class->compileServiceLocation("http://localhost:80/applications/Telepath/test.php");

/* For the setters above there are also getters */
$protocol = $class->getServerProtocol();
$address = $class->getServerAddress();
$port = $class->getServerPort();
$dir = $class->getServerDir();

/* Usage */
/* Just use the metods the same way u would use a local class */
$test = $class->reverse("test");
$test = $class->shuff(array(1,2,3,4,5,6));
$value = $class->variable;
$class->variable = "this Value";

?>
