<?php
namespace Nogo\Feedbox\Helper;

use Aura\Sql\Connection\AbstractConnection;
use Aura\Sql\ConnectionFactory;

class DatabaseConnector
{
    /**
     * @var ConnectionFactory
     */
    protected $factory;
    /**
     * @var string
     */
    protected $adapter = '';
    /**
     * @var string
     */
    protected $dsn = '';
    /**
     * @var string
     */
    protected $username = '';
    /**
     * @var string
     */
    protected $password = '';

    /**
     * Constructor
     *
     * @param $adapter
     * @param $dsn
     * @param $username
     * @param $password
     */
    public function __construct($adapter, $dsn, $username, $password)
    {
        $this->factory = new ConnectionFactory();

        $this->adapter = $adapter;
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get a database connection
     *
     * @return AbstractConnection
     */
    public function getInstance()
    {
        return $this->factory->newInstance(
            $this->adapter,
            $this->dsn,
            $this->username,
            $this->password
        );
    }

    /**
     * @return string
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param string $adapter
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return string
     */
    public function getDsn()
    {
        return $this->dsn;
    }

    /**
     * @param string $dsn
     */
    public function setDsn($dsn)
    {
        $this->dsn = $dsn;
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
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Load a sql file
     *
     * @param AbstractConnection $conn
     * @param $file
     */
    public static function loadSqlFile(AbstractConnection $conn, $file)
    {
        if (file_exists($file)) {
            $sql = file_get_contents($file);

            if (!empty($sql)) {
                $queries = explode(';', $sql);
                foreach ($queries as $q) {
                    $conn->query(trim($q) . ";");
                }
            }
        }
    }

}