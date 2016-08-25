<?php
class SpotLike{
	private $id;
	private $spotId;


	public function getId(){
		return $this->id;
	}
	public function setId($id){
		$this->id = $id;
	}
	public function getSpotId(){
		return $this->spotId;
	}
	public function setSpotId($spotId){
		$this->spotId = $spotId;
	}
}
