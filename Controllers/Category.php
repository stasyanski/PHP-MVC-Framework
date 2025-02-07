<?php
namespace Controllers;
/**
 * Contains the specific functions for the category public page
 * includes any functions that are on the category public page and the layout of the website
 * e.g. displaying the comment section
 */
class Category {
    /**
     * @var \Database\DatabaseTable $categoriesTableTable global variable / class variable used to store methods to access the table
     * @var \Database\DatabaseTable $usersTable global variable / class variable used to store methods to access the table
     * @var \Database\DatabaseTable $commentsTable global variable / class variable used to store methods to access the table
     * @var \Core\Helper $helper global variable / class variable used to store methods to access the class methods
     */
    private \Database\DatabaseTable $categoriesTable;
    private \Database\DatabaseTable $usersTable;
    private \Database\DatabaseTable $commentsTable;
    private \Core\Helper $helper;
    /**
     * Used to construct pdo access for the specific
     * table / class, and specifying the primary key
     */
    public function __construct() {
        $database = new \Database\DatabaseConnect();
        $pdo = $database->initPDO();
        $this->categoriesTable = new \Database\DatabaseTable($pdo, 'article', 'id');
        $this->usersTable = new \Database\DatabaseTable($pdo, 'users', 'id');
        $this->commentsTable = new \Database\DatabaseTable($pdo, 'comments', 'id');
        $this->helper = new \Core\Helper();
    }
    /**
     * This functions returns the title and templaveVar for the respective pages, if anything needs
     * changing, change it here to reflect the website
     * @return array returns an array with the template to load and templateVars
     */
    public function filter(): array {
        return [
            'variables' => ['title' => 'Website- Category'],
            'template' => 'category.html.php'
        ];
    }

