<?php
class SpotImage{
    private $id;
    private $path;


    public function getId(){
        return $this->id;
    }

    public function getPath(){
        return $this->path;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function setPath($path){
        $this->path = $path;
    }
}
?>
