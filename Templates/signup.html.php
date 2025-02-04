<!--

Main output content for signup page

-->
<nav>
    <?php
    /**
     * This code is used to make the sidebar on the side of the pages
     * specific to the public pages, THIS SIDEBAR IS NOT MADE VISIBLE ON ALL PAGES
     * as it was not visible on all pages in the initial website
    */
    $accountController = new \Controllers\Account();
    echo $accountController->makeSideBar();
    ?>
</nav>


<article>
    <h2><?=$title?></h2>
    <?php
    /**
     * This code is the form for submitting and
     * creating an account on the website
    */
    $accountController->makeAccount(); ?>
</article>