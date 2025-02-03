<?php
namespace Core;
/**
 * This file contains the autoloader class, which allows to use classes anywhere on php files without a specific require statement
*/
class Autoloader {
    /**
     * This function is used create the filepath for registering classes automatically
     * @param string $name takes in the classname as parameter
     * @return void no return.
     */
    public static function autoload(string $name): void {
        $file = '../../' . str_replace('\\', '/', $name) . '.php';
        // only executes if the file is actually existing
        if (file_exists($file)) {
            require_once $file;
        } else {
            // log error to nginx
            error_log('Error in autoloading()');
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function registers the autoloader function in the spl_autoload_register
     * @return void no return.
    */
    public static function register(): void {
        $autoloader = array('\Core\Autoloader', 'autoload');
        spl_autoload_register($autoloader);
    }
}
