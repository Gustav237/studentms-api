<?php

namespace App\Controllers;

use App\Models\Student;
use Exception;
use PDO;

/**
 * StudentController
 * ------------------
 * A "Controller" is the traffic cop. It:
 *   1. Receives the incoming request (already routed to it)
 *   2. Reads input (query params, JSON body)
 *   3. Talks to the Model to do the actual database work
 *   4. Sends back a JSON response with the correct HTTP status code
 *
 * A Controller should NEVER contain raw SQL. That belongs in the Model.
 */
class StudentController
{
    private Student $studentModel;

    public function __construct(PDO $db)
    {
        $this->studentModel = new Student($db);
    }

    /**
     * index()
     * Handles: GET /students        -> list everyone
     * Handles: GET /students?id=1   -> get one student
     */
    public function index(): void
    {
        if (isset($_GET['id'])) { // /student?id=12
            $this->show((int) $_GET['id']);
            return;
        }else{
            $students = $this->studentModel->getAll();
            $this->respond(200, $students);

        }
    }

    /**
     * show()
     * Handles: GET /students?id=1
     */
    public function show(int $id): void
    {
        $student = $this->studentModel->find($id);

        if (!$student) {
            $this->respond(404, ['error' => 'Student not found']);
            return;
        }

        $this->respond(200, $student);
    }

    /**
     * store()
     * Handles: POST /students
     * Reads a JSON body like:
     * { "first_name": "Jane", "last_name": "Doe", "email": "jane@x.com", "courses": "CS" }
     */
    public function store()
    {
        $data = $this->getJsonInput();

        $errors = $this->validate($data);
        if (!empty($errors)) {
            $this->respond(400, ['errors' => $errors]);
            return;
        }

        if ($this->studentModel->emailExists($data['email'])) {
            $this->respond(400, ['errors' => ['email' => 'Email already in use']]);
            return;
        }

        try {
           $id =  $this->studentModel->create($data);
           
            $student = $this->studentModel->find($id);

            // 201 Created = "your POST worked and a new resource now exists"
            $this->respond(201, $student);
        }catch(Exception $e){
            $this->respond(500, $e->getMessage());
        }
    }
        /*
        reset()
     * Handles: DELETE /students
     * Reads a JSON body like:
     * { "confirm": true/false}, so the function is implemented
     * knowingly and not by mistake.
     */

    public function reset(){
        $data = $this->getJsonInput();

        try {
            // var_dump($data);
            if(empty($data)){
                $this->respond(400, ['Success' => false,'Message'=> 'Reset failed. Confirm option Required']);  
            }elseif($data['confirm'] !== 'true'){
                $this->respond(400, ['Success' => false,'Message'=> 'Reset failed. Confirm option Invalid']);  
            }
            else{
                $student = $this->studentModel->reset();
                $this->respond(201, ['Success' => true, 'Message' => "{$student} records have being deleted"]);
            }

        } catch (\Throwable $e) {
            $this->respond(500, $e->getMessage());
        }
    }

    
    /**
     * ---------- Helper methods below ----------
     */

    /**
     * getJsonInput()
     * Reads the raw request body and decodes it from JSON into a PHP array.
     * php://input is a special stream that holds the raw body of the request.
     */
    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    /**
     * validate()
     * Very simple manual validation. In a real framework (Laravel) this
     * would be done with a "Form Request" class, but doing it by hand
     * once helps interns understand what's happening under the hood.
     */
    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is not valid';
        }
        if (empty($data['courses'])) {
            $errors['courses'] = 'courses is required';
        }

        return $errors;
    }

    /**
     * respond()
     * Sends a JSON response with the correct HTTP status code.
     * Centralizing this in one place keeps every endpoint consistent.
     */
    private function respond(int $statusCode, mixed $payload): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}












