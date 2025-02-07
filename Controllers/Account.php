<?php
namespace Controllers;
/**
 * Contains the specific functions for the account public page
 * Includes any functions that are on the account public page and the layout of the website
 * such as logging in and out of the website
 */
class Account {
    /**
     * @var \Database\DatabaseTable $usersTable global variable / class variable used to store methods to access the table
     * @var \Core\Helper $helper global variable / class that provides access to the methods / funcs inside the helper class
     */
    private \Database\DatabaseTable $usersTable;
    private \Core\Helper $helper;
    /**
     * Used to construct pdo access for the specific
     * table, and specifying the primary key
     */
    public function __construct() {
        $database = new \Database\DatabaseConnect();
        $pdo = $database->initPDO();
        $this->usersTable = new \Database\DatabaseTable($pdo, 'users', 'uid');
        $this->helper = new \Core\Helper();
    }
    /**
     * This functions returns the title and templaveVar for the respective pages, if anything needs
     * changing, change it here to reflect the website
     * @return array returns an array with the template to load and templateVars
     */
    public function portal(): array {
        return [
            'variables' => ['title' => 'Website - Account'],
            'template' => 'account.html.php'
        ];
    }
    public function signup(): array {
        return [
            'variables' => ['title' => 'Website - Sign up'],
            'template' => 'signup.html.php'
        ];
    }
    /**
     * This function is used to make the sidebar specific to the accounts portal
     * it includes url to the account portal and the signup itself
     * @return array with the paths themselves
     */
    public function accountPaths(): array {
        // this returns an array of the variables assigned in the layout.html.php
        // specific to the sidebar in the account portal
        return [
            ['name' => 'Portal', 'url' => '/account/portal'],
            ['name' => 'Sign up', 'url' => '/account/signup'],
        ];
    }
    /**
     * This function is used to print the html for the navbar, it uses the data provided
     * by websitepaths function to construct a html side nav bar
     * @return bool|string returns the made html through buffer output
    */
    public function makeSideBar(): bool|string {
        ob_start();
        try {
            $paths = $this->accountPaths();
            if(!empty($paths)) {
                ?><ul><?php
                // loop through the array of websitePaths to create a sideBar
                foreach ($paths as $link):
                    if(!empty($link['url']) && !empty($link['name'])): ?>
                    <li>
                        <a href="<?= $link['url']; ?>"><?= $link['name']; ?></a>
                    </li>
                    <?php endif;
                endforeach;
                ?></ul><?php
            } else {
                // log error to nginx
                error_log('Error in makeSideBar(), the paths are empty.');
                echo '<p>We could not process your request. Please try again later.</p>';
            }
        } catch(\Exception $e) {
            // log error to nginx
            error_log('Error in makeSideBar(): ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
        return ob_get_clean();
    }
    /**
     * A simple logout function triggered on button press
     * which clears the session (stored user logged in info such as loggedin=true, session['username'], etc.)
     * clears the data / unsets the session and redirs user back to the login / portal page
     * @return void returns nothing.
     */
    public function logOut(): void {
        try {
            ?>
            <form method="post">
                <button type="submit" name="logout">Log Out</button>
            </form>
            <?php
            if (isset($_POST['logout'])) {
                $_SESSION = [];
                session_unset();
                header('Location: /account/portal');
            }
        } catch(\Exception $e) {
            // log error to nginx
            error_log('Error in logOut(): ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * The login check function allows a user to login to their account, so they can
     * more easily post comments and send inquiries (autofill on fields based on $_session vars)
     * @return void no return.
     */
    public function loginCheck(): void {
        try {
            if (isset($_POST['submit'])) {
                // sanitize post before extracting it
                $this->helper->sanitizePostInput();
                extract($_POST);
                $checks = [];
                // because the required param in html input tag is not too great and can be removed using inspect element
                // i have made a proper function which ensures empty fields are checked properly, serverside
                $checkEmptyFields = $this->helper->checkEmptyFields([
                    'Username' => $username,
                    'Password' => $password
                ]);
                if ($checkEmptyFields) {
                    $checks[] = $checkEmptyFields;
                }
                // it is mostly standardardised that there cant be spaces in usernames
                // and there cant be spaces in passwords either
                $checkNoSpaces = $this->helper->checkNoSpaces([
                    'Username' => $username,
                    'Password' => $password
                ]);
                if ($checkNoSpaces) {
                    $checks[] = $checkNoSpaces;
                }
                // specifying the length of various variables to ensure compatability with the database
                // inquiry for example is varchar2(5000) so therefore 5000 characters max
                $checks[] = $this->helper->checkLength('Username', $username, 2, 32);
                $checks[] = $this->helper->checkLength('Password', $password, 8, 32);;
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
                    $rows = $this->usersTable->find('username', $username);
                    if (!empty($rows)) {
                        $row = $rows[0];
                        if (password_verify($password, $row['password'])) {
                            $this->helper->setSessionVars($row, true);
                        } else {
                            echo '<p>Wrong username or password</p>';
                        }
                    } else {
                        echo '<p>No user found with that username.<p>';
                    }
                }
            }
        if (isset($_SESSION['loggedin'])) {
            echo '<p>Welcome back, ' . ucfirst($_SESSION['username'] ?? '') . '.</p>';
            $this->logout();
        } else {
            ?>
            <form action="portal" method="POST">
                <label>Username</label>
                <input type="text" name="username" />
                <label>Password</label>
                <input type="password" name="password" />
                <input type="submit" name="submit" value="submit" />
            </form>
            <?php
        }
    } catch(\Exception $e) {
        error_log('Error in loginCheck(): ' . $e->getMessage());
        echo '<p>We could not process your request. Please try again later.</p>';}
    }
    /**
     * This function is used to create an account, fresh account to store in the db
     * and users can login into this account, all the information is validated before
     * insert
     * @return void returns nothing
     */
    public function makeAccount(): void {
        try {
            if (isset($_POST['submit']) && $_POST['randchk'] == $_SESSION['rand']) {
                // sanitise post input before extracting it
                $this->helper->sanitizePostInput();
                extract($_POST);
                // check for duplicate username and email, email and username are unique in the db
                // signing up with an already used email is not allowed
                $checkDuplicateUser = $this->helper->checkDuplicateUsername($username);
                if ($checkDuplicateUser) {
                    $checks[] = $checkDuplicateUser;
                }
                $checkDuplicateEmail = $this->helper->checkDuplicateEmail($email);
                if ($checkDuplicateEmail) {
                    $checks[] = $checkDuplicateEmail;
                }
                // check for empty fields
                // all fields should be populated when creating account
                $checkEmptyFields = $this->helper->checkEmptyFields([
                    'Firstname' => $firstname,
                    'Surname' => $surname,
                    'Username' => $username,
                    'Email' => $email,
                    'Phone Number' => $tel,
                    'Password' => $password
                ]);
                if ($checkEmptyFields) {
                    $checks[] = $checkEmptyFields;
                }
                // check for no spaces
                // it is mostly standardised that username, password, email and phone number cannot contain spaces
                $checkNoSpaces = $this->helper->checkNoSpaces([
                    'Username' => $username,
                    'Password' => $password,
                    'Email' => $email,
                    'Phone Number' => $tel
                ]);
                if ($checkNoSpaces) {
                    $checks[] = $checkNoSpaces;
                }
                // restring length of inputted text
                // ensures integrity in the db and prevents violation of varchar length max
                $checks[] = $this->helper->checkLength('Username', $username, 4, 32);
                $checks[] = $this->helper->checkLength('Firstname', $firstname, 2, 32);
                $checks[] = $this->helper->checkLength('Surname', $surname, 2, 32);
                $checks[] = $this->helper->checkLength('Password', $password, 8, 32);
                $checks[] = $this->helper->checkValidEmail($email);
                // check that the following are alphabetic only
                $checkAlpha = $this->helper->checkAlpha([
                    'Firstname' => $firstname,
                    'Surname' => $surname,
                    'Username' => $username,
                ]);
                if ($checkAlpha) {
                    $checks[] = $checkAlpha;
                }
                // check validity of phone number and that it is uk format using regex
                $checks[] = $this->helper->checkPhoneNumber($tel);
                // the functions return null instead of a string with error, this gets rid of the nulls so can truly check if checks is empty
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
                        'username' => strtolower($username),
                        'firstname' => $firstname,
                        'surname' => $surname,
                        'email' => strtolower($email),
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'phone_num' => $tel
                    ];
                    $this->usersTable->insert($values);
                    $rows = $this->usersTable->find('username', strtolower($username));
                    if (!empty($rows)) {
                        $row = $rows[0];
                    }
                    if ($row) {
                        $this->helper->setSessionVars($row, true);
                    }
                }
            }
            /**
             * [in code reference]
             * Stackoverflow.com. Preventing form resubmission via session variable.
             *      @see https://stackoverflow.com/a/38768140
             */
            $rand = rand();
            $_SESSION['rand'] = $rand;
            if (isset($_SESSION['loggedin'])) {
                echo '<p>You are currently signed in to: ' . ucfirst($_SESSION['username'] ?? '').'.</p>';
                $this->logout();
            } else { ?>
            <form action="signup" method="POST">
                <input type="hidden" value="<?php echo $rand; ?>" name="randchk">
                <label>Firstname | <small>At least two characters. Letters only.</small></label>
                <input type="text" name="firstname" value="<?= $firstname ?? '' ?>"/>
                <label>Surname | <small>At least two characters. Letters only.</small></label>
                <input type="text" name="surname" value="<?= $surname ?? '' ?>"/>
                <label>Username | <small>Username must be unique. Letters only.</small></label>
                <input type="text" name="username" value="<?= $username ?? '' ?>"/>
                <label>Email | <small>Ensure valid email format, e.g. john@gmail.com.</small></label>
                <input type="email" name="email" value="<?= $email ?? '' ?>"/>
                <label>Phone number | <small>UK Phone numbers only.</small></label>
                <input type="tel" name="tel" value="<?= $tel ?? '' ?>"/>
                <label>Password | <small>Must be 8 to 32 characters.</small></label>
                <input type="password" name="password" value="<?= $password ?? '' ?>"/>
                <input type="submit" name="submit" value="Submit" />
            </form>
            <?php
            }
        } catch (\Exception $e) {
            //log error to nginx
            error_log('Error in makeAccount(): '.$e->getMessage());
            echo '<p>We could not make your request. Please try again later.</p>';
        }
    }
}