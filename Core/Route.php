<?php
namespace Core;
/**
 * The routes class is used by entrypoint, it is used to process incoming web requests and
 * it returns the template of the corresponding page needed
*/
class Route implements \Interfaces\Routes {
    /**
     * @var array global variable / class variable used to store all controllers
     */
    private array $controllers;
    /**
     * Constructor to initialise all the controllers for routing
     */
    public function __construct() {
        $this->controllers['home'] = new \Controllers\Home();
        $this->controllers['article'] = new \Controllers\Article();
        $this->controllers['category'] = new \Controllers\Category();
        $this->controllers['contact'] = new \Controllers\Contact();
        $this->controllers['admin'] = new \Controllers\Admin();
        $this->controllers['account'] = new \Controllers\Account();
    }
    /**
     * This function is used to route and return the needed page content when loading, used in entrypoint
     * @param string $route the route string - Controller/Function, e.g. Account/loginCheck
     * @return array returns the page output by the controller, used in entrypoint
     * @throws \Exception if the controller or function doesnt exist, throws Exception and fallback to index
     */
    public function getPage(string $route): array {
        // checking to ensure the route is properly defined, if not it throws exception
        try {
            if (str_contains($route, '/')) {
                list($controllerName, $functionName) = explode('/', $route);
            } else {
                throw new \Exception;
            }
            if (!isset($this->controllers[$controllerName]) || !method_exists($this->controllers[$controllerName], $functionName)) {
                throw new \Exception;
            }
            // this only executes after ensuring controller exists and the method to the pages functions also exists
            $page = $this->controllers[$controllerName]->$functionName();
        } catch(\Exception) {
            // fallback index if exception occurs in the above code, such as trying to access a page that doesnt exist
            $page = $this->controllers['home']->index();
        }
        return $page;
    }
}