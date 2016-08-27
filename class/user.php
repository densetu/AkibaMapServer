<?php
class User{
	private $id;
	private $name;
	private $admin;
	private $userdata;
	
	public function __construct($id,$name,$userdata,$admin){
		$this->id = $id;
		$this->name = $name;
		if ($userdata instanceof BaseUserData)
			$this->userdata = $userdata;
		$this->admin = $admin;
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function getEmail(){
		return $this->userdata instanceof EmailLoginUserData ? $this->userdata->getEmail() : null;
	}
	
	public function getPassword(){
		return $this->userdata instanceof EmailLoginUserData ? $this->userdata->getPassword() : null;
	}
	
	public function getToken(){
		return $this->userdata instanceof ServiceLoginUserData ? $this->userdata->getToken() : null;
	}
	
	public function getTokenSecret(){
		return $this->userdata instanceof ServiceLoginUserData ? $this->userdata->getTokenSecret() : null;
	}
	
	public function getService(){
		return $this->userdata instanceof ServiceLoginUserData ? $this->userdata->getService() : null;
	}
	
	public function getIsEmailLoginUserData(){
		return $this->userdata instanceof EmailLoginUserData;
	}
	
	public function getAdmin(){
		return $this->admin == 1;
	}
	
	public function setId($id){
		$this->id = $id;
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function setAdmin($admin){
		$this->admin = $admin;
	}
	
	public function setUserData($userdata){
		if ($userdata instanceof BaseUserData)
			$this->userdata = $userdata;
	}
}
