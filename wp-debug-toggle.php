<?php
/*
 * Plugin Name: WP_DEBUG Toggle
 * Plugin URI: https://plugins.club/wordpress/wp_debug-toggle/
 * Description: 🔴Enable / 🟢Disable WP_DEBUG from admin and update wp-config.php file.
 * Version: 1.1
 * Author: plugins.club
 * Author URI: https://plugins.club/
 */

// Include plugin helper
include 'include/PluginHelperClass.php';

// Register and enqueue the plugin's stylesheet
function wp_debug_toggle_enqueue_styles() {
    wp_register_style( 'wp-debug-toggle', plugin_dir_url( __FILE__ ) . '/include/css/wp-debug-toggle.css' );
    wp_enqueue_style( 'wp-debug-toggle' );
}
add_action( 'admin_enqueue_scripts', 'wp_debug_toggle_enqueue_styles' );
add_action( 'wp_enqueue_scripts', 'wp_debug_toggle_enqueue_styles' );


// Add the WP_DEBUG toggle button to the WP-Admin bar
function add_wp_debug_toggle_to_admin_bar() {
    // Get the current status of WP_DEBUG
    $wp_debug = ( defined( 'WP_DEBUG' ) && WP_DEBUG === true );

    // Set the label and CSS class for the button based on the current status
    if ( $wp_debug ) {
        $label = 'WP_DEBUG ON';
        $class = 'wp-debug-on';
    } else {
        $label = 'WP_DEBUG OFF';
        $class = 'wp-debug-off';
    }

    global $wp_admin_bar;
    $wp_admin_bar->add_node( array(
        'id'    => 'wp-debug-toggle',
        'title' => $label,
        'href'  => '#',
        'meta'  => array( 'class' => $class ),
        'parent' => 'top-secondary'
    ) );
}

add_action( 'admin_bar_menu', 'add_wp_debug_toggle_to_admin_bar', 100 );

// Handle the toggle button click
function handle_wp_debug_toggle() {
    // Check if the toggle button was clicked
    if ( isset( $_GET['wp-debug-toggle'] ) ) {
        // Get the path to the wp-config.php file
        $config_path = ABSPATH . 'wp-config.php';
        // Check if the wp-config.php file exist in the default location
        if (!file_exists($config_path)) {
            // If not, check if the wp-config file exist in the parent folder of the wp-config.php file as per https://wordpress.org/support/article/hardening-wordpress/#securing-wp-config-php
            $config_path = dirname(ABSPATH) . '/wp-config.php';
            if (!file_exists($config_path)) {
                // If the wp-config.php file is not found in either of the above locations,
                // return an error message
                echo 'wp-config.php file could not be found. This plugin will not work if you are using a custom name for the wp-config.php file.';
                return;
            }
        }
        // Read the contents of the wp-config.php file
        $config_contents = file_get_contents( $config_path );

        // Get the current status of WP_DEBUG
        $wp_debug = ( defined( 'WP_DEBUG' ) && WP_DEBUG === true );

        // Update the value of WP_DEBUG in the wp-config.php file
        if ( $wp_debug ) {
            $config_contents = preg_replace( '/define\s*\(\s*\'WP_DEBUG\'\s*,\s*true\s*\)\s*;/', "define('WP_DEBUG', false);", $config_contents );
        } else {
            $config_contents = preg_replace( '/define\s*\(\s*\'WP_DEBUG\'\s*,\s*false\s*\)\s*;/', "define('WP_DEBUG', true);", $config_contents );
        }

        // Write the updated contents back to the wp-config.php file
        file_put_contents( $config_path, $config_contents );

        // Redirect the user back to the current page
        wp_redirect( remove_query_arg( 'wp-debug-toggle' ) );
        exit;
    }
}
add_action( 'init', 'handle_wp_debug_toggle' );

// Add the JavaScript to handle the toggle button click
function add_wp_debug_toggle_script() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('#wp-admin-bar-wp-debug-toggle a').click(function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            if ( href !== '#' ) {
                window.location = href;
            } else {
                window.location = '<?php echo add_query_arg( 'wp-debug-toggle', 1 ); ?>';
            }
        });
    });
    </script>
    <?php
}
add_action( 'admin_footer', 'add_wp_debug_toggle_script' );
add_action( 'wp_footer', 'add_wp_debug_toggle_script' );
