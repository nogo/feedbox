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
     * Migrate sql files into a database.
     *
     * @param $path, directory with sql files
     * @param array $ignore, filename to ignore
     * @return array of sql queries
     */
    public static function migrate(AbstractConnection $conn, $path, array $ignore = [])
    {
        $result = [];

        if (file_exists($path) && is_dir($path)) {
            $files = scandir($path);
            if ($files !== false) {
                $queries = [];

                foreach ($files as $file) {
                    $fileinfo = pathinfo($path . DIRECTORY_SEPARATOR . $file);
                    if ($fileinfo['extension'] === 'sql'
                        && !in_array($fileinfo['filename'], $ignore)) {

                        $sql = file_get_contents($path . DIRECTORY_SEPARATOR . $file);
                        $queries = array_merge($queries, explode(';', $sql));
                    }
                }

                if (!empty($queries)) {
                    foreach ($queries as $q) {
                        $query = trim($q);
                        if (!empty($query)) {
                            $conn->query($query . ';');
                            $result[] = $query;
                        }
                    }
                }
            }
        }

        return $result;
    }
}