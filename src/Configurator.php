<?php

namespace Rancoud\Database;

use Exception;
use PDO;

/**
 * Class Configurator.
 */
class Configurator
{
    protected $engine;

    protected $host;

    protected $user;

    protected $password;

    protected $database;

    protected $parameters;

    protected $saveQueries = false;

    protected $permanentConnection = false;

    protected $reportError = 'exception';

    protected $charset = 'utf8';

    /**
     * DatabaseConfigurator constructor.
     *
     * @param array $settings
     *
     * @throws \Exception
     */
    public function __construct(array $settings)
    {
        $this->setMandatorySettings($settings);

        if (array_key_exists('save_queries', $settings)) {
            $this->saveQueries = (bool) $settings['save_queries'];
        }

        if (array_key_exists('permanent_connection', $settings)) {
            $this->permanentConnection = (bool) $settings['permanent_connection'];
        }

        if (array_key_exists('report_error', $settings)) {
            $this->setReportError($settings['report_error']);
        }

        if (array_key_exists('charset', $settings)) {
            $this->setCharset($settings['charset']);
        }

        if (!is_array($settings['parameters'])) {
            throw new Exception('"parameters" settings is not an array: ' . gettype($settings['parameters']), 20);
        }

        $this->parameters = $settings['parameters'];
    }

    /**
     * @param array $settings
     *
     * @throws Exception
     */
    private function setMandatorySettings(array $settings)
    {
        $props = ['engine', 'host', 'user', 'password', 'database'];
        foreach ($props as $prop) {
            if (!isset($settings[$prop]) || !is_string($settings[$prop])) {
                throw new Exception('"' . $prop . '" settings is not defined or not a string', 10);
            }

            $this->{'set' . ucfirst($prop)}($settings[$prop]);
        }
    }

    /**
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param string $engine
     *
     * @throws Exception
     */
    public function setEngine(string $engine)
    {
        $enginesAvailables = PDO::getAvailableDrivers();
        if (!in_array($engine, $enginesAvailables, true)) {
            throw new Exception('The engine "' . $engine . '" is not available for PDO', 20);
        }

        $this->engine = $engine;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user)
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @param string $database
     */
    public function setDatabase(string $database)
    {
        $this->database = $database;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * @return array
     */
    public function getParametersForPDO()
    {
        $parameters = $this->getParameters();

        if ($this->getEngine() === 'mysql') {
            $parameters[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $this->getCharset();
        }

        if ($this->getReportError() === 'silent') {
            $parameters[PDO::ATTR_ERRMODE] = PDO::ERRMODE_SILENT;
        } elseif ($this->getReportError() === 'exception') {
            $parameters[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        }

        $parameters[PDO::ATTR_PERSISTENT] = $this->permanentConnection;

        return $parameters;
    }

    /**
     * @return bool
     */
    public function hasSaveQueries()
    {
        return $this->saveQueries;
    }

    public function enableSaveQueries()
    {
        $this->saveQueries = true;
    }

    public function disableSaveQueries()
    {
        $this->saveQueries = false;
    }

    /**
     * @return string
     */
    public function getReportError()
    {
        return $this->reportError;
    }

    /**
     * @param string $reportError
     *
     * @throws Exception
     */
    public function setReportError(string $reportError)
    {
        if (!in_array($reportError, ['silent', 'exception'], true)) {
            throw new Exception('The report error "' . $reportError . '" is incorrect. (silent , exception)', 30);
        }

        $this->reportError = $reportError;
    }

    /**
     * @return bool
     */
    public function hasThrowException()
    {
        return $this->reportError === 'exception';
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     */
    public function setCharset(string $charset)
    {
        $this->charset = $charset;
    }
}
