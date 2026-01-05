<?php
/**
 * OceanWP Child Theme Functions
 *
 * UI/UX Improvements for Madhu Spices Japan
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue parent and child theme styles
 */
function oceanwp_child_enqueue_styles() {
    // Parent theme style
    wp_enqueue_style(
        'oceanwp-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme('oceanwp')->get('Version')
    );

    // Child theme style
    wp_enqueue_style(
        'oceanwp-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('oceanwp-style'),
        wp_get_theme()->get('Version')
    );

    // Google Fonts - Optimized loading
    wp_enqueue_style(
        'madhu-google-fonts',
        'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap',
        array(),
        null
    );
}
add_action('wp_enqueue_scripts', 'oceanwp_child_enqueue_styles');

/**
 * Enqueue custom JavaScript
 */
function oceanwp_child_enqueue_scripts() {
    wp_enqueue_script(
        'oceanwp-child-scripts',
        get_stylesheet_directory_uri() . '/js/custom.js',
        array('jquery'),
        wp_get_theme()->get('Version'),
        true
    );

    // Pass data to JavaScript
    $localize_data = array(
        'ajaxUrl' => admin_url('admin-ajax.php')
    );

    // Add WooCommerce URLs only if WooCommerce is active
    if (function_exists('WC')) {
        $localize_data['cartUrl'] = wc_get_cart_url();
        $localize_data['checkoutUrl'] = wc_get_checkout_url();
    }

    wp_localize_script('oceanwp-child-scripts', 'madhuData', $localize_data);
}
add_action('wp_enqueue_scripts', 'oceanwp_child_enqueue_scripts');

/**
 * Add skip navigation link for accessibility
 */
function oceanwp_child_skip_link() {
    ?>
    <a class="skip-link screen-reader-text" href="#main"><?php esc_html_e('Skip to content', 'oceanwp-child'); ?></a>
    <?php
}
add_action('wp_body_open', 'oceanwp_child_skip_link', 1);

/**
 * Add floating cart button for mobile
 */
function oceanwp_child_floating_cart() {
    if (!function_exists('WC') || is_cart() || is_checkout()) {
        return;
    }

    $cart_count = WC()->cart->get_cart_contents_count();
    ?>
    <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="floating-cart-btn" aria-label="<?php esc_attr_e('View cart', 'oceanwp-child'); ?>">
        <i class="fa fa-shopping-cart" aria-hidden="true"></i>
        <?php if ($cart_count > 0) : ?>
            <span class="cart-count"><?php echo esc_html($cart_count); ?></span>
        <?php endif; ?>
    </a>
    <?php
}
add_action('wp_footer', 'oceanwp_child_floating_cart');

/**
 * Update floating cart count via AJAX
 */
function oceanwp_child_cart_count_fragment($fragments) {
    $cart_count = WC()->cart->get_cart_contents_count();

    ob_start();
    ?>
    <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="floating-cart-btn" aria-label="<?php esc_attr_e('View cart', 'oceanwp-child'); ?>">
        <i class="fa fa-shopping-cart" aria-hidden="true"></i>
        <?php if ($cart_count > 0) : ?>
            <span class="cart-count"><?php echo esc_html($cart_count); ?></span>
        <?php endif; ?>
    </a>
    <?php
    $fragments['.floating-cart-btn'] = ob_get_clean();

    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'oceanwp_child_cart_count_fragment');

/**
 * Add checkout progress indicator
 */
function oceanwp_child_checkout_progress() {
    if (!is_checkout() && !is_cart()) {
        return;
    }

    $current_step = is_cart() ? 1 : 2;
    if (is_checkout() && is_wc_endpoint_url('order-received')) {
        $current_step = 3;
    }

    $steps = array(
        1 => __('Cart', 'oceanwp-child'),
        2 => __('Checkout', 'oceanwp-child'),
        3 => __('Complete', 'oceanwp-child')
    );
    ?>
    <div class="checkout-progress" role="navigation" aria-label="<?php esc_attr_e('Checkout progress', 'oceanwp-child'); ?>">
        <?php foreach ($steps as $step_num => $step_label) :
            $status = '';
            if ($step_num < $current_step) {
                $status = 'completed';
            } elseif ($step_num === $current_step) {
                $status = 'active';
            }
        ?>
            <div class="checkout-progress-step <?php echo esc_attr($status); ?>">
                <div class="checkout-progress-icon">
                    <?php if ($status === 'completed') : ?>
                        <i class="fa fa-check" aria-hidden="true"></i>
                    <?php else : ?>
                        <?php echo esc_html($step_num); ?>
                    <?php endif; ?>
                </div>
                <span class="checkout-progress-label"><?php echo esc_html($step_label); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}
