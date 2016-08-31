<?php
require_once("config.php");
function success($output,$data){
	$output["id"] = $data->getId();
	$output["name"] = $data->getName();
	$output["admin"] = $data->getAdmin();
	unset($output["error"]);
	$output["result"] = true;
	return $output;
}
$output = ["error"=>"login error.","result"=>false];
if ($_SERVER["REQUEST_METHOD"] === "POST"){
	$input = file_get_contents("php://input");
	$json = @json_decode($input,true);
	$userdata = null;
	if(isset($_SESSION["id"])){
		$data = $db->getUser($_SESSION["id"]);
		if($data !== null){
			$output = success($output,$data);
			die(json_encode($output));
		}
	}else if(isset($json["token"]) && isset($json["tokenSecret"]) && isset($json["service"]))
		$userdata = new ServiceLoginUserData($json["token"],$json["tokenSecret"],$json["service"]);
	else if(isset($json["email"]) && isset($json["password"]))
		$userdata = new EmailLoginUserData($json["email"],$json["password"]);
	else
		die(json_encode($output));
	$data = $db->getUserByUserData($userdata);
	if($data !== null){
		$output = success($output,$data);
		$_SESSION["id"] = $data->getId();
	}
}
die(json_encode($output));