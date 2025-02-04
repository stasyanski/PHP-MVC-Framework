<!--

Main output content for accounts page

-->
<nav>
    <?php
    /**
     * This code is used to make the sidebar on the side of the pages
     * specific to the account pages, which includes a login and signup
    */
    $accountController = new \Controllers\Account();
    echo $accountController->makeSideBar();
    ?>
</nav>


<article>
    <h2><?=$title?></h2>
    <?php
    /**
     * This code is used to call the login check function
     * which checks if a user is logged in, if not, allows for the creation of an account
    */
    $accountController->loginCheck(); ?>
</article>
