<?php

namespace App;

use Illuminate\Container\Container;

/**
 * Minimal Application wrapper to provide Laravel Application-like features
 * needed by ViewServiceProvider without requiring the full Laravel framework
 */
class Application extends Container
{
    /**
     * The base path for the application installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * The array of terminating callbacks.
     *
     * @var array
     */
    protected $terminatingCallbacks = [];

    /**
     * Create a new Application instance.
     *
     * @param string|null $basePath
     */
    public function __construct($basePath = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->instance('app', $this);
        $this->instance(Container::class, $this);
    }

    /**
     * Set the base path for the application.
     *
     * @param string $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
        return $this;
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @param string $path
     * @return string
     */
    public function basePath($path = '')
    {
        return $this->basePath.($path != '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    /**
     * Register a terminating callback with the application.
     *
     * @param callable $callback
     * @return $this
     */
    public function terminating($callback)
    {
        $this->terminatingCallbacks[] = $callback;
        return $this;
    }

    /**
     * Call the terminating callbacks.
     *
     * @return void
     */
    public function terminate()
    {
        foreach ($this->terminatingCallbacks as $callback) {
            call_user_func($callback);
        }
    }
}

