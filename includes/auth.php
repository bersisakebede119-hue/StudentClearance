<?php
if(session_status()!==PHP_SESSION_ACTIVE) session_start();
function h($v){return htmlspecialchars((string)($v??''),ENT_QUOTES,'UTF-8');}
function require_login(){if(empty($_SESSION['user'])){header('Location: index.php');exit;}}
function require_role($roles){require_login();if(!in_array($_SESSION['user']['role'],$roles,true)){http_response_code(403);exit('Access denied');}}
