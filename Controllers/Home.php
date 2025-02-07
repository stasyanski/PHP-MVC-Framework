<?php
namespace Controllers;
/**
 * Contains the specific functions for the Index public page and the Layout
 * includes any functions that are on the index public page and the layout of the website, such as setting the randbanner
*/
class Home {
    /**
     * @var \Database\DatabaseTable $categoriesTable global variable / class variable used to store methods to access the categories table
     */
    private \Database\DatabaseTable $categoriesTable;
    /**
     * Used to construct pdo access for the specific
     * table, and specifying the primary key
    */
    public function __construct() {
        $database = new \Database\DatabaseConnect();
        $pdo = $database->initPDO();
        $this->categoriesTable = new \Database\DatabaseTable($pdo, 'category', 'id');
    }
    /**
     * This functions returns the title and templaveVar for the respective pages, if anything needs
     * changing, change it here to reflect the website
     * @return array with the necessary templateVars and the template name itself
    */
    public function index(): array{
        return [
            'variables' => ['title' => 'Website - Home'],
            'template' => 'index.html.php'
        ];
    }
    /**
     * This function returns the links on the website, used to construct the navbar and sidebar
     * contains paths to website which are resolved in the Core>Route.php with server uri
     * @return array returns array of variables assigned in the layout html php
    */
    public function websitePaths(): array {
        return [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Latest Articles', 'url' => '/article/latest'],
            ['name' => 'Select Category', 'url' => '#', 'subcategories' => $this->makeSubcategories()],
            ['name' => 'Contact Page', 'url' => '/contact/inquiry'],
            ['name' => 'My Account', 'url' => '/account/portal']
        ];
    }
    /**
     * These function is used to display the header and footer
     * getters that are website wide, used in layout.html.php
     * changing the return value here will be reflected on the website
     *
     *  usage:
     *      <section>
     *          <h1>
     *              <?=$pagesController->getHeader();?>
     *          </h1>
     *      </section>
     *
     * @return string returns a string to be formatted into html and displayed
    */
    public function getHeader(): string {
        return 'PHP MVC Framework - Header';
    }
    public function getFooter(): string {
        return 'PHP MVC Framework - Footer' . ' ' . date('Y');
    }
    /**
     * This function is used to print the HTML for the navbar
     * it uses the data provided by the websitePaths() function to construct the navbar
     * @return bool|string the html for the navbar buffered output
    */
    public function makeNavBar(): bool|string {
        try {
            ob_start();
            $paths = $this->websitePaths();
            if (!empty($paths)) {
                ?><ul><?php
                foreach ($paths as $link):
                    if (!empty($link['url']) && !empty($link['name'])):
                        ?><li>
                        <a href="<?= $link['url'] ?>"><?= $link['name'] ?></a>
                        <!-- check if the current link being looped over is a subcategory, a subcategory, make an unordered list with anchor hrefs to the url -->
                        <?php if (!empty($link['subcategories'])): ?>
                        <ul>
                            <?php foreach ($link['subcategories'] as $subcategory):
                                if (!empty($subcategory['url']) && !empty($subcategory['name'])): ?>
                                    <li><a href="<?=$subcategory['url'] ?>"><?=$subcategory['name']?></a></li>
                                <?php endif;
                            endforeach; ?>
                        </ul>
                    <?php endif; ?>
                        </li><?php
                    endif;
                endforeach;
                ?></ul><?php
            } else {
                error_log('Error in makeSideBar(), the paths are empty.');
                echo '<p>We could not process your request. Please try again later.</p>';
            }
            return ob_get_clean();
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in makeNavBar(): ' . $e->getMessage());
            return '<p>We could not process your request. Please try again later.</p>';
        }
    }

    /**
     * This function is used to print the html for the navbar it uses
     * the data provided by the websitepaths function to construct
     * a side navbar
     * @return bool|string buffered output of the html sidebar
    */
    public function makeSideBar(): bool|string {
        try {
            ob_start();
            $paths = $this->websitePaths();
            if (!empty($paths)) {
                ?><ul><?php
                // loop through the array of websitePaths to create a sideBar
                foreach ($paths as $link):
                    if (!empty($link['url']) && !empty($link['name'])):
                        ?><li>
                        <a href="<?=$link['url']; ?>"><?= $link['name']; ?></a>
                        <!--  check if the current link being looped over is a subcategory, a subcategory, make an unordered list with anchor hrefs to the url-->
                        <?php if (!empty($link['subcategories'])): ?>
                        <ul>
                            <?php foreach ($link['subcategories'] as $subcategory):
                                if (!empty($subcategory['url']) && !empty($subcategory['name'])): ?>
                                    <li><a href="<?= $subcategory['url'] ?>"><?= $subcategory['name']; ?></a></li>
                                <?php endif; endforeach; ?>
                        </ul>
                        <?php endif; ?>
                        </li>
                    <?php endif; endforeach;
                ?></ul><?php
            } else {
                // log error to nginx
                error_log('Error in makeSideBar(), the paths are empty.');
                echo '<p>We could not process your request. Please try again later.</p>';
            }
            return ob_get_clean();
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in makeSideBar(): ' . $e->getMessage());
            return '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * As the subcategories on the website are dynamic and can change anytime
     * this function retrieves them from the database to construct website paths
     *
     * @return array of subcategories on the website
    */
    public function makeSubcategories(): array {
        try {
            $subcategories = [];
            $stmt = $this->categoriesTable->findAll();
            if (!empty($stmt)) {
                foreach ($stmt as $category) {
                    if (!empty($category['name']) && !empty($category['id'])) {
                        $subcategories[] = [
                            'name' => $category['name'],
                            'url' => '/category/filter?id=' . $category['id'],
                        ];
                    }
                }
            }
            return $subcategories;
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in makeSubcategories(): ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
            return[];
        }
    }
    /**
     * The randbanner.php function was restructured to be more reusable and abstracted
     * usage: simply call the function inside an img src=
     *
     *      <?php $pagesController = new \Controllers\Home; ?>
     *      <img src=<?=$pagesController->getRandBanner();?> />
     *
     * @return string returns the relative to index.php in public dir directory with a random banner
    */
    public function getRandBanner(): string {
        try {
            $files = [];
            // relative to where the getRandBanner is being called, in this case index.php always
            foreach (new \DirectoryIterator('../../news/public/images/banners/') as $file) {
                if ($file->isDot()) {
                    continue;
                }
                if (!strpos($file->getFileName(), '.jpg')) {
                    continue;
                }
                $files[] = $file->getFileName();
                if(empty($files)) {
                    // log error to nginx
                    error_log('Error in getRandBanner(), files array is empty: ' . $file->getFileName());
                    return '<p>We could not process your request. Please try again later.</p>';
                }
            }
            $randomFile = $files[rand(0, count($files) - 1)];
            return '/images/banners/' . $randomFile;
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in makeSideBar(): ' . $e->getMessage());
            return '<p>We could not process your request. Please try again later.</p>';
        }
    }
}