<?php
class CategoryName{
	private $id;
	private $name;
	private $parentId;

	public function setId($id){
		$this->id = $id;
	}

	public function getId(){
		return $this->id;
	}

	public function setName($name){
		$this->name = $name;
	}

	public function getName(){
		return $this->name;
	}

	public function setParentId($parentId){
		$this->parentId = $parentId;
	}

	public function getParentId(){
		return $this->parentId;
	}
}
