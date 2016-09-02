<?php
$output = ["error"=>"login error.","result"=>false];
require_once("config.php");
$output["error"] = "insertcategory error.";
try{
	if ($_SERVER["REQUEST_METHOD"] === "POST"){
		$input = file_get_contents("php://input");
		$json = @json_decode($input,true);
		if(!isset($json["name"]))
			die(json_encode($output));
		$p_id = $json['parent_id'];
		if(mb_strlen($p_id)<=0)
			$p_id = null;
		$categoryName = new CategoryName();
		$categoryName->setName($json['name']);
		$categoryName->setParentId($p_id);
		if($db->insertCategoryName($categoryName)){
			unset($output["error"]);
			$output["result"] = true;
		}
	}
	throw new Exception("");
}catch(Exception $e){
	die(json_encode($output));
}
