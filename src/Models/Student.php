<?php

namespace App\Models;

use PDO;

/**
 * Student (Model)
 * ---------------
 * A "Model" represents ONE thing in our database (here: a student)
 * and holds every SQL query related to that thing.
 *
 * Rule of thumb we teach interns: "If it touches SQL, it belongs in
 * a Model, not in a Controller."
 */
class Student
{
    // The model holds a reference to the PDO connection so every
    // method below can use it without recreating the connection.
    private PDO $db;

    // Constructor: runs automatically when we do `new Student($pdo)`.
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * getAll()
     * Fetch every student, most recently added first.
     */
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM students ORDER BY id DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * find()
     * Fetch a single student by id.
     * Returns an associative array, or false if not found.
     *
     * We use a PREPARED STATEMENT here: the "?" is a placeholder,
     * and we bind the real value separately with execute([$id]).
     * This is what stops SQL injection — the value is NEVER
     * concatenated directly into the SQL string.
     */
    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM students WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * emailExists()
     * Helper used before inserting/updating, since the email column
     * is UNIQUE in the database.
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $stmt = $this->db->prepare("SELECT * FROM Students WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if($data){
            return true;
        }else{
            return false;
        }
    }

    /**
     * reset()
     * Delete all records in the students table
     * Returns the number of records deleted
     * Carefull calling this function as it could clear all the data
     * in the students' table.
     */
    public function reset(): int{
        $stmt = $this->db->prepare("DELETE FROM Students");
        $stmt->execute();
        return $stmt->rowCount(); 
    }

    
    /**
     * create()
     * Insert a new student. Returns the newly created student's id.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO students (first_name, last_name, email, courses)
             VALUES (:first_name, :last_name, :email, :courses)'
        );

        // Named placeholders (:first_name) are the same idea as "?"
        // placeholders, just more readable when there are many fields.
        $stmt->execute([
            ':first_name' => $data['first_name'],
            ':last_name'  => $data['last_name'],
            ':email'      => $data['email'],
            ':courses'     => $data['courses'],
        ]);

        // lastInsertId() asks MySQL "what id did you just generate?"
        return $this->db->lastInsertId();
    }

}