    /**
     * The function below is used to create the add Comment form on the comment page
     * all the input is sanitised before it is entered into the database
     * @return void returns nothing.
     */
    public function commentForm():void {
        try {
            if (isset($_POST['submit']) && $_POST['randchk'] == $_SESSION['rand']) {
                // sanitize post input before extracting it
                $this->helper->sanitizePostInput();
                extract($_POST);
                $checks=[];
                // because the required param in html input tag is not too great and can be removed using inspect element
                // i have made a proper function which ensures empty fields are checked properly, serverside
                $checkEmptyFields = $this->helper->checkEmptyFields([
                    'Message' => $text,
                    'Firstname' => $firstname,
                    'Surname' => $surname,
                    'Email' => $email,
                ]);
                if ($checkEmptyFields) {
                    $checks[] = $checkEmptyFields;
                }
                // it is mostly standardardised that there cant be spaces in email addresses
                $checkNoSpaces = $this->helper->checkNoSpaces([
                    'Email' => $email,
                ]);
                if ($checkNoSpaces) {
                    $checks[] = $checkNoSpaces;
                }
                // specifying the length of various variables to ensure compatability with the database
                // message text for example is varchar2(5000) so therefore 5000 characters max
                $checks[] = $this->helper->checkLength('Firstname', $firstname, 2, 32);
                $checks[] = $this->helper->checkLength('Surname', $surname, 2, 32);
                $checks[] = $this->helper->checkLength('Message', $text, 10, 5000);
                $checks[] = $this->helper->checkLength('Email', $email, 2, 128);
                $checks[] = $this->helper->checkValidEmail($email);
                // check that the following contain only alphabet values (spaces allowed)
                $checkAlpha = $this->helper->checkAlpha([
                    'Firstname' => $firstname,
                    'Surname' => $surname,
                ]);
                if ($checkAlpha) {
                    $checks[] = $checkAlpha;
                }
                foreach ($checks as $key => $value) {
                    if ($value === null) {
                        unset($checks[$key]);
                    }
                }
                if (!empty($checks)) {
                    ?>
                    <p>We could not send your form, please ensure the following: </p>
                    <?php
                    foreach ($checks as $error) {
                        if ($error) {
                            echo $error;
                        }
                    }
                } else {
                    $values = [
                        'firstname' => $firstname,
                        'articleId' => $articleId,
                        'surname' => $surname,
                        'email' => strtolower($email),
                        'text' => $text,
                        'date' => (new \DateTime())->format('Y-m-d H:i:s')
                    ];
                    if(isset($_SESSION['username'])) {
                        $values['username'] = $_SESSION['username'];
                    }
                    $this->commentsTable->insert($values);
                    $_POST=[];
                    unset($_POST);
                }
            }
            /**
             * [in code reference]
             * Stackoverflow.com. Preventing form resubmission via session variable.
             *      @see https://stackoverflow.com/a/38768140
             */
            $rand = rand();
            $_SESSION['rand'] = $rand;
            ?>
            <form action="" method="POST">
                <h3>Add comment | <small>Comment down your thoughts on this article or discuss with others.</small></h3>
                <p>Rather than typing your full name and email everytime you leave a comment, it is easier to create an account!</p>
                <p><a href="/account/signup">Sign up now</a></p>
                <!--  pre-fill / autofill with the already saved session vars for easier form completion / submissions -->
                <!--    if session vars not isset / user not logged in then defaults to empty str ''  -->
                <input type="hidden" value="<?php echo $rand; ?>" name="randchk">
                <input type="hidden" name="articleId" value="<?=$_GET['article']?>">
                <!--      The code for value contains the  $_POST value instead of the extracted value as i havent found a way to unset the dynamically assigned       -->
                <label>Firstname:</label>
                <input type="text" name="firstname" value="<?= htmlspecialchars($_POST['firstname'] ?? $_SESSION['firstname'] ?? '') ?>" />
                <label>Surname:</label>
                <input type="text" name="surname" value="<?= htmlspecialchars($_POST['surname'] ?? $_SESSION['surname'] ?? '') ?>" />
                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $_SESSION['email'] ?? '') ?>" />
                <label>Message:</label>
                <textarea name="text" rows="4" cols="50"><?= htmlspecialchars($_POST['text'] ?? '') ?></textarea>
                <input type="submit" name="submit" value="Submit">
            </form>
            <?php
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in commentForm(): ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function is used to display the current comments on the
     * specific article, sorted by descending order on date field
     * @return void returns nothing.
    */
    public function displayComments(): void {
        try {
            if (isset($_GET['article']) && is_numeric($_GET['article'])) {
                $comments = $this->commentsTable->find('articleId', $_GET['article'], 'date', 'DESC');
                if (!empty($comments)) {
                    foreach ($comments as $comment) {
                        ?>
                        <table style="padding-top: 50px;">
                            <thead>
                            <tr>
                                <th style="text-align: left;">Firstname</th>
                                <th style="text-align: left;">Surname</th>
                                <th style="text-align: left;">Username</th>
                                <th style="text-align: left;">Date</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><?= $comment['firstname'] ?? '' ?></td>
                                <td><?= $comment['surname'] ?? '' ?></td>
                                <td><?= $comment['username'] ?? '' ?></td>
                                <td><?= $comment['date'] ?? '' ?></td>
                            </tr>
                            </tbody>
                        </table>
                        <table>
                            <tbody>
                            <tr>
                                <td style="border-bottom: 0;">
                                    <?= $comment['text'] ?? '' ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <br>
                        <?php
                    }
                }
            }
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in displayComments(): ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * The function below displays the articles
     * based on various GET variables
     *
     * @return bool|string returns the buffer output
    */
    public function displayCategories(): bool|string {
        try {
            ob_start();
            // checks if there id and id is set, then display the categories only specific articles
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $articles = $this->categoriesTable->find('categoryId', $_GET['id']);
                // if not then checks based on the article get var
            } else if (isset($_GET['article']) && is_numeric($_GET['article'])) {
                $articles = $this->categoriesTable->find('id', $_GET['article']);
            }
            // if articles stmt returns nothing then display article not found
            if (empty($articles)) {
                echo '<p>Article not found.</p>';
                return ob_get_clean();
            }
            foreach ($articles as $article) {
                if(empty($article['uid'])) {
                    echo '<p>We could not process your request. Please try again later.</p>';
                } else {
                    $users = $this->usersTable->find('uid',$article['uid']);
                }
                if (!empty($users)) {
                    $user = $users[0];
                } else {
                    echo '<p>User not found.</p>';
                    return ob_get_clean();
                }
                ?>
                <h3>
                    <?php
                    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                        // if category id is set, then display just the categories
                        echo '<a href="?article=' . $article['id'] . '&comments=show">' . ($article['title'] ?? 'Unknown') . '</a>';
                    } else {
                        // if not display just the title in plaintext
                        echo ($article['title'] ?? 'Unknown');
                    }
                    ?>
                </h3>
                <?php
                echo '<hr />';
                echo '<em>Posted: ' . ($article['date'] ?? 'Unknown') . ' | Author: <a href="/article/latest?id=' . ($user['uid'] ?? '0') . '">' . ($user['firstname'] ?? 'Unknown') . ' ' . ($user['surname'] ?? 'Unknown') . '</a></em>';
                // only display the description if viewing an article
                if(isset($_GET['article']) && is_numeric($_GET['article'])) {
                    echo '<p>' . ($article['description'] ?? 'Unknown') . '</p>';
                }
                echo '<p><img src="' . ($article['path'] ?? '') . '" alt="" style="max-height: 220px; width: auto;" /></p>';

                // if viewing an article and comments are on, then view the comments
                if (isset($_GET['article']) && is_numeric($_GET['article']) && isset($_GET['comments']) && $_GET['comments'] == 'show') {
                    $this->commentForm();
                    $this->displayComments();
                }
            }
            return ob_get_clean();
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in displayCategories(): ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
            return ob_get_clean();
        }
    }
}