<?php
chdir(dirname(dirname(__FILE__)));
require_once("DBAccess.php");
$db = new DBAccess();
chdir(dirname(__FILE__));