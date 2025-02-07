<!--

Main output for contact page

-->

<article>
    <h2><?=$title;?></h2>
    <p>You can contact us via the following communication channels:</p>
    <p> • Email: enquiries@website.com</p>
    <p> • Telephone: 07488888888</p>
    <?php
    /**
     * This code contains the contant form
     * which users can use to send inquiries
    */
    $contactController = new \Controllers\Contact;
    $contactController->submitContactForm();
    ?>
</article>
