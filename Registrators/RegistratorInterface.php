<?php

interface RegistratorInterface
{
    /**
     * @param Client $client
     *
     * @throws RegistratorException
     */
    public function save(Client $client) : void;

    /**
     * @param array $clients
     */
    public function saveAll(array $clients) : void;

    /**
     * @param Client $old
     * @param Client $new
     *
     * @throws RegistratorException
     */
    public function edit(Client $old, Client $new) : void;

    /**
     * @param Client $client
     */
    public function delete(Client $client) : void;

    /**
     * @return array
     */
    public function listClients() : array;

    /**
     * @param string $email
     *
     * @return Client|null
     */
    public function findByEmail(string $email) : ?Client;
}