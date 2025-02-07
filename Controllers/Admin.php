<?php
namespace Controllers;
/**
 * Contains the specific functions for the Admin private pages and the Layout
 * Includes any functions that are on the Admin private pages and the layout of the website
 * e.g. editing the categories and articles
 * Despite there being a controller for article and categories, the functions related
 * to controlling articles and categories from an admin account will be
 * kept here !
*/
class Admin {
    /**
     * @var \Database\DatabaseTable $usersTable global variable / class variable used to store methods to access the table
     * @var \Database\DatabaseTable $articlesTable global variable / class variable used to store methods to access the table
     * @var \Database\DatabaseTable $categoriesTable global variable / class variable used to store methods to access the table
     * @var \Database\DatabaseTable $inquiriesTable global variable / class variable used to store methods to access the table
     * @var \Core\Helper $helper global variable / class variable used to store methods to access the helper class
     */
    private \Database\DatabaseTable $usersTable;
    private \Database\DatabaseTable $articlesTable;
    private \Database\DatabaseTable $categoriesTable;
    private \Database\DatabaseTable $inquiriesTable;
    private \Core\Helper $helper;
    /**
     * Used to construct pdo access for the specific
     * table, and specifying the primary key
     */
    public function __construct() {
        $database = new \Database\DatabaseConnect();
        $pdo = $database->initPDO();
        $this->usersTable = new \Database\DatabaseTable($pdo, 'users', 'uid');
        $this->articlesTable = new \Database\DatabaseTable($pdo, 'article', 'id');
        $this->categoriesTable = new \Database\DatabaseTable($pdo, 'category', 'id');
        $this->inquiriesTable = new \Database\DatabaseTable($pdo, 'inquiries', 'id');
        $this->helper = new \Core\Helper();
    }
    /**
     * This functions returns the title and templaveVar for the respective pages, if anything needs
     * changing, change it here to reflect the website
     * @return array with template vars and the template name itself
     */
    public function portal(): array {
        return [
            'variables' => ['title' => 'Admin Portal'],
            'template' => 'admin/index.html.php'
        ];
    }
    public function manageusers(): array {
        return [
            'variables' => ['title' => 'Manage Users'],
            'template' => 'admin/manageusers.html.php'
        ];
    }
    public function inquiries(): array {
        return [
            'variables' => ['title' => 'Customer Inquiries'],
            'template' => 'admin/inquiries.html.php'
        ];
    }
    public function articles(): array {
        return [
            'variables' => ['title' => 'Articles'],
            'template' => 'admin/articles.html.php'
        ];
    }
    public function addarticle(): array {
        return [
            'variables' => ['title' => 'Add Article'],
            'template' => 'admin/addarticle.html.php'
        ];
    }
    public function editarticle(): array {
        return [
            'variables' => ['title' => 'Edit Article'],
            'template' => 'admin/editarticle.html.php'
        ];
    }
    public function deletearticle(): array {
        return [
            'variables' => ['title' => 'Delete Article'],
            'template' => 'admin/deletearticle.html.php'
        ];
    }
    public function categories(): array {
        return [
            'variables' => ['title' => 'Categories'],
            'template' => 'admin/categories.html.php'
        ];
    }
    public function addcategory(): array {
        return [
            'variables' => ['title' => 'Add Category'],
            'template' => 'admin/addcategory.html.php'
        ];
    }
    public function deletecategory(): array {
        return [
            'variables' => ['title' => 'Delete Category'],
            'template' => 'admin/deletecategory.html.php'
        ];
    }
    public function editcategory(): array {
        return [
            'variables' => ['title' => 'Edit Category'],
            'template' => 'admin/editcategory.html.php'
        ];
    }
    /**
     * This function returns the links on the website, used to construct the sidebar
     * contains paths to website which are resolved in the Core>Route.php with server uri
     * @return array returns array of variables assigned in the layout html php
     */
    public function adminPaths(): array {
        // this returns an array of the variables assigned in the layout.html.php
        // specific to the sidebar in the admin portal
        return [
            ['name' => 'Portal', 'url' => '/admin/portal'],
            ['name' => 'Manage Users', 'url' => '/admin/manageusers'],
            ['name' => 'Inquiries', 'url' => '/admin/inquiries'],
            ['name' => 'Add Category', 'url' => '/admin/addcategory'],
            ['name' => 'Add Article', 'url' => '/admin/addarticle'],
            ['name' => 'List Categories', 'url' => '/admin/categories'],
            ['name' => 'List Articles', 'url' => '/admin/articles'],
        ];
    }
    /**
     * This function is used to print the HTML for the sidebar
     * t uses the data provided by the adminPaths function to make the sidebar in html
     * @return bool|string buffered output of the HTML sidebar that was created
     */
    public function makeSideBar(): bool|string {
        try {
            ob_start();
            $paths = $this->adminPaths();
            if (!empty($paths)):
            ?><ul><?php
                foreach ($paths as $link):
                    if (!empty($link['url']) && !empty($link['name'])): ?>
                        <li>
                            <a href="<?= $link['url']; ?>"><?= $link['name']; ?></a>
                        </li>
                    <?php endif;
                endforeach;
            endif;
            ?></ul><?php
            return ob_get_clean();
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in makeSidebar() ' . $e->getMessage());
            return '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function checks if the user is logged in and mainly ensures that the account logged in
     * has the right permissions for loggin in and accessing the rest of the admin portal
     */
    public function loginCheck(): void {
        try {
            if (isset($_POST['submit'])) {
                extract($_POST);
                $this->helper->sanitizePostInput();
                $checks = [];
                $checkEmptyFields = $this->helper->checkEmptyFields([
                    'Username' => $username,
                    'Password' => $password
                ]);
                if ($checkEmptyFields) {
                    $checks[] = $checkEmptyFields;
                }
                $checkNoSpaces = $this->helper->checkNoSpaces([
                    'Username' => $username,
                    'Password' => $password
                ]);
                if ($checkNoSpaces) {
                    $checks[] = $checkNoSpaces;
                }
                $checks[] = $this->helper->checkLength('Username', $username, 2, 32);
                $checks[] = $this->helper->checkLength('Password', $password, 8, 32);
                // the functions return null instead of a string with error, this gets rid of the nulls so can truly check if checks is empty
                foreach ($checks as $key => $value) {
                    if ($value === null) {
                        unset($checks[$key]);
                    }
                }
                if (!empty($checks)) {
                    echo '<p>We could not send your form, please ensure the following: </p>';
                    foreach ($checks as $error) {
                        if ($error) {
                            echo $error;
                        }
                    }
                } else {
                    $rows = $this->usersTable->find('username', strtolower(htmlspecialchars($username)));
                    if (!empty($rows)) {
                        $row = $rows[0];
                    }
                    if ($row) {
                        if (isset($row['permissions']) && ($row['permissions'] == '1' || $row['permissions'] == '2')) {
                            if (password_verify($password, $row['password'])) {
                                $this->helper->setSessionVars($row, true);
                            } else {
                                echo '<p>Wrong password for the username.</p>';
                            }
                        } else {
                            echo '<p>You do not have the permissions to access the admin portal.</p>';
                            echo '<p>If you believe this to be an error, please contact the system administrator.</p>';
                            echo '<p>Email: sysadmin@admin.uk</p>';
                        }
                    } else {
                        echo '<p>No user found with that username.</p>';
                    }
                }
            }
            if (isset($_SESSION['loggedin']) && ($_SESSION['permissions'] == '1' || $_SESSION['permissions'] == '2')) {
                echo '<p>Welcome back, ' . ucfirst($_SESSION['username']) . '. Please choose an option from the left.</p>';
            } elseif (isset($_SESSION['loggedin']) && $_SESSION['permissions'] == '0') {
                echo '<p>You do not have the permissions to access this page.</p>';
                echo '<p>If you believe this to be an error, please contact the system administrator.</p>';
                echo '<p>Email: sysadmin@admin.uk</p>';
            } else {
                ?>
                <form action="portal" method="POST">
                    <label>Username</label>
                    <input type="text" name="username" />
                    <label>Password</label>
                    <input type="password" name="password" />
                    <input type="submit" name="submit" value="Submit" />
                </form>
                <?php
            }
        } catch (\Exception $e) {
            // log error to nginx
            error_log('error in loginCheck() ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function contains the logic for editing the users accounts
     * editing a users account can only be done in a hierarchical way
     *
     *      example: a sysadmin can edit anyone
     *               an admin cannot edit a sysadmin
     *                  but can edit users
     *
     * @param int $uid the id of the user currently being edited
     * @return void returns nothing.
     */
    public function editUserFunc(int $uid): void {
        try {
            $users = $this->usersTable->find('uid', $uid);
            // the user being undefined is already handled in manageAccounts, $user should NEVER be undefined.
            if ($users) {
                $user = $users[0];
            }
            // used to control not showing the edit form after succersfful submission!
            $showForm=true;
            if (isset($_POST['submit'])) {
                extract($_POST);
                $this->helper->sanitizePostInput();
                if(isset($_POST['delete'])) {
                    // need to ensure both are int before making a comparison, might otherwise delete the user
                    // a user cannot delete their own account
                    if((int)$uid !== (int)$_SESSION['uid']) {
                        $this->usersTable->delete($uid);
                        echo '<p>User deleted successfully.</p>';
                        return;
                    } else {
                        echo '<p>You cannot delete your own account.</p>';
                        return;
                    }
                }
                $checks = [];
                // the usual checks, morst important are checking for duplicates
                // if not changing the username, it should not dive a duplicate user error !!
                $checkDuplicateUser = $this->helper->checkDuplicateUsername($username, $user);
                if ($checkDuplicateUser) {
                    $checks[] = $checkDuplicateUser;
                }
                $checkDuplicateEmail = $this->helper->checkDuplicateEmail($email, $user);
                if ($checkDuplicateEmail) {
                    $checks[] = $checkDuplicateEmail;
                }
                $checkEmptyFields = $this->helper->checkEmptyFields([
                    'Firstname' => $firstname,
                    'Surname' => $surname,
                    'Username' => $username,
                    'Email' => $email,
                    'Phone Number' => $phone_num
                ]);
                if ($checkEmptyFields) {
                    $checks[] = $checkEmptyFields;
                }
                $checkNoSpaces = $this->helper->checkNoSpaces([
                    'Email' => $email,
                    'Phone Number' => $phone_num
                ]);
                if ($checkNoSpaces) {
                    $checks[] = $checkNoSpaces;
                }
                $checks[] = $this->helper->checkLength('Username', $username, 4, 32);
                $checks[] = $this->helper->checkLength('Firstname', $firstname, 2, 32);
                $checks[] = $this->helper->checkLength('Surname', $surname, 2, 32);
                $checks[] = $this->helper->checkValidEmail($email);
                $checkAlpha = $this->helper->checkAlpha([
                    'Firstname' => $firstname,
                    'Surname' => $surname,
                    'Username' => $username,
                ]);
                if ($checkAlpha) {
                    $checks[] = $checkAlpha;
                }
                $checks[] = $this->helper->checkPhoneNumber($phone_num);
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
                        'uid' => $uid,
                        'username' => strtolower($username),
                        'firstname' => $firstname,
                        'surname' => $surname,
                        'email' => strtolower($email),
                        'phone_num' => $phone_num
                    ];
                    if (!empty($permissions)) {
                        $values['permissions'] = $permissions;
                    }
                    $this->usersTable->update($values);
                    echo '<p>User has been updated.</p>';
                    $showForm = false;
                }
            }
        if($showForm): ?>
        <form action="/admin/manageusers?id=<?= $uid ?>" method="POST">
            <input type="hidden" name="uid" value="<?= $user['uid'] ?? ''?>"/>
            <label>Username | <small>Username must be unique. Letters only.</small></label>
            <input type="text" name="username" value="<?= $user['username'] ?? '' ?>"/>
            <label>Firstname | <small>At least two characters. Letters only.</small></label>
            <input type="text" name="firstname" value="<?= $user['firstname'] ?? '' ?>"/>
            <label>Surname | <small>At least two characters. Letters only.</small></label>
            <input type="text" name="surname" value="<?= $user['surname'] ?? ''?>"/>
            <label>Email | <small>Ensure valid email format, e.g. john@gmail.com.</small></label>
            <input type="email" name="email" value="<?= $user['email'] ?? '' ?>"/>
            <label>Phone number | <small>UK Phone numbers only.</small></label>
            <input type="tel" name="phone_num" value="<?= $user['phone_num'] ?? '' ?>"/>
            <?php if ($_SESSION['permissions'] == 2) { ?>
                <label>Permissions | <small>Choose the user role.</small></label>
                <select name="permissions">
                    <option value="0" <?= $user['permissions'] == '0' ? 'selected' : '' ?>>User</option>
                    <option value="1" <?= $user['permissions'] == '1' ? 'selected' : '' ?>>Admin</option>
                    <option value="2" <?= $user['permissions'] == '2' ? 'selected' : '' ?>>Sysadmin</option>
                </select>
            <label>Delete This User? | <small>Permanently removes account.</small></label>
            <input type="checkbox" name="delete" style="width:15px; height:15px" />
            <?php } ?>
            <input type="submit" name="submit" value="Submit" />
            </form>
        <?php endif;
        } catch(\Exception $e) {
            // log error to nginx
            error_log('Error in editUserFunc() ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function contains the logic for displaying
     * all the current users and calling the func to edit them
     *
     *       example: a sysadmin can edit anyone
     *                an admin cannot edit a sysadmin
     *                   but can edit users
     *
     * @return void returns nothing
    */
    public function manageAccounts(): void {
        try {
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $users = $this->usersTable->find('uid', $_GET['id']);
                if (!empty($users)) {
                    $user = $users[0];
                } else {
                    echo '<p>User not found.</p>';
                    return;
                }
                // checking if the user is trying to edit their own profile first !
                if ($user['username'] == $_SESSION['username']) {
                    $this->editUserFunc($_GET['id']);
                }
                elseif ($_SESSION['permissions'] == '2') {
                    $this->editUserFunc($_GET['id']);
                }
                elseif ($_SESSION['permissions'] == '1' && $user['permissions'] == '0') {
                    $this->editUserFunc($_GET['id']);
                }
                elseif ($_SESSION['permissions'] == '1' && ($user['permissions'] == '1' || $user['permissions'] == '2')) {
                    echo '<p>You do not have the permissions to edit this user!</p>';
                }
                else {
                    echo '<p>You do not have the permissions to edit this user!</p>';
                }
            } else {
                $users = $this->usersTable->findAll('uid', 'ASC');
                echo '<h3>To set a user as admin, they must create an account first - news.v.je/account/signup</h3>';
                ?>
                <table>
                    <thead>
                    <tr>
                        <th style="text-align: left;">Role</th>
                        <th style="text-align: left;">UserID</th>
                        <th style="text-align: left;">Username</th>
                        <th style="text-align: left;">Email</th>
                        <th style="text-align: left;">Firstname</th>
                        <th style="text-align: left;">Surname</th>
                        <th style="text-align: left;">Edit User</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($users as $user) {
                        echo '<tr>';
                        switch ($user['permissions'] ?? '-') {
                            case '0':
                                echo '<td>User</td>';
                                break;
                            case '1':
                                echo '<td>Admin</td>';
                                break;
                            case '2':
                                echo '<td>Sys Admin</td>';
                                break;
                            default:
                                echo '<td>-</td>';
                                break;
                        }
                        echo '<td>' . ($user['uid'] ?? '-') . '</td>';
                        echo '<td>' . ($user['username'] ?? '-') . '</td>';
                        echo '<td>' . ($user['email'] ?? '-') . '</td>';
                        echo '<td>' . ($user['firstname'] ?? '-') . '</td>';
                        echo '<td>' . ($user['surname'] ?? '-') . '</td>';
                        // if user does not have an uid, set a 0 uid which triggers an error
                        echo '<td><a href="/admin/manageusers?id=' . ($user['uid'] ?? '0') . '">EDIT</a></td>';
                        echo '</tr>';
                    }
                    ?>
                    </tbody>
                </table>
                <?php
            }
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in manageAccounts() ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function contains the logic for displaying
     * all the current customer enquiries it only sets it as complete if the inquiry is
     * not found
     * @return void does not return anything
    */
    public function displayInquiries(): void {
        try {
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $rows = $this->inquiriesTable->find('id', $_GET['id']);
                if (!empty($rows)) {
                    $this->inquiriesTable->update(
                        [
                            'id' => $_GET['id'],
                            'status' => 'Complete'
                        ]);
                    echo '<p>Inquiry has been set as completed.</p>';
                } else {
                    echo '<p>Inquiry not found.</p>';
                }
            } else {
                $inquiries = $this->inquiriesTable->findAll('date', 'DESC');
                if (!empty($inquiries)) {
                    foreach ($inquiries as $inquiry) {
                        ?>
                        <table>
                            <thead>
                            <tr>
                                <th style="text-align: left;">Title</th>
                                <th style="text-align: left;">UserID</th>
                                <th style="text-align: left;">Firstname</th>
                                <th style="text-align: left;">Surname</th>
                                <th style="text-align: left;">Phone Num.</th>
                                <th style="text-align: left;">Email</th>
                                <th style="text-align: left;">Date</th>
                                <th style="text-align: left;">Status</th>
                                <?php if ($inquiry['status'] !== 'Complete') { ?>
                                    <th style="text-align: left;">Mark As Done</th>
                                <?php } ?>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><?= $inquiry['title'] ?? '' ?></td>
                                <td><?= $inquiry['username'] ?? '' ?></td>
                                <td><?= $inquiry['firstname'] ?? '' ?></td>
                                <td><?= $inquiry['surname'] ?? '' ?></td>
                                <td><?= $inquiry['phone_num'] ?? '' ?></td>
                                <td><?= $inquiry['email'] ?? '' ?></td>
                                <td><?= $inquiry['date'] ?? '' ?></td>
                                <td><?= $inquiry['status'] ?? '' ?></td>
                                <?php if ($inquiry['status'] !== 'Complete') { ?>
                                    <td>
                                        <a href="/admin/inquiries?id=<?=$inquiry['id'];?>">DONE</a>
                                    </td>
                                <?php } ?>
                            </tr>
                            <tr>
                                <td style="border-bottom: 0; padding-bottom: 50px">
                                    <p><b>Customer Wrote: </b><?= $inquiry['inquiry'] ?? '' ?></p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <?php
                    }
                } else {
                    echo '<p>Inquiries not found, check the database.</p>';
                }
            }
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in displayInquiries() ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function contains the logic for adding categories
     * in addcategory.html.php, it performs various checks before inserting a category name
     * @return void returns nothing
    */
    public function addCategoryFunc(): void {
        try {
            if (isset($_POST['submit']) && $_POST['randchk'] == $_SESSION['rand']) {
                extract($_POST);
                $this->helper->sanitizePostInput();
                $checks=[];
                $checkEmptyFields = $this->helper->checkEmptyFields([
                    'Category Name' => $name,
                ]);
                if ($checkEmptyFields) {
                    $checks[] = $checkEmptyFields;
                }
                $checks[] = $this->helper->checkLength('Category Name', $name, 2, 32);
                $checkAlpha = $this->helper->checkAlpha([
                    'Category Name' => $name,
                ]);
                if ($checkAlpha) {
                    $checks[] = $checkAlpha;
                }
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
                    $this->categoriesTable->insert(['name' => $name]);
                    echo 'Category added.';
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
            <form action="addcategory" method="POST">
                <input type="hidden" value="<?php echo $rand; ?>" name="randchk">
                <label>Category name | <small>Must be 2 to 32 letters.</small></label>
                <input type="text" name="name" />
                <input type="submit" value="Submit" name="submit" />
            </form>
            <?php
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in addCategoryFunc(). ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function contains the logic for deleting an image
     * @return void returns nothing
    */
    public function deleteImg(string $imagePath): void {
        try {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
                echo '<p>Old image deleted.</p>';
            } else {
                echo '<p>Image file not found for deletion.</p>';
            }
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in deleteImg()  ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function is used to validate the image
     * upload from edit article or add article
     *
     * [in code reference]
     * W3Schools.com. PHP File Upload.
     *      @see https://www.w3schools.com/php/php_file_upload.asp
    */
    public function uploadImg(): ?string {
        try {
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $relativeDir = '/images/upload/';
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . $relativeDir;
                $targetFile = $uploadDir . basename($_FILES['image']['name']);
                $relativeFile = $relativeDir . basename($_FILES['image']['name']);
                $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                $allowedFileTypes = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'cur', 'apng', 'svg', 'jfif', 'pjpeg', 'pjp'];
                $check = getimagesize($_FILES['image']['tmp_name']);
                if ($check === false) {
                    echo '<p>File is not an image. If you wish to add an image, add one that meets the requirements, by editing the article.<p/> ';
                    return null;
                }
                if ($_FILES['image']['size'] > 5000000) {
                    echo '<p>File is too big, max size is 5MB. If you wish to add an image, add one that meets the requirements, by editing the article.<p/>';
                    return null;
                }
                if (!in_array($imageFileType, $allowedFileTypes)) {
                    echo '<p>Invalid file type. Only JPG, JPEG, PNG, GIF, ICO, CUR, APNG, SVG, JFIF, PJPEG, PJP are allowed. If you wish to add an image, add one that meets the requirements, by editing the article.<p/> ';
                    return null;
                }
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    echo '<p>The file ' . basename($_FILES['image']['name']) . ' has been uploaded.<p/> ';
                    return $relativeFile;
                } else {
                    echo '<p>Sorry, there was an error uploading your file.<p/> ';
                    return null;
                }
            } else {
                echo 'No file uploaded or upload error.<p/> ';
                return null;
            }
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in uploadImg(). ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
            return null;
        }
    }
    /**
     * This function contains the logic for adding articles
     * in addarticle.html.php
     * @return void returns nothing
     */
    public function addArticleFunc(): void {
        try {
            if (isset($_POST['submit']) && $_POST['randchk'] == $_SESSION['rand']) {
                extract($_POST);
                $this->helper->sanitizePostInput();
                $checks = [];
                $checkEmptyFields = $this->helper->checkEmptyFields([
                    'Article Title' => $title,
                    'Article Text' => $description,
                    'Category ID' => $categoryId,
                ]);
                if ($checkEmptyFields) {
                    $checks[] = $checkEmptyFields;
                }
                $checks[] = $this->helper->checkLength('Article Title', $title, 2, 255);
                $checks[] = $this->helper->checkLength('Article Text', $description, 10, 5000);
                $checkAlpha = $this->helper->checkAlpha([
                    'Article Title' => $title,
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
                    echo '<p>We could not send your form, please ensure the following: </p>';
                    foreach ($checks as $error) {
                        if ($error) {
                            echo $error;
                        }
                    }
                } else {
                    $imagePath = $this->uploadImg();
                    $this->articlesTable->insert(
                        [
                            'title' => $title,
                            'description' => $description,
                            'categoryId' => $categoryId,
                            'date' => (new \DateTime())->format('Y-m-d H:i:s'),
                            'uid' => $_SESSION['uid'],
                            'path' => $imagePath // IMAGE PATH can be NN in db!
                        ]
                    );
                    echo '<p>Article added.</p>';
                }
            }
            $categories = $this->categoriesTable->findAll();
            /**
             * [in code reference]
             * Stackoverflow.com. Preventing form resubmission via session variable.
             *      @see https://stackoverflow.com/a/38768140
             */
            $rand = rand();
            $_SESSION['rand'] = $rand;
            ?>
            <form action="addarticle" method="POST" enctype="multipart/form-data">
                <input type="hidden" value="<?php echo $rand; ?>" name="randchk">
                <label>Category</label>
                <?php if (!empty($categories)) { ?>
                    <select name="categoryId">
                        <?php foreach ($categories as $category):
                            if (!empty($category['id']) && !empty($category['name'])): ?>
                            <option value="<?= $category['id']; ?>"><?php echo $category['name']; ?></option>
                        <?php endif;
                        endforeach; ?>
                    </select>
                <?php } else {
                    echo '<p>No categories found in the database, please check the database.</p>';
                } ?>
                <label>Article title | <small>Must be 2 to 255 characters.</small></label>
                <input type="text" name="title" />
                <label>Article text | <small>Must be 10 to 5000 characters.</small></label>
                <textarea name="description"></textarea>
                <!--
                    [in code reference]
                    W3Schools.com. HTML Images. List of acceptable extensions
                            @see https://www.w3schools.com/html/html_images.asp
                -->
                <label>Article image | <small>Acceptable formats: APNG, GIF, ICO, JPEG, PNG, SVG</small></label>
                <input type="file" id="image" name="image" accept=".jpg, .jpeg, .png, .gif, .ico, .cur, .apng, .svg, .jfif, .pjpeg, .pjp'"/>
                <input type="submit" value="Submit" name="submit" />
            </form>
            <?php
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in addArticleFunc(). ' . $e->getMessage());
            echo $e->getMessage();
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function contains the logic for listing articles
     * in articles.html.php
     * @return void returns nothing
    */
    public function articlesFunc(): void {
        try {
            $stmt = $this->articlesTable->findAll();
            if (!empty($stmt)) {
                echo '<table>';
                foreach ($stmt as $article) {
                    echo '<tr>';
                    echo '<td>' . ($article['title'] ?? 'Unknown') . '</td>';
                    echo '<td><a href="editarticle?id=' . ($article['id'] ?? '0') . '">Edit</a></td>';
                    echo '<td><a href="deletearticle?id=' . ($article['id'] ?? '0')  . '">Delete</a></td>';
                    echo '</td>';
                }
                echo '</table>';
            } else {
                echo '<p>No articles found in the database, please check the database.</p>';
            }
        } catch (\Exception $e) {
            // log the error to nginx
            error_log('Error in articlesFunc(): '. $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function contains the logic for listing categories
     * in categories.html.php
     * @return void returns nothing
     */
    public function categoriesFunc(): void {
        try {
            $stmt = $this->categoriesTable->findAll();
            if (!empty($stmt)) {
                echo '<table>';
                foreach ($stmt as $category) {
                    echo '<tr>';
                    echo '<td>' . ($category['name'] ?? 'Unknown') . '</td>';
                    echo '<td><a href="editcategory?id=' . ($category['id'] ?? '0') . '">Edit</a></td>';
                    echo '<td><a href="deletecategory?id=' . ($category['id'] ?? '0') . '">Delete</a></td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p>No categories found in the database, please check the database.</p>';
            }
        } catch (\Exception $e) {
            // log the error to nginx or a custom log file
            error_log('Error in categoriesFunc(): ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function contains the logic for deleting articles
     * in deletearticle.html.php
     */
    public function deleteArticleFunc(): void {
        try {
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $articles = $this->articlesTable->find('id', $_GET['id']);
                if (!empty($articles)) {
                    $article = $articles[0];
                    if (isset($article['path'])) {
                        $this->deleteImg($article['path']);
                    }
                    if ($this->articlesTable->delete($_GET['id'])) {
                        echo '<p>Article ' . ($article['title'] ?? 'Unknown') . ' deleted successfully.</p>';
                    } else {
                        echo '<p>Failed to delete the article. Please try again later.</p>';
                    }
                } else {
                    echo '<p>Article not found.</p>';
                }
            } else {
                echo '<p>Article not found.</p>';
            }
        } catch (\Exception $e) {
            // log the error to nginx
            error_log('Error in deleteArticleFunc(): ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function contains the logic for deleting categories
     * in deletecategory.html.php
     */
    public function deleteCategoryFunc(): void {
        try {
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $categoryDeleted = $this->categoriesTable->delete($_GET['id']);
                if (!empty($categoryDeleted)) {
                    echo '<p>Category deleted successfully.</p>';
                } else {
                    echo '<p>Failed to delete the category. Please try again later.</p>';
                }
            } else {
                echo '<p>Category not found.</p>';
            }
        } catch (\Exception $e) {
            // log the error to ngninx
            error_log('Error in deleteCategoryFunc(): ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function contains the logic for editing articles
     * in editarticle.html.php
     */
    public function editArticleFunc(): void {
        try {
            if (isset($_GET['id'])) {
                $articles = $this->articlesTable->find('id', $_GET['id']);
                if (empty($articles)) {
                    echo '<p>Article not found.</p>';
                    return;
                } else {
                    $article = $articles[0];
                }
            }
            $categories = $this->categoriesTable->findAll();
            $showForm=true;
            if (isset($_POST['submit']) && $_POST['randchk'] == $_SESSION['rand']) {
                extract($_POST);
                $this->helper->sanitizePostInput(); // Sanitize POST input
                $checks = [];
                $checkEmptyFields = $this->helper->checkEmptyFields([
                    'Article Title' => $title,
                    'Article Text' => $description,
                    'Category ID' => $categoryId,
                ]);
                if ($checkEmptyFields) {
                    $checks[] = $checkEmptyFields;
                }
                $checks[] = $this->helper->checkLength('Article Title', $title, 2, 255);
                $checks[] = $this->helper->checkLength('Article Text', $description, 10, 5000);
                $checkAlpha = $this->helper->checkAlpha([
                    'Article Title' => $title,
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
                    echo '<p>We could not process your request. Please ensure the following:</p>';
                    foreach ($checks as $error) {
                        if ($error) {
                            echo $error;
                        }
                    }
                } else {
                    // VERY IMPORTANT CODE, doesnt reset the path on a normal edit of the articlw
                    $imagePath=$article['path'];
                    if (isset($_POST['delete'])) {
                        if (!empty($article['path'])) {
                            $this->deleteImg($article['path']);
                            $imagePath = null;
                        }
                    }
                    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                        $newImagePath = $this->uploadImg();
                        $imagePath = $newImagePath;
                    }
                    $values = [
                        'id' => $article['id'],
                        'title' => $title,
                        'description' => $description,
                        'categoryId' => $categoryId,
                        'date' => (new \DateTime())->format('Y-m-d H:i:s'),
                        'path' => $imagePath
                    ];
                    $this->articlesTable->update($values);
                    echo '<p>Article edited successfully.</p>';
                    $showForm = false;
                }
            }
            /**
             * [in code reference]
             * Stackoverflow.com. Preventing form resubmission via session variable.
             *      @see https://stackoverflow.com/a/38768140
             */
            $rand = rand();
            $_SESSION['rand'] = $rand;
            if ($showForm):
            ?>
            <form action="editarticle?id=<?= $_GET['id'] ?? '0' ?>" method="POST" enctype="multipart/form-data">
                <label>Category | <small>Choose a category for this article.</small></label>
                <input type="hidden" value="<?php echo $rand; ?>" name="randchk">
                <select name="categoryId">
                    <?php foreach ($categories as $category):
                        if (!empty($category['id']) && !empty($category['name']) && !empty($article['categoryId'])):?>
                            <option value="<?php echo $category['id']; ?>"
                                <?php if ($article['categoryId'] === $category['id']) echo 'selected="selected"'; ?>>
                                    <?= $category['name'] ?>
                            </option>
                        <?php endif;
                    endforeach; ?>
                </select>
                <label>Article title | <small>Must be 2 to 255 characters.</small></label>
                <input type="text" name="title" value="<?= $article['title'] ?? 'Unknown' ?>" />
                <label>Article text | <small>Must be 10 to 5000 characters.</small></label>
                <textarea name="description"><?= $article['description'] ?? 'Unknown' ?></textarea>
                <label>Upload New Image | <small>Tick "Delete Old Image" if uploading new one.</small></label>
                <input type="file" id="image" name="image" accept=".apng, .gif, .jpg, .jpeg, .png" />
                <input type="submit" value="Submit" name="submit" />
                <?php if (!empty($article['path'])): ?>
                    <p style="display: flex; flex-direction: column; padding-right: 2em; padding-left: 2em; padding-top:2em;">
                        <img src="<?= $article['path'] ?>" alt="" style="max-width: 250px; height: auto;" />
                    </p>
                    <p style="display: flex; align-items: center; padding-right: 2em; padding-left: 2em;">
                        <label>Delete Current Image? | <small>Tick if uploading new one.</small></label>
                        <input type="checkbox" name="delete" style="width:15px; height:15px" />
                    </p>
                <?php endif; ?>
            </form>
            <?php
            endif;
        } catch (\Exception $e) {
            // log error to nginx
            error_log('Error in editArticleFunc(): ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }
    /**
     * This function contains the logic for editing categories
     * in editcategory.html.php
    */
    public function editCategoryFunc(): void {
        try {
            if (isset($_GET['id'])) {
                $category = $this->categoriesTable->find('id', $_GET['id']);
                if (empty($category)) {
                    echo '<p>Category not found.</p>';
                    return;
                } else {
                    $category = $category[0];
                }
            }
            $showForm=true;
            if (isset($_POST['submit']) && $_POST['randchk'] == $_SESSION['rand']) {
                extract($_POST);
                $this->helper->sanitizePostInput();
                $checks = [];
                $checkEmptyFields = $this->helper->checkEmptyFields([
                    'Category Name' => $name,
                ]);
                if ($checkEmptyFields) {
                    $checks[] = $checkEmptyFields;
                }
                $checks[] = $this->helper->checkLength('Category Name', $name, 2, 32);
                $checkAlpha = $this->helper->checkAlpha([
                    'Category Name' => $name,
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
                    echo '<p>We could not process your request. Please ensure the following:</p>';
                    foreach ($checks as $error) {
                        if ($error) {
                            echo $error;
                        }
                    }
                } else {
                    $this->categoriesTable->update([
                        'id' => $id,
                        'name' => $name
                    ]);
                    echo '<p>Category edited successfully.</p>';
                    $showForm = false;
                }
            }
            /**
             * [in code reference]
             * Stackoverflow.com. Preventing form resubmission via session variable.
             * @see https://stackoverflow.com/a/38768140
             */
            $rand = rand();
            $_SESSION['rand'] = $rand;
            if ($showForm):
            ?>
            <form action="editcategory?id=<?= $_GET['id'] ?? '0' ?>" method="POST">
                <input type="hidden" value="<?php echo $rand; ?>" name="randchk">
                <input type="hidden" name="id" value="<?= $category['id'] ?? 'Unknown' ?>">
                <label>Category Name | <small>Must be 2 to 32 letters.</small></label>
                <input type="text" name="name" value="<?= $category['name'] ?? 'Unknown' ?>" />
                <input type="submit" value="Submit" name="submit" />
            </form>
            <?php
            endif;
        } catch (\Exception $e) {
            // Log error to nginx
            error_log('Error in editCategoryFunc(): ' . $e->getMessage());
            echo '<p>We could not process your request. Please try again later.</p>';
        }
    }

}