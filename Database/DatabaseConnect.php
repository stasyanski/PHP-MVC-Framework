<?php
namespace Database;
/**
 * This file contains the generic code to connect to a database, using constructors
 * it loads the credentials inside db_credentials.ini, changing the credentials there
 * will change
*/
class DatabaseConnect
{
    /**
     * This function has a usage of the credentials class:
     * the Credentials class throws an exception if you pass a file that does not exist, or one that is not a .ini configuration file
     * PHP supports parsing configuration .ini files fully, which is why it was chosen
     * @throws \Exception
     */
    private \DotEnv\DotEnvLoader $credentials;
    public function __construct() {
        $this->credentials = new \DotEnv\DotEnvLoader();
    }
    /**
     * This function makes a PDO connection based on the getCredentials which returns them from the configuration file
     * attempts to validate and establish a connection if not, exceptions are thrown
     * @return \PDO|null returns a PDO instance, or null if the connection fails to be established
     */
    public function initPDO(): ?\PDO {
        try {
            $host = getenv('DB_HOST');
            $database = getenv('DB_NAME');
            $username = getenv('DB_USERNAME');
            $password = getenv('DB_PASSWORD');
            if (!empty($host) && !empty($database) && !empty($username) && !empty($password)) {
                $pdo = new \PDO('mysql:host=' . $host . ';dbname=' . $database . ';charset=utf8', $username, $password);
                // set a higher error reporting mode as it is usually silent
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                return $pdo;
            } else {
                // log error to nginx
                error_log('Error in initPDO() Database credentials are empty, DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD should be checked in DatabaseConnect. ');
                echo '<p>We could not process your request. Please try again later.</p>';
                return null;
            }
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in initPDO() Failed to establish database connection: ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
            return null;
        }
    }
}

