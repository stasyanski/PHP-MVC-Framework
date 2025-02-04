<!--

Main output for categories page, depending on get ID

-->

<nav>
    <?php
    /**
     * This code is used to make the sidebar on the side of the pages
     * specific to the public pages, THIS SIDEBAR IS NOT MADE VISIBLE ON ALL PAGES
     * as it was not visible on all pages in the initial website
     */
    $pagesController = new \Controllers\Home;
    echo $pagesController->makeSideBar();
    ?>
</nav>

<article>
    <h2><?=$title?></h2>
    <?php
    /**
     * This code takes the categoryId as a parameter and queries to display
     * all code with that certain category
     */
    $articleController = new \Controllers\Category();
    echo $articleController->displayCategories();
    ?>
</article>