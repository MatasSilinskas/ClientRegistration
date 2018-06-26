<?php

require_once 'RegistratorInterface.php';
require_once 'RegistratorException.php';

class FileRegistrator implements RegistratorInterface
{
    const FILENAME = 'clients';
    const SEPARATOR = '<!-- E -->';

    /**
     * FileRegistrator constructor.
     */
    public function __construct()
    {
        if (!file_exists(self::FILENAME)) {
            fopen(self::FILENAME, 'wb');
        }
    }

    /**
     * @param Client $client
     *
     * @throws RegistratorException
     */
    public function save(Client $client) : void
    {
        if ($this->findByEmail($client->getEmail()) !== null) {
            throw new RegistratorException('Client with such email already exists!');
        }

        $this->appendFile($this->serializeClient($client));
    }

    /**
     * @param array $clients
     */
    public function saveAll(array $clients): void
    {
        $data = '';
        foreach ($clients as $client) {
            if ($this->findByEmail($client->getEmail()) !== null) {
                trigger_error(
                    'Client has been skipped because a client with such email already exists',
                    E_USER_WARNING
                );
                continue;
            }
            $data .= $this->serializeClient($client);
        }

        $this->appendFile($data);
    }

    /**
     * @param Client $old
     * @param Client $new
     *
     * @throws RegistratorException
     */
    public function edit(Client $old, Client $new) : void
    {
        if ($new->getEmail() !== $old->getEmail() && $this->findByEmail($new->getEmail()) !== null) {
            throw new RegistratorException('Client with such email already exists!');
        }

        $data = file_get_contents(self::FILENAME);
        $data = str_replace(serialize($old), serialize($new), $data);
        file_put_contents(self::FILENAME, $data, LOCK_EX);
    }

    /**
     * @param Client $client
     */
    public function delete(Client $client) : void
    {
        $data = file_get_contents(self::FILENAME);
        $data = str_replace(serialize($client) . self::SEPARATOR, '', $data);
        file_put_contents(self::FILENAME, $data, LOCK_EX);
    }

    /**
     * @return array
     */
    public function listClients(): array
    {
        $data = file_get_contents(self::FILENAME);
        $data = explode(self::SEPARATOR, $data);
        array_pop($data);

        $clients = [];
        foreach ($data as $i => $iValue) {
            $clients[$i] = unserialize($data[$i], ['allowed_classes' => true]);
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
        /**
         * @var Client $client
         */
        foreach ($this->listClients() as $client) {
            if ($client->getEmail() === $email) {
                return $client;
            }
        }

        return null;
    }

    /**
     * @param string $data
     */
    private function appendFile(string $data) : void
    {
        file_put_contents(self::FILENAME, $data,FILE_APPEND | LOCK_EX);
    }

    /**
     * @param Client $client
     *
     * @return string
     */
    private function serializeClient(Client $client) : string
    {
        return serialize($client) . self::SEPARATOR;
    }
}