<?php
/**
 * The session is used to store session variables
 * it stores if user is logged in $_SERVER['loggedin']
 * and permissions $_SERVER['permissions'] for example
*/
session_start();

/**
 * This code is used to register the autoloader which allows for easier
 * management and use of classes, listens for class instantiations
 * makes use of namespaces, grouping related classes for easier
 * management
*/
require_once '../../Core/Autoloader.php';
\Core\Autoloader::register();

/**
 * Instantiates the entrypoint and routes class which is used to
 * handle incoming server requests using server_uri and loading
 * the necessary files, such as the templates
*/
$routes = new \Core\Route();
$entryPoint = new \Core\EntryPoint($routes);
$entryPoint->run();


