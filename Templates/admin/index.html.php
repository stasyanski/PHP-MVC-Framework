<!--

Main output for the admin index page

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
    <?php $adminController->loginCheck(); ?>
</article>
