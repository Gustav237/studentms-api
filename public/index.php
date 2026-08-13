<?php 
// Report all PHP errors
error_reporting(E_ALL);

// Force PHP to display errors on the screen
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');


use App\Controllers\StudentController;


// capture the requirest sent
// method
// url/endpoint
// urlparam
// body


header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:GET,POST,PUT,DELETE,OPTIONS');
header('Access-Control-Allow-Headers:Content-Type');

require __DIR__.'/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Database;



$dotenv = Dotenv::createMutable(__DIR__.'./..');
$dotenv->load();

try {
    $db = Database::getConnnection();
    http_response_code(200);
    header("Content-Type:application/json");
    // var_dump($e);
    // echo json_encode(['success' => $db]);
}catch(\Throwable  $e) {
    http_response_code(500);
    header("Content-Type:application/json");
    // var_dump($e);
    echo json_encode(['error' => $e->getMessage()]);
}



$studentController = new StudentController($db);


// var_dump($studentController->index());
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];


$basePath = '/studentapi/public';



if(str_starts_with($path, $basePath)){
    $path = substr($path,strlen($basePath));
}


if($path === ''){
    $path = '/';
}


// echo $path;

// if($path = "students"){
//     echo json_encode(["success" => $path, 'method' => $method]);
// }


if($path === '/students' && $method == 'GET'){
    $studentController->index();
}elseif($path === "/students" && $method === "POST"){
    $studentController->store();
}elseif($path === "/students" && $method === "DELETE"){
    $studentController->reset();
}

