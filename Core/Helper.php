<?php
namespace Core;
/**
 * The helper class contains reusable functions that are used in various other classes
*/
class Helper {
    /**
     * @var \Database\DatabaseTable $usersTable global variable / class variable used to store methods to access the usersTable
     *
    */
    private \Database\DatabaseTable $usersTable;
    /**
     * Used to construct pdo access for the specific
     * table, and specifying the primary key
    */
    public function __construct() {
        $database = new \Database\DatabaseConnect();
        $pdo = $database->initPDO();
        $this->usersTable = new \Database\DatabaseTable($pdo, 'users', 'uid');
    }
    /**
     * Check if any of the provided fields are empty, must pass associative array with field to check and value
     * @param array $fieldValue must pass associative array with field to check and value
     * @return string|null returns null or str if there is an error, str with the error message
     */
    function checkEmptyFields(array $fieldValue): ?string {
        foreach ($fieldValue as $field => $value) {
            if (empty($value)) {
                return '<p> • ' . $field . ' field is required.</p>';
            }
        }
        return null;
    }
    /**
     * Check if any of the provided fields are having spaces, some field cannot allow spaces such as password
     * @param array $fieldValue must pass associative array with field to check and value
     * @return string|null returns null or str if there is an error, str with the error message
     */
    function checkNoSpaces(array $fieldValue): ?string {
        foreach ($fieldValue as $field => $value) {
            if (str_contains($value, ' ')) {
                return '<p> • ' . $field . ' field should not contain spaces.</p>';
            }
        }
        return null;
    }
    /**
     * Check the length of a specific fields value, must pass minLen and maxLen
     * @param string $field the name of the field passed
     * @param string $value the value itself to be strlen
     * @param int $minLen min allowed length of the field value
     * @param int $maxLen max length of the value
     * @return string|null returns error str if value is out of the range or null if not
     */
    function checkLength(string $field, string $value, int $minLen, int $maxLen): ?string {
        $length = strlen($value);
        if ($length < $minLen || $length > $maxLen) {
            return '<p> • '. $field . ' must be between ' . $minLen . ' and ' . $maxLen . ' characters.</p>';
        }
        return null;
    }
    /**
     * Check if the provided email is valid
     * @param string $email the email address to be checked
     * @return string|null rturns an error message str if the email is invalid using FILTER_VALIDATE_EMAIL
     */
    function checkValidEmail(string $email): ?string {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '<p> • Invalid email address. Make sure it is in a valid format, e.g., dexter.morgan@mdpd.us</p>';
        }
        return null;
    }
    /**
     * [in code reference]
     * Stackoverflow.com. Usage of str_replace with ctype_alpha.
     *      @see https://stackoverflow.com/a/2936893
     *
     * The str_replace allows for ctype_alpha to still work and check if it is alphabetic only
     * while still keeping the spaces, for example, someone could have a space in their name or in their title.
     *
     *
     * @param array $fieldValue an associative array where keys are field names values are the value itself
     * @return string|null returns string or null, str with the error message or null if no error
     */
    function checkAlpha(array $fieldValue): ?string {
        foreach ($fieldValue as $field => $value) {
            if (!ctype_alpha(str_replace(' ', '', $value))) {
                return '<p> • ' . $field . ' may only contain alphabetic values.</p>';
            }
        }
        return null;
    }
    /**
     * Check if the provided username is already taken within the db
     * @param string $username the username to be checked
     * @param array|null $user [optional] if editing user, this will prevent NOT changing the username and leaving the existing one from producing a duplication error
     * @return string|null str error message or null if no error message
     */
    function checkDuplicateUsername(string $username, ?array $user = null): ?string {
        if (empty($username)) {
            return null;
        }
        $username = strtolower(htmlspecialchars($username));
        // if editing a user, and leaving the name as it is, it will not give a dupeUser error, this check checks if user is defined
        // if it is, check the username thats in the array and compare it with the username from the POST array
        if ($user && $username === strtolower(htmlspecialchars($user['username']))) {
            return null;
        }
        $dupeUser = $this->usersTable->find('username', $username);
        if ($dupeUser) {
            return '<p><h3> • We are sorry, that username was already used.</h3></p>';
        }
        return null;
    }
    /**
     * Check if the provided email is already taken within the db
     * @param string $email the email to be checked
     * @param array|null $user [optional] if editing current user, this will prevent NOT changing the email and leaving the existing one from producing a duplication error
     * @return string|null str error message or null if no error message
     */
    function checkDuplicateEmail(string $email, ?array $user = null): ?string {
        if (empty($email)) {
            return null;
        }
        $email = strtolower(htmlspecialchars($email));
        // if editing a user, and leaving the email as it is, it will not give a dupeEmail error, this check checks if user is defined
        // if it is, check the email thats in the array and compare it with the email from the POST array
        if ($user && $email === strtolower(htmlspecialchars($user['email']))) {
            return null;
        }
        $dupeEmail = $this->usersTable->find('email', $email);
        if ($dupeEmail) {
            return '<p><h3> • We are sorry, that email was already used.</h3></p>';
        }
        return null;
    }
    /**
     * [in code reference]
     * Stackoverflow.com. UK Phone number regular expression REGEX.
     *
     *       Allows for:
     *           Matches     +447222555555   | +44 7222 555 555 | (0722) 5555555 #2222
     *           Non-Matches (+447222)555555 | +44(7222)555555  | (0722) 5555555 #22
     *
     *      @see https://stackoverflow.com/a/11518538
     *
     *
     * @param string $phone_num the phone number to check for validation
     * @return string|null returns error message if phone number doesnt match regex rules, or returns null
     */
    function checkPhoneNumber(string $phone_num): ?string {
        $pattern = '/^(((\+44\s?\d{4}|\(?0\d{4}\)?)\s?\d{3}\s?\d{3})|((\+44\s?\d{3}|\(?0\d{3}\)?)\s?\d{3}\s?\d{4})|((\+44\s?\d{2}|\(?0\d{2}\)?)\s?\d{4}\s?\d{4}))(\s?#(\d{4}|\d{3}))?$/';
        if (!preg_match($pattern, $phone_num)) {
            return '<p> • Phone number you provided is not a valid format.</p>';
        }
        return null;
    }
    /**
     * This function is used to set session variables
     * common variables used throughout this website:
     *
     * //                        $_SESSION['permissions'] = $row['permissions'];
     * //                        $_SESSION['loggedin'] = true;
     * //                        $_SESSION['uid'] = $row['uid'];
     * //                        $_SESSION['username'] = $row['username'];
     * //                        $_SESSION['firstname'] = $row['firstname'];
     * //                        $_SESSION['surname'] = $row['surname'];
     * //                        $_SESSION['email'] = $row['email'];
     * //                        $_SESSION['tel'] = $row['phone_num'];
     *
     * @param array $sessionVars an associative array passed for storing the key value pairs in the session
     * @return void no return.
     */
    function setSessionVars(array $sessionVars, bool $setLogin = false): void {
        try {
            if (empty($sessionVars)) {
                // log error to nginx
                error_log('Error in setSessionVars(), session vars cannot be empty.');
                echo '<p>We could not process your request. Please try again later.</p>';
            }
            // only set logged in variable if needed, session vars may not always need to set a logged in true.
            if ($setLogin) {
                $_SESSION['loggedin'] = true;
            }
            /**
             * [in code reference]
             * Stackoverflow.com. Sorting / filtering an associative array to remove the integers.
             *
             *     @see https://stackoverflow.com/a/38461730
            */
            $sessionVars = array_filter($sessionVars, function($key) {
                return !is_numeric($key);
            }, ARRAY_FILTER_USE_KEY);
            foreach ($sessionVars as $key => $value) {
                // IMPORTANT TO CHECK FOR VALUE NULL AND EXPLICITLY CHECK TYPE ===, INSTEAD OF EMPTY VALUE
                // AS empty($value = INT 0) EVALUATES AS TRUE! int 0 in the db is the normal user permission!
                if ($value === null || $value === '') {
                    // log error to nginx
                    error_log('Error in setSessionVars(), value cannot be empty.');
                    echo '<p>We could not process your request. Please try again later.</p>';
                }
                $_SESSION[$key] = $value;
            }
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in setSessionVars: ' . $e->getMessage());
            echo'<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * The simple function below is used to sanitise text from the post submit
     * before inserting into the database
     * @return void returns nothing
     */
    function sanitizePostInput(): void {
        foreach ($_POST as $key => $value) {
            if (isset($value)) {
                $_POST[$key] = htmlspecialchars($value);
            }
        }
    }
}