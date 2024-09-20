<?php
/**
 * Plugin Name: scouting
 * Plugin URI: https://wordpress.org/plugins/scouting/
 * Description: The leading documents management plugin for scouting system by shortcode
 * Author: dgc.network
 * Author URI: https://dgc.network/
 * Version: 0.0.1
 * Requires at least: 6.0
 * Tested up to: 6.5.3
 *
 * Text Domain: scouting
 * Domain Path: /languages/
 */

if (!defined('ABSPATH')) {
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function register_session() {
    if ( ! session_id() ) {
        session_start();
    }
}
add_action( 'init', 'register_session' );

function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}
//add_action('after_setup_theme', 'remove_admin_bar');

function allow_subscribers_to_view_users($allcaps, $caps, $args) {
    // Check if the user is trying to view other users
    if (isset($args[0]) && $args[0] === 'list_users') {
        // Check if the user has the "subscriber" role
        $user = wp_get_current_user();
        if (in_array('subscriber', $user->roles)) {
            // Allow subscribers to view users
            $allcaps['list_users'] = true;
        }
    }
    return $allcaps;
}
//add_filter('user_has_cap', 'allow_subscribers_to_view_users', 10, 3);

function get_post_type_meta_keys($post_type) {
    global $wpdb;
    $query = $wpdb->prepare("
        SELECT DISTINCT(meta_key)
        FROM $wpdb->postmeta
        INNER JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id
        WHERE $wpdb->posts.post_type = %s
    ", $post_type);
    return $wpdb->get_col($query);
}

function isURL($str) {
    $pattern = '/^(http|https):\/\/[^ "]+$/';
    return preg_match($pattern, $str) === 1;
}

//require_once plugin_dir_path( __FILE__ ) . 'erp/erp-cards.php';
//require_once plugin_dir_path( __FILE__ ) . 'erp/subforms.php';
require_once plugin_dir_path( __FILE__ ) . 'services/services.php';
require_once plugin_dir_path( __FILE__ ) . 'services/mqtt-client.php';
//require_once plugin_dir_path( __FILE__ ) . 'includes/display-login.php';

setcookie('custom_test_cookie', wp_date(get_option('time_format'), time()), time() + 3600, '/', '', is_ssl(), true);

// Line login callback
function handle_line_callback() {
    if (isset($_GET['code']) && isset($_GET['state'])) {
        // Sanitize inputs
        $code = sanitize_text_field($_GET['code']);
        $state = sanitize_text_field($_GET['state']);

        // Validate the state parameter (important to prevent CSRF)
        $stored_state = get_transient('line_login_state');
        if ($state !== $stored_state) {
            wp_die('Invalid state parameter. Possible CSRF attack.');
        }
        
        // Remove the transient as it's no longer needed
        delete_transient('line_login_state');

        // Exchange authorization code for access token
        $response = wp_remote_post('https://api.line.me/oauth2/v2.1/token', array(
            'body' => array(
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => get_option('line_login_redirect_uri'),
                'client_id'     => get_option('line_login_client_id'),
                'client_secret' => get_option('line_login_client_secret'),
            ),
        ));

        if (is_wp_error($response)) {
            wp_die('Failed to contact LINE API: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);

        if (isset($json['access_token'])) {
            $access_token = $json['access_token'];

            // Use the access token to get the user's profile
            $profile_response = wp_remote_get('https://api.line.me/v2/profile', array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $access_token,
                ),
            ));

            if (is_wp_error($profile_response)) {
                wp_die('Failed to retrieve LINE profile: ' . $profile_response->get_error_message());
            }

            $profile = json_decode(wp_remote_retrieve_body($profile_response), true);

            if (isset($profile['userId'])) {
                // You now have the user's LINE ID
                $line_user_id = $profile['userId'];
                $line_display_name = isset($profile['displayName']) ? $profile['displayName'] : '';
                //wp_die('Display LINE profile: '.$line_display_name);
/*        
                // Check if the user is already logged in
                if (is_user_logged_in()) {
                    echo 'You are already logged in.';
                    exit;
                }
*/        
                // Check if the LINE user is already registered
                $user_query = new WP_User_Query(array(
                    'meta_key'   => 'line_user_id',
                    'meta_value' => $line_user_id,
                ));
                
                $users = $user_query->get_results();
                $user = !empty($users) ? $users[0] : null;                        
                                        
                //wp_die('Display user profile: '.$user->display_name);

                // Check if user exists, log them in
                if ($user && $user instanceof WP_User) {
                    // Check if headers have already been sent
                    if (headers_sent()) {
                        wp_die('Headers already sent. Cannot set cookie.');
                    } else {
                        wp_set_current_user($user->ID);
                        wp_set_auth_cookie($user->ID, true, is_ssl());
                        do_action('wp_login', $user->user_login);

                        setcookie('custom_test_cookie', wp_date(get_option('time_format'), time()), time() + 3600, '/', '', is_ssl(), true);

                        wp_redirect(home_url());
                        exit;
                    }
/*                            
                }
                
                if ($user && $user instanceof WP_User) {
                    $stored_pass = get_user_meta($user->ID, 'stored_pass', true);
                    //$stored_pass = get_user_meta($line_user_id, 'stored_pass', true);
                    $creds = array(
                        //'user_login'    => $user->user_login,
                        'user_login'    => $line_user_id,
                        'user_password' => $stored_pass,
                        'remember'      => true,
                    );
                    $user_signon = wp_signon($creds, false);
                
                    if (is_wp_error($user_signon)) {
                        wp_die('Login failed: ' . $user_signon->get_error_message());
                    } else {
                        //wp_die('Display user_signon profile: '.$user_signon->display_name);
                        error_log('Authentication cookies set for user ID: ' . $user_signon->ID);
                        wp_redirect(home_url());
                        exit;
                        //wp_safe_redirect(home_url());
                        //exit;

                    }
*/
                } else {
                    // Register a new user
                    $random_password = wp_generate_password();
                    $user_data = array(
                        //'user_login' => $line_display_name,
                        'user_login' => $line_user_id,
                        'user_pass'  => $random_password,
                        'nickname'   => $line_display_name,
                        'display_name' => $line_display_name,
                    );
                    $user_id = wp_insert_user($user_data);
                
                    if (!is_wp_error($user_id)) {
                        update_user_meta($user_id, 'line_user_id', $line_user_id);
                        update_user_meta($user_id, 'stored_pass', $random_password);
                
                        // Log in the newly registered user
                        $creds = array(
                            //'user_login'    => $line_display_name,
                            'user_login'    => $line_user_id,
                            'user_password' => $random_password,
                            'remember'      => true,
                        );
                        $user_signon = wp_signon($creds, false);
                
                        if (is_wp_error($user_signon)) {
                            wp_die('Login failed: ' . $user_signon->get_error_message());
                        } else {
                            wp_redirect(home_url());
                            exit;
                        }
                    } else {
                        wp_die('User registration failed: ' . $user_id->get_error_message());
                    }
                }
            } else {
                wp_die('Failed to retrieve LINE user profile.');
            }
        } else {
            wp_die('Failed to get access token from LINE.');
        }
    } else {
        //wp_die('Authorization code or state parameter is missing.');
    }
}
add_action('plugins_loaded', 'handle_line_callback'); // Hooking after plugins are loaded
//add_action('template_redirect', 'handle_line_callback');
//add_action('init', array( $this, 'handle_line_callback'));
