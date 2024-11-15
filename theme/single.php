<?php
/**
 * The main template file
 *
 * @package Xoppio_Base_Theme
 */

get_header(); ?>


    <main id="primary" class="site-main">
        <div class="container">
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white shadow-md rounded-lg p-6 mb-6'); ?>>
                        <header class="entry-header mb-4">
							<?php the_title( '<h1 class=" text-3xl font-bold text-gray-800">', '</h1>' ); ?>
                            <?php echo '<p>Written by ' . get_the_author() . ' on ' . get_the_date() . '</p>'; ?>
                        </header><!-- .entry-header -->
                        <div class="entry-content text-gray-700">
							<?php
							the_content();
							?>
                        </div><!-- .entry-content -->
                    </article><!-- #post-<?php the_ID(); ?> -->
                    <?php
                    // Include the comments template if comments are open or if there are comments
                    if (comments_open() || get_comments_number()) :
                        comments_template();
                    endif;
                    ?>
				<?php
				endwhile;
			else :
				?>
                <p class="text-center text-gray-600"><?php esc_html_e( 'No posts found', 'xoppio-base-theme' ); ?></p>
			<?php
			endif;
			?>
        </div><!-- .container -->
    </main><!-- #primary -->

<?php
get_footer();