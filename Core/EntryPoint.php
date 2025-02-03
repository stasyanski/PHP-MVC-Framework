<?php
namespace Core;
/**
 * The class in this file handles incoming webpage requests and uses routes to properly route users onto the page they want
 * it returns the template of the corresponding page needed
*/
class EntryPoint {
    /**
     * @var \Core\Route $routes global variable / class variable used to store the routes passed in index.php from route class
     */
    private \Core\Route $routes;
    /**
     * Constructor initialises the router variable
     * @param \Core\Route $routes initiliased with an instance of the route class
     */
    public function __construct(\Core\Route $routes) {
        $this->routes = $routes;
    }
    /**
     * The run function is used to define the route the user wants to be accessing
     * which loads the respective template and templateVars
     */
    public function run(): void {
        try {
            // added strtolower so everything can still be accessed, e.g. website.com/admin/portal will load
            // the same as  website.com/ADMIN/PORTAL
            $route = strtolower(ltrim(explode('?', $_SERVER['REQUEST_URI'])[0], '/'));
            $page = $this->routes->getPage($route);
            $output = $this->loadTemplate('../../Templates/' . $page['template'], $page['variables']);
            $title = $page['variables']['title'];
            require '../../Templates/layout.html.php';
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in run(): ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * Loads the template specified by the run function
     * this function returns the output on any main pages on the website
     * @param string $fileName the path to the the file that must be loaded into output
     * @param array $templateVars an associative array with variables to be unpacked and assigned to vars, e.g. $title
     * @return bool|string returns ob_get_clean buffer
     */
    public function loadTemplate(string $fileName, array $templateVars): bool|string {
        try {
            if(!file_exists($fileName)) {
                // log error to nginx
                error_log('Error in loadTemplate, template filename does not exist :');
                return '<p>We could not process your request. Please try again later.</p>';
            } else {
                extract($templateVars);
                ob_start();
                require $fileName;
                return ob_get_clean();
            }
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in loadTemplate(): ' . $e->getMessage());
            return '<p>We could not process your request. Please try again later.</p>';
        }
    }
}
