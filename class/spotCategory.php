<?php
class SpotCategory{
	private $spotId;
	private $categoryId;
	

	public function getSpotId(){
		return $this->spotId;
	}

	public function getCategoryId(){
		return $this->categoryId;
	}

	public function setSpotId($spotId){
		$this->spotId = $spotId;
	}

	public function setCategoryId($categoryId){
		$this->categoryId = $categoryId;
	}
}
?>
