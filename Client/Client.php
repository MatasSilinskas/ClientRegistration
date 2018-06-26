<?php

class Client
{
    private $firstname;
    private $lastname;
    private $email;
    private $phonenumber1;
    private $phonenumber2;
    private $comment;

    private $emailValidator;
    private $phoneNumberValidator;

    /**
     * Client constructor.
     *
     * @param Validator $emailValidator
     * @param Validator $phoneNumberValidator
     */
    public function __construct(Validator $emailValidator, Validator $phoneNumberValidator)
    {
        $this->emailValidator = $emailValidator;
        $this->phoneNumberValidator = $phoneNumberValidator;
    }


    /**
     * @return string
     */
    public function getFirstname() : string
    {
        return $this->firstname;
    }

    /**
     * @param $firstname
     *
     * @return Client
     */
    public function setFirstname(string $firstname) : self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname() : string
    {
        return $this->lastname;
    }

    /**
     * @param $lastname
     *
     * @return Client
     */
    public function setLastname(string $lastname) : self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail() : string
    {
        return $this->email;
    }

    /**
     * @param $email
     *
     * @return Client
     * @throws ClientException
     */
    public function setEmail(string $email) : self
    {
        if (!$this->emailValidator->validate($email)) {
            throw new ClientException('Email is not valid!');
        }

        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhonenumber1() : string
    {
        return $this->phonenumber1;
    }

    /**
     * @param $phonenumber1
     *
     * @return Client
     * @throws ClientException
     */
    public function setPhonenumber1(string $phonenumber1) : self
    {
        if (!$this->phoneNumberValidator->validate($phonenumber1)) {
            throw new ClientException('First phone number is not valid!');
        }
        $this->phonenumber1 = $phonenumber1;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhonenumber2() : string
    {
        return $this->phonenumber2;
    }

    /**
     * @param $phonenumber2
     *
     * @return Client
     * @throws ClientException
     */
    public function setPhonenumber2(string $phonenumber2) : self
    {
        if ($phonenumber2 !== '') {
            if (!$this->phoneNumberValidator->validate($phonenumber2)) {
                throw new ClientException('Second phone number is not valid!');
            }

            $this->phonenumber2 = $phonenumber2;
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getComment() : ?string
    {
        return $this->comment;
    }

    /**
     * @param $comment
     *
     * @return Client
     */
    public function setComment(string $comment) : self
    {
        $this->comment = $comment;

        return $this;
    }

    public function __toString()
    {
        $info = 'Client: ' . $this->firstname . ' ' . $this->lastname .
            "\nEmail: " . $this->email .
            "\nPhone numbers: " . $this->phonenumber1;

        if ($this->phonenumber2 !== null) {
            $info .= ' ' . $this->phonenumber2;
        }

        if ($this->comment !== null) {
            $info .= "\nComment: " . $this->comment;
        }

        return $info;
    }
}