CREATE DATABASE IF NOT EXISTS clearance_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clearance_db;

-- WARNING: importing this file drops the existing project tables and their data.
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS clearance_requests;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS offices;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE offices (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255)
);
CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_no VARCHAR(30) NOT NULL UNIQUE,
  full_name VARCHAR(150) NOT NULL,
  department VARCHAR(120) NOT NULL,
  year_level TINYINT UNSIGNED NOT NULL DEFAULT 4,
  email VARCHAR(190) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','office','student') NOT NULL,
  office_id INT NULL,
  student_id INT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE SET NULL,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL
);
CREATE TABLE clearance_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  office_id INT NOT NULL,
  status ENUM('Pending','Cleared','Rejected') NOT NULL DEFAULT 'Pending',
  student_note VARCHAR(500),
  office_note VARCHAR(500),
  reviewed_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_student_office (student_id, office_id),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO offices (id,name,description) VALUES
(1,'Library','Library clearance'),
(2,'Finance','Fee clearance'),
(3,'Department','Academic clearance'),
(4,'Proctor Office','Student conduct and disciplinary clearance'),
(5,'Cafe Office','Cafe / cafeteria clearance'),
(6,'Registrar Office','Registrar and student-record clearance');

INSERT INTO students (id,student_no,full_name,department,year_level,email)
VALUES (1,'AUW-IS-2022-001','Demo Student','Information Systems',4,'student@campus.local');

-- This bcrypt hash is for the demo password: password
INSERT INTO users (id,name,email,password_hash,role,office_id,student_id) VALUES
(1,'System Administrator','admin@campus.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin',NULL,NULL),
(2,'Library Officer','library@campus.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','office',1,NULL),
(3,'Finance Officer','finance@campus.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','office',2,NULL),
(4,'Department Officer','department@campus.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','office',3,NULL),
(5,'Proctor Officer','proctor@campus.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','office',4,NULL),
(6,'Cafe Officer','cafe@campus.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','office',5,NULL),
(7,'Registrar Officer','registrar@campus.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','office',6,NULL),
(8,'Demo Student','student@campus.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','student',NULL,1);

INSERT INTO clearance_requests (student_id,office_id,status,student_note) VALUES
(1,1,'Pending','Please review'),
(1,2,'Pending','Please review'),
(1,3,'Pending','Please review'),
(1,4,'Pending','Please review'),
(1,5,'Pending','Please review'),
(1,6,'Pending','Please review');
