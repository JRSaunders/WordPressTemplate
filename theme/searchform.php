<form role="search" method="get" class="search-form flex items-center" action="<?php echo esc_url(home_url('/')); ?>">
    <label class="sr-only" for="search-field"><?php esc_html_e('Search for:', 'xoppio-base-theme'); ?></label>
    <input type="search" id="search-field" class="search-field" placeholder="<?php echo esc_attr_x('Search...', 'placeholder', 'xoppio-base-theme'); ?>" value="<?php echo get_search_query(); ?>" name="s" /><button type="submit" class="search-submit">
        <i class="fas fa-search"></i> <!-- FontAwesome magnifying glass icon -->
        <span class="sr-only"><?php esc_html_e('Search', 'xoppio-base-theme'); ?></span>
    </button>
</form>