<?php get_header();?>
<main class="wrap entry-wrap">
<?php if(have_posts()):while(have_posts()):the_post();?>
 <article class="entry"><h1><?php the_title();?></h1><?php the_content();?></article>
<?php endwhile;else:?>
 <article class="entry"><h1>Nichts gefunden</h1><p>Diese Seite gibt es nicht.</p></article>
<?php endif;?>
</main>
<?php get_footer();?>
