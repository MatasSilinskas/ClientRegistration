<?php

require_once 'Registrators/FileRegistrator.php';
require_once 'Registrators/DatabaseRegistrator.php';
require_once 'Client/Client.php';
require_once 'Client/ClientException.php';
require_once 'Validators/EmailValidator.php';
require_once 'Validators/PhoneNumberValidator.php';

$messages = [
    'First name' => 'Enter client`s first name: ',
    'Last name' => 'Enter client`s last name: ',
    'Email' => 'Enter client`s email: ',
    'First phone number' => 'Enter client`s phone number: ',
    'Second phone number' => 'Enter client`s second phone number: ',
    'Comment' => 'Enter the comment: '
];


$emailValidator = new EmailValidator();
$phoneNumberValidator = new PhoneNumberValidator();

$handle = fopen("php://stdin", 'rb');
echo 'Hello. This is a simple client registration system. Where would you like to store your data?[FILE/database]';
if (getConsoleInput($handle) === 'database') {
    $registrator = new DatabaseRegistrator();
} else {
    $registrator = new FileRegistrator();
}
listAvailableOptions();
do {
    echo 'Enter the number of action you`d like to do: ';
    $option = getConsoleInput($handle);
    switch ($option) {
        case 1:
            $client = new Client($emailValidator, $phoneNumberValidator);
            for ($i = 0; $i < 4; $i++) {
                echo $messages[array_keys($messages)[$i]];
                updateField($client, array_keys($messages)[$i], $handle);
            }
            echo 'Does client have a second phone number?[N/y]';
            if (getConsoleInput($handle) === 'y') {
                echo $messages['Second phone number'];
                updateField($client, 'Second phone number', $handle);
            }
            echo 'Would you like to add a comment?[N/y]';
            if (getConsoleInput($handle) === 'y') {
                echo $messages['Comment'];
                updateField($client, 'Comment', $handle);
            }
            $registrator->save($client);
            echo "Client was added succesfully!\n";
            break;
        case 2:
            $oldClient = getClientByEmail($registrator, 'Enter client`s email that you`d like to change: ', $handle);
            echo 'Choose the fields that you want to change ' .
                "(you can choose multiple fields by separating choices with ', '):\n";
            $fields = array_keys($messages);
            foreach ($fields as $int => $field) {
                echo ++$int . '. ' . $field . "\n";
            }
            $areChoicesCorrect = false;
            while (!$areChoicesCorrect) {
                $choices = explode(', ', getConsoleInput($handle));
                $areChoicesCorrect = true;
                foreach ($choices as $choice) {
                    if ($choice > count($fields)) {
                        $areChoicesCorrect = false;
                        echo 'Some of your entered choices do not exist. Please enter existing choices: ';
                        continue;
                    }
                }
            }
            $newClient = clone $oldClient;
            foreach ($choices as $choice) {
                echo $messages[$fields[$choice - 1]] . "\n";
                updateField($newClient, array_keys($messages)[$choice - 1], $handle);
            }
            $registrator->edit($oldClient, $newClient);
            echo "Client`s info was updated succesfully!\n";
            break;
        case 3:
            $client = getClientByEmail($registrator, 'Enter the email of a client that you`d like to delete: ', $handle);
            $registrator->delete($client);
            echo "Client was deleted succesfully!\n";
            break;
        case 4:
            foreach ($registrator->listClients() as $client) {
                echo $client;
                echo "\n----------------------\n";
            }
            break;
        case 5:
            if (($csvHandle = fopen("client_examples.csv", "r")) !== FALSE) {
                $fields = fgetcsv($csvHandle, 1000, ",");
                $clients = [];
                while (($data = fgetcsv($csvHandle, 1000, ",")) !== FALSE) {
                    $client = new Client($emailValidator, $phoneNumberValidator);
                    try {
                        foreach ($data as $key => $value) {
                            updateClient($client, $fields[$key], $value);
                        }
                        $clients[] = $client;
                    } catch (ClientException $exception) {
                        echo "A client has been skipped because it has incorrect parameters\n";
                        continue;
                    }
                }
                $registrator->saveAll($clients);
                fclose($csvHandle);
                echo "Client import is complete!\n";
            }
            break;
        case 9:
            fclose($handle);
            echo 'Goodbye! Thanks for using the system :)';
            exit;
        default:
            echo 'Such option doesn`t exist. Here`s a list to remind you of available options:';
            listAvailableOptions();
    }
} while (true);

function getConsoleInput($handle) {
    return substr(fgets($handle), 0, -1);
}

function listAvailableOptions() {
    echo "\n1. Add a new client";
    echo "\n2. Edit client`s data";
    echo "\n3. Delete a client";
    echo "\n4. List all clients";
    echo "\n5. Import a .csv file";
    echo "\n9. Exit\n";
}

/**
 * @param Client $client
 * @param string $field
 * @param string $value
 *
 * @throws ClientException
 */
function updateClient(Client $client, string $field, string $value) {
    if ($field === 'First name') {
        $client->setFirstname($value);
    } elseif ($field === 'Last name') {
        $client->setLastname($value);
    } elseif ($field === 'Email') {
        $client->setEmail($value);
    } elseif ($field === 'First phone number') {
        $client->setPhonenumber1($value);
    } elseif ($field === 'Second phone number') {
        $client->setPhonenumber2($value);
    } elseif ($field === 'Comment') {
        $client->setComment($value);
    }
}

/**
 * @param RegistratorInterface $registrator
 * @param string               $firstMessage
 * @param                      $handle
 *
 * @return Client
 */
function getClientByEmail(RegistratorInterface $registrator, string $firstMessage, $handle) : Client {
    $i = 0;
    $client = null;
    while ($client === null) {
        if ($i === 0) {
            echo $firstMessage;
            $i = 1;
        } else {
            echo 'A client with such email doesn`t exist. Please enter an existing email: ';
        }

        $client = $registrator->findByEmail(getConsoleInput($handle));
    }

    return $client;
}

/**
 * @param Client $client
 * @param string $field
 * @param        $handle
 */
function updateField(Client $client, string $field, $handle) {
    try {
        updateClient($client, $field, getConsoleInput($handle));
    } catch (ClientException $exception) {
        echo 'The value is incorrect. Enter a new one: ';
        updateField($client, $field, $handle);
    }
}