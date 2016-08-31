<?php
$output = ["error"=>"register error.","result"=>false];
require_once("register_config.php");
if ($_SERVER["REQUEST_METHOD"] === "POST"){
	$input = file_get_contents("php://input");
	$json = @json_decode($input,true);
	if(!isset($json["name"]))
		die(json_encode($output));
	$userdata = null;
	if(isset($json["token"]) && isset($json["tokenSecret"]) && isset($json["service"]))
		$userdata = new ServiceLoginUserData($json["token"],$json["tokenSecret"],$json["service"]);
	else if(isset($json["email"]) && isset($json["password"]))
		$userdata = new EmailLoginUserData($json["email"],$json["password"]);
	else
		die(json_encode($output));
	if($db->isUserByUserData($userdata)){
		unset($output["error"]);
		$output["result"] = true;
	}else if($db->insertUser(new User(-1,$json["name"],$userdata,0))){
		unset($output["error"]);
		$output["result"] = true;
	}
}
die(json_encode($output));