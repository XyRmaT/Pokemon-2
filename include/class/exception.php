<?php

class CustomException extends Exception {
    public function __construct(string $message = '', string $code = '', Exception $previous = NULL) {
        parent::__construct($message, $code, $previous);
    }
}