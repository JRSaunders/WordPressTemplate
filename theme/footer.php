<footer id="colophon" class="site-footer bg-gray-800 text-white py-6" style="position: relative; bottom: 0; width: 100%;">
    <div class="container footer-wrap mx-auto px-6">
        <nav id="footer-navigation" class="footer-navigation">
            <button class="menu-toggle bg-secondary-color text-white px-4 py-2 rounded-md mb-4" aria-controls="primary-menu" aria-expanded="false"><?php esc_html_e('Menu', 'xoppio-base-theme'); ?></button>

			<?php
			// Get all registered menus
			$all_menus = get_registered_nav_menus();

			if ( ! empty( $all_menus ) ) {
				foreach ( $all_menus as $location => $description ) {
					if($description == 'Footer Menu') {
						echo '<h3 class="nav-title text-lg font-semibold mb-2">' . esc_html($description) . '</h3>';
						wp_nav_menu(array(
							'theme_location' => $location,
							'fallback_cb'    => 'wp_page_menu', // Fallback to displaying pages if no menu is assigned
							'container'      => false,
							'menu_class'     => 'menu flex flex-col space-y-2',
						));
					}
				}
			} else {
				echo '<p>' . esc_html__('No menus found.', 'xoppio-base-theme') . '</p>';
			}
			?>
        </nav><!-- #footer-navigation -->
        <div class="site-info text-center mt-6">
            &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('All rights reserved.', 'xoppio-base-theme'); ?>
        </div><!-- .site-info -->
    </div><!-- .container -->
</footer><!-- #colophon -->
</body>
</html>
<?php wp_footer(); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const mainNavigation = document.querySelector('.main-navigation');
        const footer = document.querySelector('#colophon');

        if (menuToggle && mainNavigation) {
            menuToggle.addEventListener('click', function() {
                mainNavigation.style.display = mainNavigation.style.display === 'block' ? 'none' : 'block';
            });
        }




    });

    document.addEventListener('DOMContentLoaded', function() {
        //if #menu-main count of li is greater than 4
        if(document.querySelectorAll('#menu-main li').length > 4) {

            const searchForm = document.querySelector('.site-search');
            const logo = document.querySelector('.site-branding');
            const logoRect = logo.getBoundingClientRect();
            searchForm.style.position = 'absolute';
            searchForm.style.left = `${logoRect.right + 40}px`; // 20px padding from logo
            searchForm.style.top = `${logoRect.top}px`; // Align vertically with the logo
            searchForm.style.transform = 'translateY(0)';
            searchForm.style.marginTop = '30px'; // 25px margin from the top
            searchForm.style.zIndex = '10';
        }
    });


</script>