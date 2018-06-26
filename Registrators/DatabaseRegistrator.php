<?php

require_once 'RegistratorInterface.php';
require_once 'RegistratorException.php';
require_once './Validators/EmailValidator.php';
require_once './Validators/PhoneNumberValidator.php';
require_once './Validators/Validator.php';

class DatabaseRegistrator implements RegistratorInterface
{
    private $db_host = "localhost";
    private $db_user = "root";
    private $db_pass = "password";
    private $db_name = "ClientRegistration";
    public static $database;

    /**
     * DatabaseRegistrator constructor.
     */
    public function __construct()
    {
        if (DatabaseRegistrator::$database === null ) {
            DatabaseRegistrator::$database = new mysqli($this->db_host, $this->db_user, $this->db_pass);
            DatabaseRegistrator::$database->query("CREATE DATABASE IF NOT EXISTS $this->db_name");
            DatabaseRegistrator::$database->query("USE $this->db_name");

            $sql = "CREATE TABLE IF NOT EXISTS Clients (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
            firstname VARCHAR(30) NOT NULL,
            lastname VARCHAR(30) NOT NULL,
            email VARCHAR(50) UNIQUE NOT NULL,
            phonenumber1 CHAR(12) NOT NULL,
            phonenumber2 CHAR(12),
            comment TEXT)";

            if (DatabaseRegistrator::$database->query($sql) !== TRUE) {
                throw new RegistratorException('Something went wrong. Check if the database is configured properly');
            }
        }
    }

    /**
     * @param Client $client
     *
     * @throws RegistratorException
     */
    public function save(Client $client): void
    {
        $sql = "INSERT INTO Clients(firstname, lastname, email, phonenumber1, phonenumber2, comment) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = DatabaseRegistrator::$database->prepare($sql);

        $fields = $client->convertToAssocArray();
        $stmt->bind_param("ssssss", $fields['firstname'],  $fields['lastname'], $fields['email'],
            $fields['phonenumber1'], $fields['phonenumber2'], $fields['comment']);
        if (!$stmt->execute()) {
            throw new RegistratorException('Client with such email already exists!');
        };
    }

    /**
     * @param array $clients
     */
    public function saveAll(array $clients): void
    {
        $sql = "INSERT INTO Clients(firstname, lastname, email, phonenumber1, phonenumber2, comment) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = DatabaseRegistrator::$database->prepare($sql);

        foreach ($clients as $client) {
            $fields = $client->convertToAssocArray();
            $stmt->bind_param("ssssss", $fields['firstname'],  $fields['lastname'], $fields['email'],
                $fields['phonenumber1'], $fields['phonenumber2'], $fields['comment']);
            if (!$stmt->execute()) {
                trigger_error(
                    'Client has been skipped because a client with such email already exists',
                    E_USER_WARNING
                );
            };
        }
    }

    /**
     * @param Client $old
     * @param Client $new
     *
     * @throws RegistratorException
     */
    public function edit(Client $old, Client $new): void
    {
        $sql = "UPDATE Clients SET firstname = ?, lastname = ?, email = ?, " .
            "phonenumber1 = ?, phonenumber2  = ?, comment = ? WHERE email = ?";
        $stmt = DatabaseRegistrator::$database->prepare($sql);

        $fields = $new->convertToAssocArray();
        $email = $old->getEmail();

        $stmt->bind_param("sssssss", $fields['firstname'],  $fields['lastname'], $fields['email'],
            $fields['phonenumber1'], $fields['phonenumber2'], $fields['comment'], $email);

        if (!$stmt->execute()) {
            throw new RegistratorException('Client doesn`t exist or the email is already taken.');
        };
    }

    /**
     * @param Client $client
     *
     * @throws RegistratorException
     */
    public function delete(Client $client): void
    {
        $stmt = DatabaseRegistrator::$database->prepare("DELETE FROM Clients WHERE email = ?");
        $email = $client->getEmail();
        $stmt->bind_param('s', $email);
        if (!$stmt->execute()) {
            throw new RegistratorException('Client with such email doesn`t exist!');
        };
    }

    /**
     * @return array
     */
    public function listClients(): array
    {
        $stmt = DatabaseRegistrator::$database->prepare("SELECT * FROM Clients");
        $stmt->execute();

        $clients = [];
        $result = $stmt->get_result();
        $emailValidator = new EmailValidator();
        $phoneNumberValidator = new PhoneNumberValidator();

        while ($row = $result->fetch_assoc())
        {
            $clients[] = $this->convertRowToClient($row, $emailValidator, $phoneNumberValidator);
        }

        return $clients;
    }

    /**
     * @param string $email
     *
     * @return Client|null
     */
    public function findByEmail(string $email): ?Client
    {
        $stmt = DatabaseRegistrator::$database->prepare("SELECT * FROM Clients WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result === null) {
            return $result;
        }

        return $this->convertRowToClient($result, new EmailValidator(), new PhoneNumberValidator());
    }

    private function convertRowToClient(
        array $row,
        Validator $emailValidator,
        Validator $phoneNumberValidator
    ) : Client {
        $client = new Client($emailValidator, $phoneNumberValidator);
        $client->setFirstname($row['firstname'])
            ->setLastname($row['lastname'])
            ->setEmail($row['email'])
            ->setPhonenumber1($row['phonenumber1'])
            ->setPhonenumber2($row['phonenumber2'])
            ->setComment($row['comment']);
        return $client;
    }
}