<?php

interface ValidatorInterface
{
    public function validate(string $data) : bool;
}