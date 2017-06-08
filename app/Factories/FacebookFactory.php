<?php

namespace App\Factories;

use App\Models\Facebook;
use InvalidArgumentException;

/**
 * Facebook factory.
 */
class FacebookFactory
{
    /**
     * Create a new facebook client.
     *
     * @param array $config
     *
     * @return \App\Models\Facebook
     */
    public function createClient(array $config)
    {
        $config = $this->getConfig($config);
        return $this->getClient($config);
    }

    /**
     * Get the configuration data.
     *
     * @param string[] $config
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getConfig(array $config)
    {
        $keys = ['app_id', 'app_secret'];

        foreach ($keys as $key) {
            if (!array_key_exists($key, $config)) {
                throw new InvalidArgumentException("Missing configuration key [$key].");
            }
        }

        return array_only(
            $config, [
            'app_id',
            'app_secret',
            'default_access_token',
            'default_graph_version',
            'enable_beta_mode',
            'http_client_handler',
            'persistent_data_handler',
            'url_detection_handler',
        ]
        );
    }

    /**
     * Get the facebook client.
     *
     * @param array $config
     *
     * @return \App\Models\Facebook
     */
    protected function getClient(array $config)
    {
        return new Facebook($config);
    }
}