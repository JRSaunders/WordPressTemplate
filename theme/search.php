<?php
/**
 * The template for displaying search results pages
 *
 * @package Xoppio_Base_Theme
 */

get_header(); ?>

    <main id="primary" class="site-main bg-gray-50 py-8">
        <div class="container mx-auto px-6">
            <header class="page-header mb-6">
				<?php if ( have_posts() ) : ?>
                    <h1 class="page-title text-3xl font-bold text-gray-800">
						<?php
						printf(
							esc_html__( 'Search Results for: %s', 'xoppio-base-theme' ),
							'<span class="text-primary-color">' . get_search_query() . '</span>'
						);
						?>
                    </h1>
				<?php else : ?>
                    <h1 class="page-title text-3xl font-bold text-gray-800"><?php esc_html_e( 'Nothing Found', 'xoppio-base-theme' ); ?></h1>
				<?php endif; ?>
            </header><!-- .page-header -->

			<?php
			if ( have_posts() ) :
				/* Start the Loop */
				while ( have_posts() ) :
					the_post();
					?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('bg-white shadow-md rounded-lg p-4 mb-6 flex items-start'); ?>>
						<?php if ( 'product' === get_post_type() ) : ?>
                            <div class="entry-image mr-4">
								<?php echo woocommerce_get_product_thumbnail(); // Display product image ?>
                            </div>
						<?php endif; ?>

						<?php if ( 'post' === get_post_type() ) : ?>
                            <div class="entry-image mr-4">
								<?php echo get_the_post_thumbnail( get_the_ID(), 'medium_large' ); ?>
                            </div>
						<?php endif; ?>

                        <div class="entry-content flex-1">
                            <header class="entry-header mb-2">
                                <h2 class="entry-title text-2xl font-semibold text-gray-900">
                                    <!--- add description in the search results --->
                                    <a href="<?php the_permalink(); ?>" rel="bookmark" class="hover:text-primary-color"><?php the_title(); ?></a>
                                </h2>
                            </header><!-- .entry-header -->

							<?php if ( 'product' === get_post_type() ) : ?>
                                <div class="entry-summary text-gray-600 mb-2">
									<?php woocommerce_template_loop_price(); ?>
                                </div>
                                <div class="entry-actions">
									<?php woocommerce_template_loop_add_to_cart(); ?>
                                </div><!-- .entry-actions -->
							<?php else : ?>
                                <div class="entry-summary">
                                    <p class="text-gray-600"><?php the_excerpt(); ?></p>
                                </div><!-- .entry-summary -->
							<?php endif; ?>
                        </div><!-- .entry-content -->
                    </article><!-- #post-<?php the_ID(); ?> -->
				<?php
				endwhile;

				// Pagination
				the_posts_pagination( array(
					'prev_text' => __( 'Previous', 'xoppio-base-theme' ),
					'next_text' => __( 'Next', 'xoppio-base-theme' ),
					'class'     => 'flex justify-between mt-6',
				) );

			else :
				?>
                <div class="no-results not-found text-center">
                    <p class="text-gray-600"><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with different keywords.', 'xoppio-base-theme' ); ?></p>
					<?php get_search_form(); ?>
                </div><!-- .no-results -->
			<?php
			endif;
			?>
        </div><!-- .container -->
    </main><!-- #primary -->

<?php
get_footer();