<?php

//DBへのアクセスが失敗した時に投げる例外

class AccessException extends Exception{
  public function __construct($message, $code = 0, Exception $previous = null) {
      parent::__construct($message, $code, $previous);
  }
}
