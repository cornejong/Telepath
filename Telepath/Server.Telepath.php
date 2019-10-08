<?php

/**
 * Telepath - RPC Framework
 *
 * Server.Telepath.php
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

class Server
{
    /* TO DO: */
    /* NameSpace Support */
    /* Object Support */
    /* Proper Error Handling */
    /* End - TO DO */

    /* The server Status (bool) */
    /**
     * @var mixed
     */
    private $status = true;
    /* The client's request (array) */
    /**
     * @var mixed
     */
    private $request;
    /* The instantiated class (string) */
    /**
     * @var mixed
     */
    private $class;
    /* The server's instance (obj) */
    /**
     * @var mixed
     */
    private $instance;
    /* List of available methods inside $instance */
    /**
     * @var mixed
     */
    private $instanceMethods;

    /* List of error codes with associated messages */
    /**
     * @var array
     */
    private $errorCodes = [
        "0" => [
            "errorcode" => 0,
            "message" => "Class not defied!",
        ],
        "7" => [
            "errorcode" => 7,
            "message" => "Empty Request!",
        ],
        "8" => [
            "errorcode" => 8,
            "message" => "Submitted data is not JSON!",
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | __construct()
    |--------------------------------------------------------------------------
    |
    | Currently not in use
    |
     */

    public function __construct()
    {
        # Code...
    }

    /*
    | END OF -> __construct()
    |--------------------------------------------------------------------------
    | loadClass($class)
    |--------------------------------------------------------------------------
    |
    | Load the class into the server
    |
    |   $class = (string)   // Classname in string format
    |   Currently no namespace support, This is in development
    |
     */

    /**
     * @param string $class
     */
    public function loadClass(string $class)
    {
        /* Check if the provided class exists */
        if (class_exists((string) $class)) {
            /* If it does, store it in the $class variable */
            $this->class = $class;
            /* Create a new instance of the class */
            $this->instance = new $class(null);
            /* Get a list of all methods in the class */
            $this->instanceMethods = get_class_methods($this->instance);
            /* And return a conformation message */
            return ([
                "message" => "Class instantiated.",
                "class" => $class,
            ]);
        } else {
            /* If not, Set the status to false */
            $this->status = false;
            /* And return a message to inform the user */
            return ([
                "message" => "Class not defined.",
                "class" => $class,
            ]);
        }
    }

    /*
    | END OF -> loadClass($class)
    |--------------------------------------------------------------------------
    | responde($methodResponse, $format)
    |--------------------------------------------------------------------------
    |
    | This method builds the response to the client
    |
    |   $methodResponse = (mixed)   // The output from the requested action
    |   $format = (string)          // The output format of the responde method.
    |                                   Currently only JSON is supported.
    |                                   XML is in development
    |
     */

    /**
     * @param $methodResponse
     * @param $format
     */
    public function responde($methodResponse, $format = "JSON")
    {
        /* First build the response base */
        $response = [
            "id" => uniqid(), # unique ID for debuging
            "status" => $this->status, # the response status
            "response" => $methodResponse, # the methods response
            "request" => $this->request, # the original request
            "timestamp" => time(), # the response timestamp
        ];

        /* Check what output format was chosen  */
        switch ($format) {
            case 'JSON':
                /* If JSON, Print the final response as a json string */
                print(json_encode($response));
                break;

            /* Currently JSON is the only supported output format */
            default:
                # code...
                break;
        }
    }

    /*
    | END OF -> responde($methodResponse, $format)
    |--------------------------------------------------------------------------
    | handle()
    |--------------------------------------------------------------------------
    |
    | This method handles all the incomming requests
    |
     */

    public function handle()
    {
        /* Telepath only accepts POST requests */
        if ($_SERVER['REQUEST_METHOD'] !== "POST") {
            /* If its not a post request, send header 400 and kill the script */
            header('HTTP/1.1 400') && die;
        }

        /* Load the request */
        $this->getRequest();

        /* Switch based on the request type */
        switch ($this->request['type']) {
            /* If the requested action is 'method' */
            case 'method':
                /* Check if the method exists */
                if (in_array($this->request['content']['method'], $this->instanceMethods)) {
                    /* TO DO: Implement Error Handling */
                    /* If it exists, call the function and store the results in $classResponse */
                    $classResponse = call_user_func_array([$this->instance, $this->request['content']['method']], $this->request['content']['parameters']);
                } else {
                    /* if it doesn't exist, inform the user */
                    $classResponse = [
                        "message" => "Function not defined.",
                        "method" => $this->request['content']['method'],
                        "parameters" => $this->request['content']['parameters'],
                    ];
                }

                break;

            /* if the requested action is 'get' */
            case 'get':
                /* Check if the requested variable is set in the class */
                if (isset($this->instance->{$this->request['content']['name']})) {
                    /* If so, No worries, just add its value to the $classResponse */
                    $classResponse = $this->instance->{$this->request['content']['name']};
                } else {
                    /* if not, Inform the user */
                    $classResponse = [
                        "message" => "Variable not defined.",
                        "name" => $this->request['content']['name'],
                    ];
                }
                break;

            /* if the reqeuested action is 'set' */
            /* Eventhough this is a pretty useless function right now, Cuz the class is being initialized every time a new request is made. */
            /* Currently working on a session based storage system for continued use */
            case 'set':
                /* Check if everything went ok with the storing of the values */
                if ($this->instance->{$this->request['content']['name']} = $this->request['content']['value']) {
                    /* if all went well, just return true to the user */
                    $classResponse = true;
                } else {
                    /* if not, inform the user */
                    $classResponse = [
                        "message" => "Variable could not be saved.",
                        "name" => $this->request['content']['name'],
                        "value" => $this->request['content']['name'],
                    ];
                }
                break;
        }

        /* And at last, respond the result to the client */
        $this->responde($classResponse);
    }

    /*
    | END OF -> handle()
    |--------------------------------------------------------------------------
    | NEW FUNCTION
    |--------------------------------------------------------------------------
    |
    | FUNCTION DESCRIPTION
    |
     */

    /* Helper Functions */

    /**
     * @param $string
     */
    private function isJson($string)
    {
        # Decode the given string
        json_decode($string);
        # And return the json error
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /*
    | END OF -> isJson($string)
    |--------------------------------------------------------------------------
    | getRequest()
    |--------------------------------------------------------------------------
    |
    | FUNCTION DESCRIPTION
    |
     */

    private function getRequest()
    {
        # Retreve the post body
        $postdata = file_get_contents('php://input');

        # Check if post body isn't empty
        if (empty($postdata)) {
            // If the body is empty.. throw error 7
            $this->error([7]);
        }

        # Check if the content is JSON formated
        if (!$this->isJson($postdata)) {
            // If the body is not in json format.. throw error 7 + Json error
            $this->error([8, $this->isJson($postdata)]);
        }

        # Decode the Json
        $this->request = json_decode($postdata, true);

        # Return the array
        return ($this->request);

    }

    /*
    | END OF -> getRequest()
    |--------------------------------------------------------------------------
    | error($resource)
    |--------------------------------------------------------------------------
    |
    | This method compiles the error response. Still in development!
    |
    |   $resource = (array) // $resource[0] is the error code.
    |                           After that you van supply data to be send along
    |
     */

    /**
     * @param array $resource
     */
    private function error(array $resource)
    {
        # Get the error message from the JSON file
        $error = $this->errorCodes[$resource[0]];
        # Shift the array to only keep the provided resources
        $temp = array_shift($resource);

        # Compile and print the error message out to the user
        print_r(json_encode([
            "status" => "error",
            "statuscode" => $error["errorcode"],
            # Add the resource to the error message
            "message" => $error["message"]])) && die; # And die the script.

    }

    /*
    | END OF -> error($resource)
    |--------------------------------------------------------------------------
    | __call($function, $parameter)
    |--------------------------------------------------------------------------
    |
    | The magic call method. In case a method is called that does not exist.
    |
     */

    /**
     * @param $function
     * @param $parameter
     */
    public function __call($function, $parameter)
    {
        $this->status = false;
        return [
            "message" => "Function not defined.",
            "function" => $function,
            "parameters" => $parameter,
        ];
    }

}
