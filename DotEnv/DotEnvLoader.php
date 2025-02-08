<?php
namespace DotEnv;

/**
 * This class constains my own implementation of a .env file loader
 * avoiding the need to install composer + php + all else to setup
 * phpdotenv by vlucas. However, if needed, just replace my code
 * with phpdotenv.
*/

class DotEnvLoader {
    /**
     * The dir of .env file
     * @var string
     */
    private string $path;

    /**
     * Pass the path to assign to class var
     * @param string $path
     */
    public function __construct(string $path = ".env") {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException('__construct in DotEnvLoader failed - path does not exist');
        }
        $this->path = $path;
        $this->load();
    }

    /**
     * Load variables from .env
     * MAKE SURE YOUR .ENV FOLLOWS A GOOD STRUCTURE!
     *
     *      KEY=value
     *      KEY2=value2
     *
     * No spaces in surrounding the key, and no spaces surrounding value.
     * No "" or '' anywhere
     *
     * OTHERWISE, ADD ADDITIONAL CHECKS, e.g. empty value but key present
    */
    private function load(): void {
        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue; // skip empty lines and skip comments
            }
            // split key into key-val pairs with = separator
            list($key, $value) = explode('=', $line, 2);
            // trim any whitespace
            $key = trim($key);
            $value = trim($value);
            //set env
            putenv("$key=$value");
        }
    }
}
