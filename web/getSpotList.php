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
			$data["id"] = (int)$spot->getId();
			$data["name"] = $spot->getName();
			$data["address"] = $spot->getAddress();
			$data["description"] = $spot->getDescription();
			$data["lat"] = (double)$spot->getLat();
			$data["lng"] = (double)$spot->getLng();
			$data["user_id"] = (int)$spot->getUserId();
			$output["data"][] = $data;
		}
		$output["result"] = true;
	}
	throw new Exception(json_encode($output));
}catch(Exception $e){
	die($e->getMessage());
}