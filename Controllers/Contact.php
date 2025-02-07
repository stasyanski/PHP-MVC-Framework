<?php
namespace Controllers;
/**
 * Contains the specific functions for the contact page
 * includes any functions that are on the contact page such as the submit form
 */
class Contact {
    /**
     * @var \Database\DatabaseTable $inquiriesTable global variable / class variable used to store methods to access the inquiries table
     * @var \Core\Helper $helper global variable / class variable used to store methods to access the helper methods
     */
    private \Database\DatabaseTable $inquiriesTable;
    private \Core\Helper $helper;
    /**
     * Used to construct pdo access for the specific
     * table / class, and specifying the primary key
     */
    public function __construct() {
        $database = new \Database\DatabaseConnect();
        $pdo = $database->initPDO();
        $this->inquiriesTable = new \Database\DatabaseTable($pdo, 'inquiries', 'id');
        $this->helper = new \Core\Helper();
    }
    /**
     * This functions returns the title and templaveVar for the respective pages, if anything needs
     * changing, change it here to reflect the website
     * @return array with the necessary templateVars and the template name itself
     */
    public function inquiry(): array {
        return [
            'variables' => ['title' => 'Website - Inquiry'],
            'template' => 'contact.html.php'
        ];
    }
    /**
     * This function handles the submission of the contact form via post, includes validation for all fields
     * @return void no return
     */
    public function submitContactForm(): void {
        try {
            if (isset($_POST['submit']) && $_POST['randchk'] == $_SESSION['rand']) {
                // sanitises all the post input before extracting it
                $this->helper->sanitizePostInput();
                extract($_POST);
                $checks = [];
                // because the required param in html input tag is not too great and can be removed using inspect element
                // i have made a proper function which ensures empty fields are checked properly, serverside
                $checkEmptyFields = $this->helper->checkEmptyFields([
                    'Inquiry' => $inquiry,
                    'Title' => $title,
                    'Firstname' => $firstname,
                    'Surname' => $surname,
                    'Email' => $email,
                    'Phone Number' => $tel
                ]);
                if ($checkEmptyFields) {
                    $checks[] = $checkEmptyFields;
                }
                // it is mostly standardardised that there cant be spaces in email addresses
                // and there cant be spaces in phone number
                $checkNoSpaces = $this->helper->checkNoSpaces([
                    'Email' => $email,
                    'Phone Number' => $tel
                ]);
                if ($checkNoSpaces) {
                    $checks[] = $checkNoSpaces;
                }
                // specifying the length of various variables to ensure compatability with the database
                // inquiry for example is varchar2(5000) so therefore 5000 characters max
                $checks[] = $this->helper->checkLength('Firstname', $firstname, 2, 32);
                $checks[] = $this->helper->checkLength('Surname', $surname, 2, 32);
                $checks[] = $this->helper->checkLength('Title', $title, 2, 128);
                $checks[] = $this->helper->checkLength('Inquiry', $inquiry, 10, 5000);
                $checks[] = $this->helper->checkLength('Email', $email, 2, 128);
                $checks[] = $this->helper->checkValidEmail($email);
                // check that the following contain only alphabet values (spaces allowed)
                $checkAlpha = $this->helper->checkAlpha([
                    'Firstname' => $firstname,
                    'Surname' => $surname,
                    'Title' => $title
                ]);
                if ($checkAlpha) {
                    $checks[] = $checkAlpha;
                }
                // check phone number validity using regex (uk phone format only)
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
                        'title' => $title,
                        'inquiry' => $inquiry,
                        'firstname' => $firstname,
                        'surname' => $surname,
                        'email' => strtolower($email),
                        'phone_num' => $tel,
                        'date' => date('Y-m-d')
                    ];
                    if(isset($_SESSION['username'])) {
                        // this is NN in the db table, only sent if the user is logged in !
                        $values['username'] = $_SESSION['username'];
                    }
                    $this->inquiriesTable->insert($values);
                    $_POST=[];
                    unset($_POST);
                    echo '<h4>Inquiry form successfully sent. We will be in touch as soon as we can!</h4>';
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
            <form action="inquiry" method="POST">
                <!--      The code for value contains the  $_POST value instead of the extracted value as i havent found a way to unset the dynamically assigned       -->
                <input type="hidden" value="<?php echo $rand; ?>" name="randchk">
                <label>Title | <small>A descriptive title. Letters only.</small></label>
                <input type="text" name="title" value="<?= htmlspecialchars($_POST['title'] ?? $_SESSION['title'] ?? '') ?>" />
                <label>Inquiry | <small>A detailed enquiry. Max 5000 characters</small></label>
                <textarea name="inquiry"><?= htmlspecialchars($_POST['inquiry'] ?? $_SESSION['inquiry'] ?? '') ?></textarea>
                <label>Firstname | <small>At least two characters. Letters only.</small></label>
                <input type="text" name="firstname" value="<?= htmlspecialchars($_POST['firstname'] ?? $_SESSION['firstname'] ?? '') ?>" />
                <label>Surname | <small>At least two characters. Letters only.</small></label>
                <input type="text" name="surname" value="<?= htmlspecialchars($_POST['surname'] ?? $_SESSION['surname'] ?? '') ?>" />
                <label>Email | <small>Ensure valid email format, e.g. john@gmail.com.</small></label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $_SESSION['email'] ?? '') ?>" />
                <label>Phone Number | <small>UK Phone numbers only.</small></label>
                <input type="tel" name="tel" value="<?= htmlspecialchars($_POST['tel'] ?? $_SESSION['phone_num'] ?? '') ?>" />
                <input type="submit" name="submit" value="Submit" />
            </form>
            <?php
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in submitContactForm(): ' . $e->getMessage());
            echo $e->getMessage();
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
}