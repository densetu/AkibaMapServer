<?php
$output = ["error"=>"login error.","result"=>false];
require_once("config.php");
$output["error"] = "getspot error.";
try{
	if ($_SERVER["REQUEST_METHOD"] === "GET"){
		$spot_id = isset($_GET["id"]) ? $_GET["id"] : null;
		if($spot_id == null)
			throw new Exception(json_encode($output));
		$spot = $db->getSpot($spot_id);
		if($spot == null)
			throw new Exception(json_encode($output));
		unset($output["error"]);
		$data = [];
		$data["id"] = $spot->getId();
		$data["name"] = $spot->getName();
		$data["address"] = $spot->getAddress();
		$data["description"] = $spot->getDescription();
		$data["lat"] = $spot->getLat();
		$data["lng"] = $spot->getLng();
		$data["user_id"] = $spot->getUserId();
		$output["data"] = [$data];
	}
	throw new Exception(json_encode($output));
}catch(Exception $e){
	die($e->getMessage());
}