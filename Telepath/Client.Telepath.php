<?php

/**
* Telepath - RPC Framework
*
* Client.Telepath.php
*
* A Simple JSON based RPC Framework for remote execution of
* PHP Classes.
*
* @package      Telepath
* @author       Corné de Jong <corne@cornedejong.net>
* @version      0.01
* @link         Nubes /applications/Telepath/
* @since        File Available since 20 May 2018
*
*/


namespace Telepath;

class Client
{
    /* The response array (array) */
    public $responses = array();
    /* The server protocol (string) */
    private $serverProtocol = "http";
    /* The server IP or Host name (string) */
    private $serverAddress = "localhost";
    /* The server port 'optional' (mixed/string or integer) */
    private $serverPort = NULL;
    /* The service directory on the server (string) */
    private $serverDir = "/applications/Telepath";
    /* The full request path including the php file (string) */
    private $serviceLocation = "";
    /* Previous requests array (array) */
    private $requests = array();
    /* The requested class (string) */
    private $class = "";

    /*
    |--------------------------------------------------------------------------
    | __construct($class)
    |--------------------------------------------------------------------------
    |
    | Telepath constructor. Sets up the class location and class name
    | 
    |   $class = (string) // Requested Classname in string format
    |                        If nested in namespaces: "__NAMESPACE__\__CLASS__"
    |
    */

    public function __construct($class)
    {
        /* Store the classname in $this->class as a string */
        $this->class = (string) $class;
        /* Then compile the full service location */
        $this->compileServiceLocation();
    }

    /*
    | END OF -> __construct
    |--------------------------------------------------------------------------
    | __callFunction($request)
    |--------------------------------------------------------------------------
    |
    | This class formats the request and proccesses the response
    |
    |   $type = (string)    // The request type. | Options: 'method' 'get' 'set'.
    |   $content = (array) // The request content. Compiled by the corresponding magic method.
    |
    */

    public function _callClass($type, $content)
    {
        /* Setup the request array */
        $request = array(
            "type" => $type,
            "content" => $content
        );

        /* Add it to the $requests array */
        $this->requests[] = $request;

        /* Proccess the request and retrieve the response */
        $response = $this->_process($request);
        /* Add the response to the $responses array */
        $this->responses[] = $response;
        /* And return the methods response to the user */
        return($response['response']);
    }

    /*
    | END OF -> __callFunction($request)
    |--------------------------------------------------------------------------
    | __call($method, $param)
    |--------------------------------------------------------------------------
    |
    | This method compiles the request content for called methods. 
    |
    |   $method = (string)  // The requested method
    |   $param = (array)    // The variables passed onto the requested method 
    |
    */

    public function __call($method, $param)
    {
        /* Compile the request content array */
        $content = array(
            "method" => $method,
            "parameters" => $param
        );
        /* Send the request off to the _callClass Method for further proccessing */
        return($this->_callClass("method", $content));
    }

    /*
    | END OF -> __call($function, $param)
    |--------------------------------------------------------------------------
    | __get($name)
    |--------------------------------------------------------------------------
    |
    | This method compiles the request content for requested variables 
    |
    |   $name = (string)  // The requested variable name
    |
    */

    public function __get($name)
    {
        /* Compile the request content array */
        $content = array(
            "name" => $name,
        );
        /* Send the request off to the _callClass Method for further proccessing */
        return($this->_callClass("get", $content));
    }

    /*
    | END OF -> __get($name)
    |--------------------------------------------------------------------------
    | __set($name, $value)
    |--------------------------------------------------------------------------
    |
    | This method compiles the request content for updating variables. 
    |
    |   $name = (string)    // The requested variable
    |   $value = (mixed)    // The new value for the variable
    |
    */

    public function __set($name, $value)
    {
        /* Compile the request content array */
        $content = array(
            "name" => $name,
            "value" => $value
        );
        /* Send the request off to the _callClass Method for further proccessing */
        return($this->_callClass("set", $content));
    }

    /*
    | END OF -> __set($name, $value)
    |--------------------------------------------------------------------------
    | _process($request)
    |--------------------------------------------------------------------------
    |
    | This method sends the POST request to the server class. 
    |
    |   $request = (arary)  // The request array with the request content and type
    |
    */

    private function _process(array $request)
    {
        /* JSON encode the request */
        $request_string = json_encode($request);                                                                                   
        
        /* initialize the cURL request and pass the service location */
        $call = curl_init($this->serviceLocation);
        curl_setopt($call, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($call, CURLOPT_POSTFIELDS, $request_string);
        curl_setopt($call, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($call, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            /* Set the Telepath request header */
            'Telepath-Request: 1',
            'Content-Length: ' . strlen($request_string))
        );
        /* Execute the cURL POST request */
        $response = curl_exec($call);

        if (curl_errno($call)) {
            // this would be the first hint that something went wrong
            $response ='Couldn\'t send request: ' . curl_error($call);
        } else {
            // check the HTTP status code of the request
            $resultStatus = curl_getinfo($call, CURLINFO_HTTP_CODE);
            if ($resultStatus == 200) {
                // everything went better than expected
            } else {
                $response = 'Request failed: HTTP status code: ' . $resultStatus;
            }
        }

        /* And at last, Return the response to the user */
        return json_decode($response, true);
        
    }

    /*
    | END OF -> _process($request)
    |--------------------------------------------------------------------------
    | compileServiceLocation($location = NULL)
    |--------------------------------------------------------------------------
    |
    | Compile the full path to the remote service.
    |
    */

    public function compileServicLocation($location = NULL)
    {
        /* Check if the user has provided the full path */
        if($location === NULL) {
            /* If not, Compile it from the available data */
            $this->serviceLocation = "{$this->serverProtocol}://{$this->serverAddress}";
            /* if there is a port specified, add it to the server location */
            if($this->serverPort !== NULL) $this->serviceLocation .= ":{$this->serverPort}";
            /* At last add the server directory and file */
            $this->serviceLocation .= "{$this->serverDir}/{$this->class}.php";
        } else {
            /* Else, Set the provided path */
            $this->serviceLocation = (string) $location;
        }
        
        /* And return true (: */
        return true;
    }

    /* Getters and Setters */
    /* Well, this is self-explanatory */

    public function setServerAddress($address)
    {
        return ($this->serverAddress = $address);
    }

    public function setServerPort($port)
    {
        return ($this->serverPort = (string) $port);
    }

    public function setServerDir($location)
    {
        return ($this->serverDir = $location);
    }

    public function setServerProtocol($protocol)
    {
        return ($this->serverProtocol = $protocol);
    }

    public function getServerAddress()
    {
        return $this->serverAddress;
    }

    public function getServerDir()
    {
        return $this->serverDir;
    }

    public function getServerProtocol()
    {
        return $this->serverProtocol;
    }

}
