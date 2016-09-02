<?php
$output = ["error"=>"login error.","result"=>false];
require_once("config.php");
$output["error"] = "insertspot error.";
try{
	if ($_SERVER["REQUEST_METHOD"] === "POST"){
		$input = file_get_contents("php://input");
		$json = @json_decode($input,true);
		$spot = new Spot();
		$spot->setName($json['name']);
		$spot->setAddress($json['address']);
		$spot->setDescription($json['description']);
		$spot->setLat($json['lat']);
		$spot->setLng($json['lng']);
		$spot->setUserId($_SESSION['id']);
		$images = [];
		foreach ($json['images'] as $image) {
			$spotImage = new SpotImage();
			$spotImage->setPath($image);
			$images[] = $spotImage;
		}
		$spot->setSpotImages($images);
		$categories = [];
		foreach ($json['categories'] as $category) {
			$spotCategory = new SpotCategory();
			$spotCategory->setCategoryId($category);
			$categories[] = $spotCategory;
		}
		$spot->setSpotCategories($categories);
		if($db->insertSpot($spot)){
			unset($output["error"]);
			$output["result"] = true;
		}
	}
	throw new Exception("");
}catch(Exception $e){
	die(json_encode($output));
}
