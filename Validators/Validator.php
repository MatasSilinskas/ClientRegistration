<?php

interface Validator
{
    public function validate(string $data) : bool;
}