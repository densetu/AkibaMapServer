<?php
chdir(dirname(dirname(__FILE__)));
header("Content-type: application/json; charset=UTF-8");
session_name("login_session");
session_start();
require_once("DBAccess.php");
$db = new DBAccess();
chdir(dirname(__FILE__));