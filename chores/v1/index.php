<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/26/17
 * Time: 5:15 PM
 */
require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        } else {
            global $proff_id;
            // get user primary key id
            $proff_id = $db->getUserId($api_key);
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * ----------- METHODS WITHOUT AUTHENTICATION ---------------------------------
 */
/**
 * proffesional Registration
 * url - /register
 * method - POST
 * params - name, email, password
 */
$app->post('/register', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('first_name', 'last_name', 'email', 'password'));

    $response = array();

    // reading post params
    $first_name = $app->request->post('first_name');
    $last_name = $app->request->post('last_name');
    $email = $app->request->post('email');
    $password = $app->request->post('password');

    // validating email address
    validateEmail($email);

    $db = new DbHandler();
    $res = $db->createUser($first_name, $last_name, $email, $password);

    if ($res == USER_CREATED_SUCCESSFULLY) {
        $response["error"] = false;
        $response["message"] = "You are successfully registered";
    } else if ($res == USER_CREATE_FAILED) {
        $response["error"] = true;
        $response["message"] = "Oops! An error occurred while registering";
    } else if ($res == USER_ALREADY_EXISTED) {
        $response["error"] = true;
        $response["message"] = "Sorry, this email already existed";
    }
    // echo json response
    echoRespnse(201, $response);
});

/**
 * proffesional Login
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('email', 'password'));

    // reading post params
    $email = $app->request()->post('email');
    $password = $app->request()->post('password');
    $response = array();

    $db = new DbHandler();
    // check for correct email and password
    if ($db->checkLogin($email, $password)) {
        // get the user by email
        $proffesional = $db->getUserByEmail($email);
        if ($proffesional != NULL) {

            $response["error"] = false;
            $response['proff_id'] = $proffesional['proff_id'];
            $response['proff_name'] = $proffesional['proff_name'];
            $response['email'] = $proffesional['email'];
            $response['cell_no'] = $proffesional['cell_no'];
            $response['national_id'] = $proffesional['national_id'];
            $response['location'] = $proffesional['location'];
            $response['availability_status'] = $proffesional['availability_status'];
            $response['image'] = $proffesional['image'];
            $response['first_name'] = $proffesional['first_name'];
            $response['last_name'] = $proffesional['last_name'];
            $response['api_key'] = $proffesional['api_key'];
            $response['gender'] = $proffesional['gender'];
            $response['status'] = $proffesional['status'];
            $response['created_at'] = $proffesional['created_at'];
        } else {
            // unknown error occurred
            $response['error'] = true;
            $response['message'] = "An error occurred. Please try again";
        }
    }
    else {
        // user credentials are wrong
        $response['error'] = true;
        $response['message'] = 'Login failed. Incorrect credentials';
    }

    echoRespnse(200, $response);
});

/**
 * ------------------------ METHODS WITH AUTHENTICATION ------------------------
 */

/**
 * Listing all tasks of particual proffesional
 * method GET
 * url /proffesionals
 */
$app->get('/proffesionals', 'authenticate', function() {

    $response = array();
    $db = new DbHandler();

    // fetching all proffesionals
    $result = $db->getAllproffesionals();

    $response["error"] = false;
    $response["proffesionals"] = array();

    // looping through result and preparing tasks array
    while ($proffesional = $result->fetch_assoc()) {
        $tmp = array();
        $tmp['proff_id'] = $proffesional['proff_id'];
        $tmp['proff_name'] = $proffesional['proff_name'];
        $tmp['email'] = $proffesional['email'];
        $tmp['cell_no'] = $proffesional['cell_no'];
        $tmp['national_id'] = $proffesional['national_id'];
        $tmp['location'] = $proffesional['location'];
        $tmp['availability_status'] = $proffesional['availability_status'];
        $tmp['image'] = $proffesional['image'];
        $tmp['first_name'] = $proffesional['first_name'];
        $tmp['last_name'] = $proffesional['last_name'];
        $tmp['api_key'] = $proffesional['api_key'];
        $tmp['gender'] = $proffesional['gender'];
        $tmp['status'] = $proffesional['status'];
        $tmp['created_at'] = $proffesional['created_at'];
        array_push($response["proffesionals"], $tmp);
    }

    echoRespnse(200, $response);
});

