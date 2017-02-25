<?php
/* Require Slim and plugins */
require 'Slim/Slim.php';
require 'plugins/NotORM.php';


/* Register autoloader and instantiate Slim */
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
/* Database Configuration */
$dbhost   = 'localhost';
$dbuser   = 'root';
$dbpass   = '';
$dbname   = 'user';
$dbmethod = 'mysql:dbname=';

$dsn = $dbmethod.$dbname;
$pdo = new PDO($dsn, $dbuser, $dbpass);
$db = new NotORM($pdo);
/* Routes */
$app->get('/', function(){
    echo 'Home - My Slim Application';
});
// Get all users
$app->get('/users', function() use($app, $db){
    $users = array();
    foreach ($db->users() as $user) {
        $users[]  = array(
            'username' => $user['username'],
            'email' => $user['email'],
            'profileName' => $user['profileName']
        );
    }
    $app->response()->header("Content-Type", "application/json");
    echo json_encode($users, JSON_FORCE_OBJECT);
});
//Get a single user
$app->get('/user/:id', function($id) use($app,$db){
    $app->response()->header("Content-Type", "application/json");
    $user = $db->users()->where("id", $id);
    if($data=$user->fetch()){
        echo json_encode(array(
            'username' => $user['username'],
            'email' => $user['email'],
            'profileName' => $user['profileName']
        ));}
    else{
        echo json_encode(array(
            'status' => false,
            'message' => "User ID $id does not exist"
        ));
    }
});

// Add a new user
$app->post('/user', function() use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $user = $app->request()->post();
    $result = $db->users->insert($user);
    echo json_encode(array('username' => $result['username']));
});
// Update a user

$app->put('/user/:id', function($id) use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $user = $db->users()->where("id", $id);
    if ($user->fetch()) {
        $post = $app->request()->put();
        $result = $user->update($post);
        echo json_encode(array(
            "status" => (bool)$result,
            "message" => "User updated successfully"
            ));
    }
    else{
        echo json_encode(array(
            "status" => false,
            "message" => "User id $id does not exist"
        ));
    }
});
// Remove a car
$app->delete('/user/:id', function($id) use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $user = $db->users()->where('id', $id);
    if($user->fetch()){
        $result = $user->delete();
        echo json_encode(array(
            "status" => true,
            "message" => "User deleted successfully"
        ));
    }
    else{
        echo json_encode(array(
            "status" => false,
            "message" => "User id $id does not exist"
        ));
    }
});
/* Run the application */
$app->run();