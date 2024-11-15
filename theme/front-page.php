<?php
/**
 * The main template file
 *
 * @package Xoppio_Base_Theme
 */

get_header();
$bannerVolume = get_theme_mod('banner_volume', 0);
$bannerVolumeStart = get_theme_mod('banner_volume_start', 0);
$cssOnOrMuteforBanner = ($bannerVolume) ? 'fa-volume-up' : 'fa-volume-mute';
?>
</div>
    <div class="site-front-page">
        <main id="primary" class="site-main">
            <!-- Front Page Content -->
            <!-- Banner -->

            <section class="main-banner">
                <?php

                if($bannerVolumeStart){
                ?>
                <div class="banner-video-control">
                    <div class="volume-control">
                        <button id="mute-button" class="button" onclick="toggleMute()">
                            <i class="fas <?php echo $cssOnOrMuteforBanner; ?>"></i>
                        </button>
                    </div>
                </div>
                <?php
                }
                ?>
                <div class="banner-title-holder">
                    <div class="container">
                        <h1><?php echo esc_html(get_theme_mod('banner_title', get_bloginfo('name'))); ?></h1>
                    </div>
                </div>
                <div class="title-holder">
                    <div class="container">
                        <?php echo esc_html(get_theme_mod('banner_subtitle', get_bloginfo('description'))); ?>
                    </div>

                </div>
                <div class="banner-button-holder">
                    <a class="button" href="<?php echo esc_url(get_theme_mod('banner_button_url', '#')); ?>">
                        <?php echo esc_html(get_theme_mod('banner_button_text', 'Learn More')); ?>
                    </a>
                </div>

                <?php
                if ($showBanner = get_theme_mod('show_banner_video')) {
                    $videoId = get_theme_mod('banner_yt_id', '1FpGshiEW_E');
                    ?>
                    <div class="youtube-video-container" style="display: none;">
                        <div id="youtube-video"></div>
                    </div>
                    <script src="https://www.youtube.com/iframe_api"></script>
                    <script>
                        var player;
                        var plays = 0;
                        var volume = <?php echo esc_js($bannerVolume); ?>;
                        if (volume) {
                            volume = 0;
                        } else {
                            volume = 1;
                        }
                        var loopLimit = <?php echo esc_js(get_theme_mod('banner_video_loop', 0)); ?>;
                        var banner_video_play_length = <?php echo esc_js(get_theme_mod('banner_video_play_length', 0)); ?>; // Corrected

                        function onYouTubeIframeAPIReady() {
                            player = new YT.Player('youtube-video', {
                                videoId: '<?php echo esc_js($videoId); ?>',
                                playerVars: {
                                    autoplay: 1,
                                    controls: 1,
                                    mute: volume,
                                    loop: 1,
                                    playlist: '<?php echo esc_js($videoId); ?>', // Necessary for looping
                                    rel: 0,
                                    modestbranding: 1
                                },
                                events: {
                                    onReady: function (event) {
                                        event.target.mute();
                                        event.target.playVideo();
                                    },
                                    onStateChange: function (event) {
                                        if (event.data === YT.PlayerState.PLAYING) {
                                            document.getElementsByClassName('youtube-video-container')[0].style.display = 'block';
                                            var len = banner_video_play_length * 1000;
                                            plays++;
                                            if (plays > loopLimit && loopLimit !== 0) {
                                                player.stopVideo(); // Corrected method to stop video
                                                document.getElementById('youtube-video').remove();
                                                console.log('Video removed');
                                                return;
                                            } else {
                                                setTimeout(function () {
                                                    player.seekTo(0);
                                                    player.playVideo();
                                                }, len);
                                            }
                                        }

                                        if (event.data === YT.PlayerState.ENDED) {
                                            player.seekTo(0);
                                            player.playVideo();
                                        }
                                    }
                                }
                            });
                        }
                        function toggleMute() {
                            if (player.isMuted()) {
                                player.unMute();
                                document.getElementById('mute-button').innerHTML = '<i class="fas fa-volume-up"></i>';
                            } else {
                                player.mute();
                                document.getElementById('mute-button').innerHTML = '<i class="fas fa-volume-mute"></i>';
                            }
                        }
                    </script>
                    <?php
                }
                ?>
            </section>

            <section>
                <div class="container">
                    <?php
                    if (have_posts()) :
                        while (have_posts()) :
                            the_post();
                            ?>
                            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                                <div class="entry-content">
                                    <?php
                                    if (get_the_content()) {
                                        the_content();
                                    } else {
                                        echo '<p>' . esc_html__('No content available.', 'xoppio-base-theme') . '</p>';
                                    }
                                    ?>
                                </div><!-- .entry-content -->
                            </article><!-- #post-<?php the_ID(); ?> -->
                        <?php
                        endwhile;
                    else :
                        ?>
                        <p><?php esc_html_e('No posts found', 'xoppio-base-theme'); ?></p>
                    <?php
                    endif;
                    ?>
                </div><!-- .container -->
            </section>
<?php if(get_theme_mod('show_products_on_homepage', true)){ ?>
            <section class="woo-commerce-front-page">
                <div class="container">
                    <!-- get products by category -->
                    <div class="woocommerce products">
                        <?php
                        $args = array(
                            'post_type' => 'product',
                            'posts_per_page' => 6,
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'product_cat',
                                    'field' => 'slug',
                                    'terms' => 'featured',
                                ),
                            ),
                        );
                        $loop = new WP_Query($args);
                        while ($loop->have_posts()) : $loop->the_post();
                            wc_get_template_part('content', 'product');
                        endwhile;
                        wp_reset_postdata();
                        ?>
                </div>
            </section>
<?php } ?>
        </main><!-- #primary -->
    </div>

<?php
get_footer();
?>