/**
 * Listing single proffesional
 * method GET
 * url /proffesional/:id
 * Will return 404 if the proffesional doesn't exist
 */
$app->get('/proffesional/:id', 'authenticate', function($proff_id) {
//    global $proff_id;
    $response = array();
    $db = new DbHandler();

    // fetch task
    $result = $db->getProffesional($proff_id);

    if ($result != NULL) {
        $response["error"] = false;
        $response['proff_id'] = $result['proff_id'];
        $response['proff_name'] = $result['proff_name'];
        $response['email'] = $result['email'];
        $response['cell_no'] = $result['cell_no'];
        $response['national_id'] = $result['national_id'];
        $response['location'] = $result['location'];
        $response['availability_status'] = $result['availability_status'];
        $response['image'] = $result['image'];
        $response['first_name'] = $result['first_name'];
        $response['last_name'] = $result['last_name'];
        $response['api_key'] = $result['api_key'];
        $response['gender'] = $result['gender'];
        $response['status'] = $result['status'];
        $response['created_at'] = $result['created_at'];
        echoRespnse(200, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

/**
 * Creating new task in db
 * method POST
 * params - name
 * url - /tasks/
 */
$app->post('/tasks', 'authenticate', function() use ($app) {
    // check for required params
    verifyRequiredParams(array('task'));

    $response = array();
    $task = $app->request->post('task');

    global $proff_id;
    $db = new DbHandler();

    // creating new task
    $task_id = $db->createTask($proff_id, $task);

    if ($task_id != NULL) {
        $response["error"] = false;
        $response["message"] = "Task created successfully";
        $response["task_id"] = $task_id;
        echoRespnse(201, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "Failed to create task. Please try again";
        echoRespnse(200, $response);
    }
});

/**
 * Updating existing task
 * method PUT
 * params task, status
 * url - /tasks/:id
 */
$app->put('/proffesionals/:id', 'authenticate', function($proff_id) use($app) {
    // check for required params
    verifyRequiredParams(array('proff_name', 'email', 'cell_no', 'national_id', 'location', 'image', 'first_name', 'last_name'));

    global $proff_id;
    $proff_name = $app->request->put('proff_name');
    $email = $app->request->put('email');
    $cell_no = $app->request->put('cell_no');
    $national_id = $app->request->put('national_id');
    $location = $app->request->put('location');
    $image = $app->request->put('image');
    $first_name = $app->request->put('first_name');
    $last_name = $app->request->put('last_name');

    $db = new DbHandler();
    $response = array();

    // updating task
    $result = $db->updateProffesional($proff_id, $proff_name, $email, $cell_no, $national_id, $location, $image, $first_name, $last_name);
    if ($result) {
        // task updated successfully
        $response["error"] = false;
        $response["message"] = "Task updated successfully";
    } else {
        // task failed to update
        $response["error"] = true;
        $response["message"] = "Task failed to update. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * Deleting proffesional. proffesional can delete only their profile
 * method DELETE
 * url /proffesionals
 */
$app->delete('/proffesionals/:id', 'authenticate', function($proff_id) use($app) {
    global $proff_id;

    $db = new DbHandler();
    $response = array();
    $result = $db->deleteproffesional($proff_id);
    if ($result) {
        // task deleted successfully
        $response["error"] = false;
        $response["message"] = "Task deleted succesfully";
    } else {
        // task failed to delete
        $response["error"] = true;
        $response["message"] = "Task failed to delete. Please try again!";
    }
    echoRespnse(200, $response);
});

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    $emailB = filter_var($email, FILTER_SANITIZE_EMAIL);

    if (filter_var($emailB, FILTER_VALIDATE_EMAIL) === false || $emailB != $email)
    {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();
?>