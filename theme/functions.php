<?php
/**
 * Xoppio Base Theme functions and definitions
 *
 * @package Xoppio_Base_Theme
 */

if ( ! function_exists( 'xoppio_base_theme_setup' ) ) :

	function hex_rgba($color, $opacity = false) {
		$default = 'rgba(0,0,0,0)';
		if (empty($color)) return $default;
		$color = str_replace('#', '', $color);
		if (strlen($color) == 3) {
			$r = hexdec(substr($color, 0, 1) . substr($color, 0, 1));
			$g = hexdec(substr($color, 1, 1) . substr($color, 1, 1));
			$b = hexdec(substr($color, 2, 1) . substr($color, 2, 1));
		} else {
			$r = hexdec(substr($color, 0, 2));
			$g = hexdec(substr($color, 2, 2));
			$b = hexdec(substr($color, 4, 2));
		}
		$opacity = $opacity === false ? 1 : $opacity;
		return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $opacity . ')';
	}
	function xoppio_base_theme_setup() {
		// Make theme available for translation
		load_theme_textdomain( 'xoppio-base-theme', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head
		add_theme_support( 'automatic-feed-links' );

		// Let WordPress manage the document title
		add_theme_support( 'title-tag' );

		// Enable support for Post Thumbnails on posts and pages
		add_theme_support( 'post-thumbnails' );

		// Register primary and footer menus
		register_nav_menus( array(
			'menu-1' => esc_html__( 'Primary Menu', 'xoppio-base-theme' ),
			'footer' => esc_html__( 'Footer Menu', 'xoppio-base-theme' ),
		) );

		// Switch default core markup for search form, comment form, etc. to output valid HTML5
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Add support for custom logo
		add_theme_support( 'custom-logo', array(
			'height'      => 100,
			'width'       => 400,
			'flex-height' => true,
			'flex-width'  => true,
		) );

		// WooCommerce support
		add_theme_support( 'woocommerce' );
	}
endif;
add_action( 'after_setup_theme', 'xoppio_base_theme_setup' );

/**
 * Enqueue scripts and styles.
 */
function xoppio_base_theme_scripts() {
	// Enqueue the main stylesheet
	wp_enqueue_style( 'xoppio-base-theme-style', get_stylesheet_uri() );

	// Enqueue Google Fonts
	wp_enqueue_style( 'xoppio-google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;700&display=swap', [], null );

	wp_enqueue_style( 'xoppio-typekit-fonts', 'https://use.typekit.net/moi2dws.css', array(), null);

	// Enqueue a script (if needed)
	wp_enqueue_script( 'xoppio-navigation', get_template_directory_uri() . '/assets/js/navigation.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'xoppio_base_theme_scripts' );

function xoppio_include_products_in_search( $query ) {
	if ( $query->is_search() && ! is_admin() && $query->is_main_query() ) {
		$query->set( 'post_type', array( 'post', 'page', 'product' ) );
	}
}
add_action( 'pre_get_posts', 'xoppio_include_products_in_search' );

function enqueue_tailwind_assets() {
	wp_enqueue_style( 'tailwind', get_stylesheet_directory_uri() . '/assets/css/style.css', [], '1.0', 'all' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_tailwind_assets' );

function enqueue_font_awesome() {
	wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', [], '6.0.0' );
}
add_action( 'wp_enqueue_scripts', 'enqueue_font_awesome' );

function xoppio_customize_register( $wp_customize ) {
	// Add color settings
	$wp_customize->add_setting( 'primary_color', array(
		'default'   => '#3498db',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'primary_color', array(
		'label'    => __( 'Primary Color', 'xoppio-base-theme' ),
		'section'  => 'colors',
		'settings' => 'primary_color',
	) ) );
//
	$wp_customize->add_setting( 'secondary_color', array(
		'default'   => '#e74c3c',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'secondary_color', array(
		'label'    => __( 'Secondary Color', 'xoppio-base-theme' ),
		'section'  => 'colors',
		'settings' => 'secondary_color',
	) ) );
//
	$wp_customize->add_setting( 'accent_color', array(
		'default'   => '#2ecc71',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'accent_color', array(
		'label'    => __( 'Accent Color', 'xoppio-base-theme' ),
		'section'  => 'colors',
		'settings' => 'accent_color',
	) ) );
//
	$wp_customize->add_setting( 'bg_color', array(
		'default'   => '#ffffff',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'bg_color', array(
		'label'    => __( 'Background Color', 'xoppio-base-theme' ),
		'section'  => 'colors',
		'settings' => 'bg_color',
	) ) );

	$wp_customize->add_setting( 'footer_color', array(
		'default'   => '#f2f2f2',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'footer_color', array(
		'label'    => __( 'Footer Color', 'xoppio-base-theme' ),
		'section'  => 'colors',
		'settings' => 'footer_color',
	) ) );

	//header_color
	$wp_customize->add_setting( 'header_color', array(
		'default'   => '#f2f2f2',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'header_color', array(
		'label'    => __( 'Header Color', 'xoppio-base-theme' ),
		'section'  => 'colors',
		'settings' => 'header_color',
	) ) );

   // banner_link_cta_color
    $wp_customize->add_setting( 'banner_link_cta_color', array(
        'default'   => '#f2f2f2',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'banner_link_cta_color', array(
        'label'    => __( 'Banner Link CTA Color', 'xoppio-base-theme' ),
        'section'  => 'colors',
        'settings' => 'banner_link_cta_color',
    ) ) );

	//header_link_color
	$wp_customize->add_setting( 'header_link_color', array(
		'default'   => '#f2f2f2',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'header_link_color', array(
		'label'    => __( 'Header Link Color', 'xoppio-base-theme' ),
		'section'  => 'colors',
		'settings' => 'header_link_color',
	) ) );


	// Add font settings
	$wp_customize->add_section( 'font_section', array(
		'title'    => __( 'Fonts', 'xoppio-base-theme' ),
		'priority' => 30,
	) );

	$font_choices = array(
		'acumin-variable' =>'Acumin Variable' ,
		'Roboto' => 'Roboto',
		'Open Sans' => 'Open Sans',
		'Lato' => 'Lato',
		'Montserrat' => 'Montserrat',
		'Oswald' => 'Oswald',
		'Raleway' => 'Raleway',
		'PT Sans' => 'PT Sans',
		'Poppins' => 'Poppins',
		'Merriweather' => 'Merriweather',
		'Noto Sans' => 'Noto Sans',
		'Ubuntu' => 'Ubuntu',
		'Playfair Display' => 'Playfair Display',
		'Roboto Condensed' => 'Roboto Condensed',
		'Mukta' => 'Mukta',
		'Arvo' => 'Arvo',
		'Fira Sans' => 'Fira Sans',
		'Inconsolata' => 'Inconsolata',
		'Source Sans Pro' => 'Source Sans Pro',
		'Cabin' => 'Cabin',
		'Zilla Slab' => 'Zilla Slab',
		'Work Sans' => 'Work Sans',
		'Rubik' => 'Rubik',
		'Karla' => 'Karla',
		'Crimson Text' => 'Crimson Text',
		'Oxygen' => 'Oxygen',
		'Titillium Web' => 'Titillium Web',
		'Libre Baskerville' => 'Libre Baskerville',
		'Josefin Sans' => 'Josefin Sans',
		'Dosis' => 'Dosis',
		'Bitter' => 'Bitter',
		// Wacky Fonts
		'Comic Sans MS' => 'Comic Sans MS',
		'Papyrus' => 'Papyrus',
		'Lobster' => 'Lobster',
		'Permanent Marker' => 'Permanent Marker',
		'Indie Flower' => 'Indie Flower',
		'Fredericka the Great' => 'Fredericka the Great',
		'Caveat' => 'Caveat',
		'Chewy' => 'Chewy',
		'Gloria Hallelujah' => 'Gloria Hallelujah',
		'Bangers' => 'Bangers',
		'Monoton' => 'Monoton',
		'Rock Salt' => 'Rock Salt',
		'Pacifico' => 'Pacifico',
		'Shadows Into Light' => 'Shadows Into Light',
		'Amatic SC' => 'Amatic SC',
		'Press Start 2P' => 'Press Start 2P',
		'VT323' => 'VT323',
		'Creepster' => 'Creepster',
		'Luckiest Guy' => 'Luckiest Guy',
		'Special Elite' => 'Special Elite',
		'Chalkduster' => 'Chalkduster',
		'Handlee' => 'Handlee',
		'Just Another Hand' => 'Just Another Hand',
		'Satisfy' => 'Satisfy'
	);

	$wp_customize->add_setting( 'primary_font', array(
		'default'   => 'Roboto',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'primary_font', array(
		'label'    => __( 'Primary Font', 'xoppio-base-theme' ),
		'section'  => 'font_section',
		'type'     => 'select',
		'choices'  => $font_choices,
	) );

	$wp_customize->add_setting( 'secondary_font', array(
		'default'   => 'Open Sans',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'secondary_font', array(
		'label'    => __( 'Secondary Font', 'xoppio-base-theme' ),
		'section'  => 'font_section',
		'type'     => 'select',
		'choices'  => $font_choices,
	) );
}

//Add search section
function xoppio_customize_search( $wp_customize ) {
	$wp_customize->add_section( 'search_section', array(
		'title'    => __( 'Search', 'xoppio-base-theme' ),
		'priority' => 30,
	) );

	$wp_customize->add_setting( 'search_placeholder', array(
		'default'   => 'Search...',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'search_placeholder', array(
		'label'    => __( 'Search Placeholder', 'xoppio-base-theme' ),
		'section'  => 'search_section',
		'type'     => 'text',
	) );
	// add show in header
	$wp_customize->add_setting( 'show_search_in_header', array(
		'default'   => true,
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'show_search_in_header', array(
		'label'    => __( 'Show Search in Header', 'xoppio-base-theme' ),
		'section'  => 'search_section',
		'type'     => 'checkbox',
	) );
}

// homepage section
function xoppio_customize_homepage( $wp_customize ) {
	$wp_customize->add_section( 'homepage_section', array(
		'title'    => __( 'Homepage', 'xoppio-base-theme' ),
		'priority' => 30,
	) );

//add video banner
    $wp_customize->add_setting('banner_yt_id', array(
        'default' => '1FpGshiEW_E'
    ));

    $wp_customize->add_control('banner_yt_id', array(
        'label' => __('Banner YouTube ID', 'xoppio-base-theme'),
        'section' => 'homepage_section',
        'type'     => 'text',
    ));

    //banner show video
    $wp_customize->add_setting('show_banner_video', array(
        'default' => false,
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('show_banner_video', array(
        'label' => __('Show Banner Video', 'xoppio-base-theme'),
        'section' => 'homepage_section',
        'type'     => 'checkbox'
    ));

    //banner loop video count
    $wp_customize->add_setting('banner_video_loop', array(
        'default' => 1,
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('banner_video_loop', array(
        'label' => __('Banner Video Loop Count ( 0 = forever )', 'xoppio-base-theme'),
        'section' => 'homepage_section',
        'type'     => 'number'
    ));

    // play video length in seconds format and in milliseconds
    $wp_customize->add_setting('banner_video_play_length', array(
        'default' => 4,
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('banner_video_play_length', array(
        'label' => __('Banner Video Play Length (in seconds)', 'xoppio-base-theme'),
        'section' => 'homepage_section',
        'type'     => 'float'
    ));

//add banner
	//banner img
	$wp_customize->add_setting( 'banner_img', array(
		'default'   => get_template_directory_uri() . '/assets/images/bg/03.jpg',
		'sanitize_callback' => 'esc_url',
	) );


	$wp_customize->add_setting( 'show_banner', array(
		'default'   => true,
		'sanitize_callback' => 'sanitize_text_field',
	) );

	$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'banner_img', array(
		'label'    => __( 'Banner Image', 'xoppio-base-theme' ),
		'section'  => 'homepage_section',
		'settings' => 'banner_img',
	) ) );
	$wp_customize->add_control( 'show_banner', array(
		'label'    => __( 'Show Banner', 'xoppio-base-theme' ),
		'section'  => 'homepage_section',
		'type'     => 'checkbox',
	) );
	$siteName = get_bloginfo( 'name' );
	$wp_customize->add_setting( 'banner_title', array(
		'default'   => $siteName,
		'sanitize_callback' => 'sanitize_text_field',
	) );

    //banner volume on off
    $wp_customize->add_setting( 'banner_volume', array(
        'default'   => true,
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'banner_volume', array(
        'label'    => __( 'Banner Volume', 'xoppio-base-theme' ),
        'section'  => 'homepage_section',
        'type'     => 'checkbox',
    ) );

    //banner volume start on off
    $wp_customize->add_setting( 'banner_volume_start', array(
        'default'   => true,
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'banner_volume_start', array(
        'label'    => __( 'Banner Volume Start', 'xoppio-base-theme' ),
        'section'  => 'homepage_section',
        'type'     => 'checkbox',
    ) );


	$wp_customize->add_control( 'banner_title', array(
		'label'    => __( 'Banner Title', 'xoppio-base-theme' ),
		'section'  => 'homepage_section',
		'type'     => 'text',
	) );
	$siteTagline = get_bloginfo( 'description' );
	$wp_customize->add_setting( 'banner_subtitle', array(
		'default'   => $siteTagline,
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'banner_subtitle', array(
		'label'    => __( 'Banner Subtitle', 'xoppio-base-theme' ),
		'section'  => 'homepage_section',
		'type'     => 'text',
	) );

	$wp_customize->add_setting( 'banner_button_text', array(
		'default'   => 'Learn More',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'banner_button_text', array(
		'label'    => __( 'Banner Button Text', 'xoppio-base-theme' ),
		'section'  => 'homepage_section',
		'type'     => 'text',
	) );

	$wp_customize->add_setting( 'banner_button_url', array(
		'default'   => '#',
		'sanitize_callback' => 'esc_url',
	) );
	$wp_customize->add_control( 'banner_button_url', array(
		'label'    => __( 'Banner Button URL', 'xoppio-base-theme' ),
		'section'  => 'homepage_section',
		'type'     => 'url',
	) );

    //show products on homepage with in a section
    $wp_customize->add_setting( 'show_products_on_homepage', array(
        'default'   => true,
        'sanitize_callback' => 'sanitize_text_field',
    ) );
    $wp_customize->add_control( 'show_products_on_homepage', array(
        'label'    => __( 'Show Products on Homepage', 'xoppio-base-theme' ),
        'section'  => 'homepage_section',
        'type'     => 'checkbox',
    ) );


}

add_action( 'customize_register', 'xoppio_customize_search' );

add_action( 'customize_register', 'xoppio_customize_register' );

add_action( 'customize_register', 'xoppio_customize_homepage');
function xoppio_custom_styles() {
	$primary_color = get_theme_mod( 'primary_color', '#3498db' );
	$secondary_color = get_theme_mod( 'secondary_color', '#e74c3c' );
	$accent_color = get_theme_mod( 'accent_color', '#2ecc71' );
	$background_color = get_theme_mod( 'bg_color', '#ffffff' );

	$primary_font = get_theme_mod( 'primary_font', 'Roboto' );
	$secondary_font = get_theme_mod( 'secondary_font', 'Open Sans' );
	$footer_color = get_theme_mod( 'footer_color', '#f2f2f2' );
	$header_color = get_theme_mod( 'header_color', '#f2f2f2' );
	$header_link_color = get_theme_mod( 'header_link_color', '#f2f2f2' );
	$banner_img = get_theme_mod( 'banner_img', '/wp-content/themes/my-theme/assets/images/bg/03.jpg' );
    $banner_link_cta_color = get_theme_mod( 'banner_link_cta_color', '#f2f2f2' );
    $fullUrlofDomain = get_site_url();
	echo "<style>
        :root {
            --primary-color: $primary_color;
            --secondary-color: $secondary_color;
            --accent-color: $accent_color;
            --background-color: $background_color;
            --primary-font: '$primary_font', sans-serif;
            --secondary-font: '$secondary_font', sans-serif;
            --footer-color: $footer_color;
            --header-color: $header_color;
            --header-link-color: $header_link_color;
            --banner-link-cta-color: $banner_link_cta_color;
        }
   
        body, html {
            font-family: var(--primary-font);
            background-color: var(--background-color);
        }
        h1, h3, h4, h5, h6{
            font-family: var(--secondary-font);
            font-weight: bolder;
            font-size: 1.7em;
            font-stretch: extra-condensed;
            color: var(--secondary-color);
        }
		h1{
			font-size: 2.5em;
			color: var(--primary-color);
			font-stretch: normal;
        }

        a {
            font-family: var(--secondary-font);
            font-weight: bold;
            font-stretch: extra-condensed;
            color: var(--secondary-color);
        }
        .button {
            background-color: var(--accent-color);
            color: var(--secondary-color);
        }
        .main-banner{
        	background-image: url('$banner_img');
        	/* contain the image */
        	background-size: cover;
        	background-position: center;
        	background-repeat: no-repeat;
        	font-size: 16px;
        	font-weight: bold;
        	font-family: var(--secondary-font);
        	color: var(--background-color);
        	width:100%;
        	height: 430px;
        	/* align text to left bottom */
        	flex-direction: column;
        	justify-content: flex-end;
        	text-shadow: 1px 1px 2px rgba(0,0,0,0.7);
        	/* shift background down by 50px */
        
        	box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        .main-banner .title-holder{
        	padding: 12px 20px 12px 20px;
        	/* back ground opacity 0.5 use secondary color */
        	background-color: ".hex_rgba($secondary_color, 0.7).";
        	
        }
        @media (max-width: 768px) {
        	.main-banner .title-holder{
        		padding-bottom: 10px;
        	}
        	.main-banner{
        		height: 460px;
        		background-position: center;
        		/* cover */
        		background-size: cover;
        	}
        }
        .main-banner h1{
            letter-spacing: 2px;
        	flex: 1;
        	text-transform: uppercase;
        	font-size: 4.5em;
			color: var(--primary-color);
			line-height: 0.8em;
			
        }
        .banner-title-holder{
        	padding: 0 20px;
			/* back ground opacity 0.5 use secondary color */
			background-color: ".hex_rgba($background_color, 0.9).";
			padding-bottom: 0px;
        }
    
        .single_add_to_cart_button.button, .single_add_to_cart_button.button, .button.product_type_simple.add_to_cart_button.ajax_add_to_cart, input[type='submit']{
         
            min-width: 80px;
			background-color: var(--accent-color) !important;
			color: var(--secondary-color) !important;
			border: 2px solid var(--secondary-color) !important;
			font-family: var(--secondary-font) !important;
			text-transform: uppercase;
			 font-variation-settings: \"slnt\" 0, \"wdth\" 70, \"wght\" 700;
			 border-radius: 0.5em !important;
        }
        .input-text.qty.text{
        min-height: 25px;
        }
        
    
    
    /* Add the Font Awesome icon */
    a[href='".$fullUrlofDomain."/cart/']::after {
        content: '\\f07a'; /* Font Awesome cart icon code */
        display: inline-block;
        font-family: 'Font Awesome 5 Free';
        margin-left: 10px;
    }
    
    a[href='".$fullUrlofDomain."/basket/']::after {
        content: '\\f07a'; /* Font Awesome cart icon code */
        display: inline-block;
        font-family: 'Font Awesome 5 Free';
        margin-left: 10px;
    }
        
   

    </style>";
}
add_action( 'wp_head', 'xoppio_custom_styles' );
function xoppio_custom_search() {
	$show_search_in_header = get_theme_mod( 'show_search_in_header', true );
	if ( $show_search_in_header ) {
		echo "<style>
		.site-search {
			display: block;
		}
	</style>";
	} else {
		echo "<style>
		.site-search {
			display: none;
		}
	</style>";
	}
}
add_action( 'wp_head', 'xoppio_custom_search' );

function xoppio_custom_banner() {
	$show_banner = get_theme_mod( 'show_banner', true );
	if ( $show_banner ) {
		echo "<style>
		.main-banner {
			display: flex;
		}
	</style>";
	} else {
		echo "<style>
		.main-banner {
			display: none;
		}
	</style>";
	}
}
add_action( 'wp_head', 'xoppio_custom_banner' );
add_action( 'wp_footer', function(){
	$search_placeholder = get_theme_mod( 'search_placeholder', 'Search...' );
	echo "<script>
		document.addEventListener('DOMContentLoaded', function() {
			const searchField = document.querySelector('.search-field');
			if (searchField) {
				searchField.placeholder = '$search_placeholder';
			}
		});
	</script>";
});

function xoppio_enqueue_google_fonts() {
	$primary_font = get_theme_mod( 'primary_font', 'Roboto' );
	$secondary_font = get_theme_mod( 'secondary_font', 'Open Sans' );

	$font_families = array( $primary_font, $secondary_font );

	$font_families = array_map( function( $font ) {
		return str_replace( ' ', '+', $font );
	}, $font_families );

	foreach ( $font_families as $key => $font ) {
		if($font == 'Acumin Variable'){
			unset($font_families[$key]);
			continue;
		}
		$font_families[ $key ] =$font.':ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900';
	}


	$font_url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $font_families ) . '&display=swap';

	wp_enqueue_style( 'xoppio-google-fonts', $font_url, array(), null );
}
add_theme_support('post-thumbnails', array('post', 'page', 'product')); // Specify post types
add_action( 'wp_enqueue_scripts', 'xoppio_enqueue_google_fonts' );

