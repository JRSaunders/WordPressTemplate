<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <!-- Primary WordPress Head Function -->
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<!-- top margin 0 for the first element -->
<header id="masthead" class="site-header">
    <div class="container">
        <div class="always-header">
        <div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="main-nav-link">
                    <h1 class="site-title"><?php bloginfo( 'name' ); ?></h1>
                </a>
			<?php endif; ?>
        </div>
        <div class="site-search">
			<?php get_search_form(); ?>
        </div>
        <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><i class="fa-solid fa-bars"></i></button>
        </div>
        <nav id="site-navigation" class="main-navigation">


			<?php
			// Get all registered menus
			$all_menus = get_registered_nav_menus();

			if ( ! empty( $all_menus ) ) {
				foreach ( $all_menus as $location => $description ) {
					if($description == 'Primary Menu') {
						echo '<h3 class="nav-title">' . esc_html( $description ) . '</h3>';
						wp_nav_menu( array(
							'theme_location' => $location,
							'fallback_cb'    => 'wp_page_menu', // Fallback to displaying pages if no menu is assigned
							'container'      => false,
							'menu_class'     => 'menu',
						));
					}
				}
			}
			?>
        </nav><!-- #site-navigation -->
    </div><!-- .container -->
</header><!-- #masthead -->