add_action('woocommerce_before_cart', 'oceanwp_child_checkout_progress', 5);
add_action('woocommerce_before_checkout_form', 'oceanwp_child_checkout_progress', 5);
add_action('woocommerce_before_thankyou', 'oceanwp_child_checkout_progress', 5);

/**
 * Add trust badges after checkout button
 */
function oceanwp_child_checkout_trust_badges() {
    ?>
    <div class="checkout-trust-badges">
        <div class="trust-badge">
            <i class="fa fa-lock" aria-hidden="true"></i>
            <span><?php esc_html_e('Secure Checkout', 'oceanwp-child'); ?></span>
        </div>
        <div class="trust-badge">
            <i class="fa fa-shield-alt" aria-hidden="true"></i>
            <span><?php esc_html_e('SSL Encrypted', 'oceanwp-child'); ?></span>
        </div>
        <div class="trust-badge">
            <i class="fa fa-undo" aria-hidden="true"></i>
            <span><?php esc_html_e('Easy Returns', 'oceanwp-child'); ?></span>
        </div>
    </div>
    <?php
}
add_action('woocommerce_review_order_after_submit', 'oceanwp_child_checkout_trust_badges');

/**
 * Add body classes for enhanced styling
 */
function oceanwp_child_body_classes($classes) {
    $classes[] = 'shrink-header';
    $classes[] = 'madhu-theme';

    return $classes;
}
add_filter('body_class', 'oceanwp_child_body_classes');

/**
 * Ensure breadcrumbs are shown
 */
function oceanwp_child_enable_breadcrumbs($return) {
    return true;
}
add_filter('ocean_display_breadcrumbs', 'oceanwp_child_enable_breadcrumbs');

/**
 * Improve product image alt text if empty
 */
function oceanwp_child_product_image_alt($html, $post_thumbnail_id) {
    if (strpos($html, 'alt=""') !== false || strpos($html, "alt=''") !== false) {
        $product_title = get_the_title();
        $html = str_replace(array('alt=""', "alt=''"), 'alt="' . esc_attr($product_title) . '"', $html);
    }
    return $html;
}
add_filter('post_thumbnail_html', 'oceanwp_child_product_image_alt', 10, 2);

/**
 * Add schema.org markup for products
 */
function oceanwp_child_add_product_schema() {
    if (!is_product()) {
        return;
    }

    global $product;

    if (!$product) {
        return;
    }
    ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org/",
        "@type": "Product",
        "name": "<?php echo esc_js($product->get_name()); ?>",
        "description": "<?php echo esc_js(wp_strip_all_tags($product->get_short_description())); ?>",
        "image": "<?php echo esc_url(wp_get_attachment_url($product->get_image_id())); ?>",
        "offers": {
            "@type": "Offer",
            "price": "<?php echo esc_js($product->get_price()); ?>",
            "priceCurrency": "<?php echo esc_js(get_woocommerce_currency()); ?>",
            "availability": "<?php echo $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'; ?>"
        }
    }
    </script>
    <?php
}
add_action('wp_head', 'oceanwp_child_add_product_schema');

/**
 * Optimize font loading with preconnect
 */
function oceanwp_child_preconnect_fonts() {
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php
}
add_action('wp_head', 'oceanwp_child_preconnect_fonts', 1);

/**
 * Add main ID to content area if not present
 */
function oceanwp_child_add_main_id($content) {
    return $content;
}

/**
 * Remove emoji scripts for performance
 */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');

/**
 * Remove WordPress embed script
 */
function oceanwp_child_dequeue_embed_script() {
    wp_dequeue_script('wp-embed');
}
add_action('wp_footer', 'oceanwp_child_dequeue_embed_script');

/**
 * Disable XML-RPC
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Limit post revisions
 */
if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 5);
}
