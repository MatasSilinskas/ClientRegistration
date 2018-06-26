<?php

require_once 'Validator.php';

class EmailValidator implements Validator
{
    public function validate(string $data) : bool
    {
        return filter_var($data, FILTER_VALIDATE_EMAIL);
    }
}