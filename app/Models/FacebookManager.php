<?php

namespace App\Models;

use GrahamCampbell\Manager\AbstractManager;
use Illuminate\Contracts\Config\Repository;
use App\Factories\FacebookFactory;

/**
 * Facebook manager class.
 */
class FacebookManager extends AbstractManager
{
    /**
     * The factory instance.
     *
     * @var \App\Factories\FacebookFactory
     */
    private $_factory;

    /**
     * Constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \App\Factories\FacebookFactory $factory
     */
    public function __construct(Repository $config, FacebookFactory $factory)
    {
        parent::__construct($config);
        $this->_factory = $factory;
    }

    /**
     * Get the factory instance.
     *
     * @return FacebookFactory
     */
    public function getFactory()
    {
        return $this->_factory;
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    protected function getConfigName()
    {
        return 'facebook';
    }

    /**
     * Create the connection instance.
     *
     * @param array $config
     *
     * @return \Facebook\Facebook
     */
    protected function createConnection(array $config)
    {
        return $this->_factory->createClient($config);
    }
}