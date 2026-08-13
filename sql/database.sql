-- create a students table 
-- with id, 
-- first_name, last_name, email,
-- course, and created_at


CREATE DATABASE IF NOT EXISTS studentms;

USE studentms;

CREATE TABLE students (
    id int AUTO_INCREMENT PRIMARY NOT NULL,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    email VARCHAR(255) UNIQUE NOT NULL,
    courses text,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

