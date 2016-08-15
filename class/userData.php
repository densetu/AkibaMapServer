<?php
class BaseUserData{
}

class EmailLoginUserData extends BaseUserData{
	private $email;
	private $password;
	public function __construct($email,$password){
		$this->email = $email;
		$this->password = $password;
	}
	
	public function getEmail(){
		return $this->email;
	}
	
	public function getPassword(){
		return $this->password;
	}
	
	public function setEmail($email){
		$this->email = $email;
	}
	
	public function setPassword($password){
		$this->password = $password;
	}
}

class ServiceLoginUserData extends BaseUserData{
	private $token;
	private $tokenSecret;
	private $service;
	
	public function __construct($token,$tokenSecret,$service){
		$this->token = $token;
		$this->tokenSecret = $tokenSecret;
		$this->service = $service;
	}
	
	public function getToken(){
		return $this->token;
	}
	
	public function getTokenSecret(){
		return $this->tokenSecret;
	}
	
	public function getService(){
		return $this->service;
	}
}