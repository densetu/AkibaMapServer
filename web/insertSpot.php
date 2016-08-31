<?php
$output = ["error"=>"login error.","result"=>false];
require_once("config.php");
$output["error"] = "insertspot error.";
if ($_SERVER["REQUEST_METHOD"] === "POST"){
	$input = file_get_contents("php://input");
	$json = @json_decode($input,true);
	if(!isset($json["name"]))
		die(json_encode($output));
	$spot = new Spot();
	$spot->setName($json['name']);
	$spot->setAddress($json['address']);
	$spot->setDescription($json['description']);
	$spot->setLat($json['lat']);
	$spot->setLng($json['lng']);
	$spot->setUserId($_SESSION["id"]);
	if($db->insertSpot($spot)){
		unset($output["error"]);
		$output["result"] = true;
	}
}
die(json_encode($output));