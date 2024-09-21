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
                // Now you have the LINE user's ID and other info
                $line_user_id = $profile['userId'];
                $line_display_name = isset($profile['displayName']) ? $profile['displayName'] : '';

                // Now, perform user authentication (or registration if user doesn't exist)
                $user_query = new WP_User_Query(array(
                    'meta_key'   => 'line_user_id',
                    'meta_value' => $line_user_id,
                ));

                $users = $user_query->get_results();
                $user = !empty($users) ? $users[0] : null;

                if ($user && $user instanceof WP_User) {
                    // Set the user in the session manually via custom cookie
                    $user_id = $user->ID;
                    $expiration = time() + (3600 * 24);  // 1 day expiration

                    // Set a custom authentication cookie manually
                    $auth_cookie_value = base64_encode($user->user_login . '|' . $expiration . '|' . wp_hash_password($user->user_pass));
                    //wp_die('Set a custom authentication cookie: ' . $auth_cookie_value);

                    // Manually setting the custom cookie
                    header('Set-Cookie: custom_auth_cookie=' . $auth_cookie_value . '; Path=/; HttpOnly; Secure=' . (is_ssl() ? 'true' : 'false') . '; SameSite=Strict; Expires=' . gmdate('D, d-M-Y H:i:s T', $expiration));

                    // Redirect the user after setting the cookie
                    wp_redirect(home_url());
                    exit;
                } else {
                    // User does not exist, handle registration
                    $random_password = wp_generate_password();
                    $user_data = array(
                        'user_login' => $line_user_id,
                        'user_pass'  => $random_password,
                        'nickname'   => $line_display_name,
                        'display_name' => $line_display_name,
                    );
                    $user_id = wp_insert_user($user_data);

                    if (!is_wp_error($user_id)) {
                        update_user_meta($user_id, 'line_user_id', $line_user_id);
                        update_user_meta($user_id, 'stored_pass', $random_password);

                        // After user registration, set the cookie for the new user
                        $auth_cookie_value = base64_encode($line_user_id . '|' . $expiration . '|' . wp_hash_password($random_password));

                        header('Set-Cookie: custom_auth_cookie=' . $auth_cookie_value . '; Path=/; HttpOnly; Secure=' . (is_ssl() ? 'true' : 'false') . '; SameSite=Strict; Expires=' . gmdate('D, d-M-Y H:i:s T', $expiration));

                        // Redirect the user after setting the cookie
                        wp_redirect(home_url());
                        exit;
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
    }
}
add_action('init', 'handle_line_callback', 1);

add_action('init', function () {
    if (isset($_COOKIE['custom_auth_cookie'])) {
        $cookie_value = $_COOKIE['custom_auth_cookie'];
        wp_die('Cookie value: '.$cookie_value);
        list($user_login, $expiration, $hash) = explode('|', base64_decode($cookie_value));

        // Validate expiration
        if (time() < $expiration) {
            // Fetch the user by their login name
            $user = get_user_by('login', $user_login);
            
            if ($user && wp_check_password($user->user_pass, $hash)) {
                // If user exists and the password is valid, set current user
                wp_set_current_user($user->ID);
            }
        }
    }
});

