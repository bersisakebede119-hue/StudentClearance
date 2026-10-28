<?php
session_start();require __DIR__.'/config/db.php';require __DIR__.'/includes/auth.php';
if(!empty($_SESSION['user'])){header('Location: dashboard.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $q=$pdo->prepare('SELECT id,name,email,password_hash,role,office_id,student_id FROM users WHERE email=? AND is_active=1');$q->execute([trim($_POST['email']??'')]);$u=$q->fetch();
 if($u&&password_verify($_POST['password']??'',$u['password_hash'])){session_regenerate_id(true);unset($u['password_hash']);$_SESSION['user']=$u;header('Location: dashboard.php');exit;} $error='Email ykn password sirrii miti.';
}
$pageTitle='Login';require __DIR__.'/includes/header.php';?>
<section class="login"><p class="eyebrow"><h1>UNIVERSITY STUDENTS <h1></p><h1>Student Clearance System</h1><p><H1>LOGIN IN <H1></p><?php if($error):?><p class="error"><?=h($error)?></p><?php endif;?>
<form method="post"><label>Email</label><input type="email" name="email" required><label>Password</label><input type="password" name="password" required><button><H1>LOGIN IN<H1></button></form>
