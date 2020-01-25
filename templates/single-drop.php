<?php

wp_head();

?>
<main id="site-content" role="main">
    <?php
        $fileView = new \DropHTML\Frontend\ContentsView();
        echo $fileView->showFileList(get_the_ID());
    ?>
</main><!-- #site-content -->

<?php get_template_part('template-parts/footer-menus-widgets');

wp_footer(); ?>