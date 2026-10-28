<?php
$dsn='mysql:host=127.0.0.1;dbname=clearance_db;charset=utf8mb4';
try {$pdo=new PDO($dsn,'root','',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);}
catch(PDOException $e){http_response_code(500);exit('Database connection failed. Check config/db.php and import database/clearance_db.sql.');}
