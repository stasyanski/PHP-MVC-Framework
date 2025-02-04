<!--

Main output for the categories page

-->

<nav>
    <?php
    /**
     * This code is used to make the sidebar on the side of the pages
     * specific to the admin pages
    */
    $adminController = new \Controllers\Admin();
    echo $adminController->makeSideBar();
    ?>
</nav>

<article>
    <h2><?=$title?></h2>
    <?php
    /**
     * This statement checks that the user who is logged in either has permission 1 (admin) or 2 (sysadmin)
     * stored in SESSION var, and checks that is logged in, if not, call loginCheck
    */
    if (isset($_SESSION['loggedin'], $_SESSION['permissions']) && ($_SESSION['permissions'] == 1 || $_SESSION['permissions'] == 2)) {
        $adminController->categoriesFunc();
    }
    else {
        $adminController->loginCheck();
    } ?>
</article>
