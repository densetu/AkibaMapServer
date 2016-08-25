<?php
require_once("config.php");
if ($_SERVER["REQUEST_METHOD"] === "POST"){
	$db = new DBAccess();
}
?>