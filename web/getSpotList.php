<?php
$output = ["error"=>"login error.","result"=>false];
require_once("config.php");
$output["error"] = "getspot error.";
try{
	if ($_SERVER["REQUEST_METHOD"] === "GET"){
		$spotList = $db->getSpotList();
		if($spotList == null)
			throw new Exception(json_encode($output));
		unset($output["error"]);
		$output["data"] = [];
		foreach($spotList as $spot){
			$data = [];
			$data["id"] = $spot->getId();
			$data["name"] = $spot->getName();
			$data["address"] = $spot->getAddress();
			$data["description"] = $spot->getDescription();
			$data["lat"] = $spot->getLat();
			$data["lng"] = $spot->getLng();
			$data["user_id"] = $spot->getUserId();
			$output["data"][] = $data;
		}
	}
	throw new Exception(json_encode($output));
}catch(Exception $e){
	die($e->getMessage());
}