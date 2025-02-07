<?php
namespace Controllers;
/**
 * Contains the specific functions for the Index public page and the Layout
 * includes any functions that are on the article public page and the layout of the website, such as displaying all articles
 */
class Article {
    /**
     * @var \Database\DatabaseTable $articlesTable global variable / class variable used to store methods to access the table
     * @var \Database\DatabaseTable $usersTable global variable / class variable used to store methods to access the table
     */
    private \Database\DatabaseTable $articlesTable;
    private \Database\DatabaseTable $usersTable;
    /**
     * Used to construct pdo access for the specific
     * table, and specifying the primary key
     */
    public function __construct() {
        $database = new \Database\DatabaseConnect();
        $pdo = $database->initPDO();
        $this->articlesTable = new \Database\DatabaseTable($pdo, 'article', 'id');
        $this->usersTable = new \Database\DatabaseTable($pdo, 'users', 'uid');
    }
    /**
     * This functions returns the title and templaveVar for the respective pages, if anything needs
     * changing, change it here to reflect the website
     * @return array with template vars and the template name itself
    */
    public function latest(): array {
        return [
            'variables' => ['title' => 'Website - Latest Articles'],
            'template' => 'latest.html.php'
        ];
    }
    /**
     * The function below displays the articles
     * the parameters are used for sql restriction
     * the findAll function takes in the field to restrict
     * and the order by sorting which can either be DESC or ASC
     *
     *
     * @param mixed $field this is the field to restring the query by, e.g. passing 2 will display all articles where categoryId is 2
     * @param string $sort this is the sorting by restriction, desc or asc
     * @return bool|string returns buffering output
    */
    public function displayAllArticles(mixed $field, string $sort): bool|string {
        ob_start();
        try {
            if (strtolower($sort) !== 'asc' && strtolower($sort) !== 'desc') {
                error_log('Error in displayAllArticles(), the sort parameter must be asc or desc');
                echo '<p>We could not process your request. Please try again later.</p>';
            }
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $articles = $this->articlesTable->find('uid', $_GET['id']);
                $users = $this->usersTable->find('uid', $_GET['id']);
                if (!empty($users)) {
                    $user = $users[0];
                } else {
                    echo '<p>User or article you searched for does not exist.</p>';
                    return ob_get_clean();
                }
                if(!empty($articles)) {
                    // if the id isset (meaning user clicked on a author anchor href) it will display the latest articles written by the author
                    echo '<h3>Articles by ' . ($user['firstname'] ?? 'Unknown') . ' ' . ($user['surname'] ?? 'Unknown') . '</h3>';
                    echo '<hr />';
                    echo '<p>Find below all articles posted by this author!</p>';
                    echo '<p></p>';
                    foreach ($articles as $article) {
                        echo '<h3>' . ($article['title'] ?? 'Unknown') . '</h3>';
                        echo '<hr />';
                        echo '<em>Posted: ' . ($article['date'] ?? 'Unknown') . '  |  Author: ' . ($user['firstname'] ?? 'Unknown') . ' ' . ($user['surname'] ?? 'Unknown') . '</em>';
                        echo '<p>' . ($article['description'] ?? 'Unknown') . '</p>';
                    }

                } else {
                    echo '<p>There are no articles posted by this author.</p>';
                    return ob_get_clean();
                }
            } else {
                // if the id is not set for $_get then it will just display all articles from the db
                $articles = $this->articlesTable->findAll($field, $sort);
                if (!empty($articles)) {
                    foreach ($articles as $article) {
                        if(empty($article['uid'])) {
                            echo '<p>We could not process your request. Please try again later.</p>';
                        } else {
                            $users = $this->usersTable->find('uid',$article['uid']);
                        }
                        if (empty($users)) {
                            echo '<p>User or article you searched for does not exist.</p>';
                            return ob_get_clean();
                        }
                        $user = $users[0];
                        echo '<h3>' . ($article['title'] ?? 'Unknown') . '</h3>';
                        echo '<hr />';
                        echo '<em>Posted: ' . ($article['date'] ?? 'Unknown') . '  |  Author: <a href="/article/latest?id=' . ($user['uid'] ?? '0') . '">' . ($user['firstname'] ?? 'Unknown') . ' ' . ($user['surname'] ?? 'Unknown') . '</a></em>';
                        echo '<p>' . ($article['description'] ?? 'Unknown') . '</p>';
                    }
                } else {
                    echo '<p>There are no articles posted yet.</p>';
                    return ob_get_clean();
                }
            }
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in displayAllArticles(): ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
        return ob_get_clean();
    }
}
