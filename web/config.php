<?php
chdir(dirname(dirname(__FILE__)));
require_once("DBAccess.php");
header("Content-type: application/json; charset=UTF-8");
$db = new DBAccess();
chdir(dirname(__FILE__));