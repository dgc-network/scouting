<?php
/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!class_exists('line_login_api')) {
    class line_login_api {
        private $channel_id;
        private $channel_access_token;
        //public $channel_access_token;

        public function __construct() {
            $this->channel_id = get_option('line_login_channel_id');
            $this->channel_access_token = get_option('line_login_token_option');
            add_action( 'admin_init', array( $this, 'line_login_register_settings' ) );
            add_shortcode( 'display-login', array( $this, 'display_shortcode'  ) );
            add_action('template_redirect', array( $this, 'handle_line_callback'));
        }

        function line_login_register_settings() {
            // Register Line login section
            add_settings_section(
                'line-login-section-settings',
                'Line login Settings',
                array( $this, 'line_login_section_settings_callback' ),
                'web-service-settings'
            );

            // Register fields for Line login section
            add_settings_field(
                'line_login_redirect_uri',
                'Line login redirect uri',
                array( $this, 'line_login_redirect_uri_callback' ),
                'web-service-settings',
                'line-login-section-settings'
            );
            register_setting('web-service-settings', 'line_login_redirect_uri');

            add_settings_field(
                'line_login_client_id',
                'Line login client id',
                array( $this, 'line_login_client_id_callback' ),
                'web-service-settings',
                'line-login-section-settings'
            );
            register_setting('web-service-settings', 'line_login_client_id');

            add_settings_field(
                'line_login_client_secret',
                'Line login client secret',
                array( $this, 'line_login_client_secret_callback' ),
                'web-service-settings',
                'line-login-section-settings'
            );
            register_setting('web-service-settings', 'line_login_client_secret');
        }

        function line_login_section_settings_callback() {
            echo '<p>Settings for Line login.</p>';
        }

        function line_login_redirect_uri_callback() {
            $value = get_option('line_login_redirect_uri');
            echo '<input type="text" name="line_login_redirect_uri" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        function line_login_client_id_callback() {
            $value = get_option('line_login_client_id');
            echo '<input type="text" name="line_login_client_id" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        function line_login_client_secret_callback() {
            $value = get_option('line_login_client_secret');
            echo '<input type="text" name="line_login_client_secret" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        // login callback
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
        
                        // Check if the user is already logged in
                        if (is_user_logged_in()) {
                            echo 'You are already logged in.';
                            exit;
                        }
        
                        // Check if the LINE user is already registered
                        $user = get_user_by('meta_value', $line_user_id);
                        
                        if ($user) {
                            // User exists, log them in
                            wp_set_auth_cookie($user->ID);
                            wp_redirect(home_url());
                            exit;
                        } else {
                            // Register a new user with the LINE ID
                            $user_data = array(
                                'user_login' => $line_display_name,
                                'user_pass'  => wp_generate_password(),
                                'nickname'   => $line_display_name,
                            );
                            $user_id = wp_insert_user($user_data);
        
                            if (!is_wp_error($user_id)) {
                                // Save LINE user ID to user meta
                                update_user_meta($user_id, 'line_user_id', $line_user_id);
        
                                // Log the user in
                                wp_set_auth_cookie($user_id);
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
            } else {
                wp_die('Authorization code or state parameter is missing.');
            }
        }
/*        
        function handle_line_callback() {
            if (isset($_GET['code'])) {
                $code = sanitize_text_field($_GET['code']);
                $state = sanitize_text_field($_GET['state']);
        
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
        
                    $profile = json_decode(wp_remote_retrieve_body($profile_response), true);
        
                    if (isset($profile['userId'])) {
                        // You now have the user's LINE ID
                        $line_user_id = $profile['userId'];
        
                        // Check if the user is already logged in (using is_user_logged_in())
                        if (is_user_logged_in()) {
                            // User is already logged in, handle accordingly
                            // (e.g. display a message or redirect to a specific page)
                            echo 'You are already logged in.';
                            exit;
                        } else {
                            // User is not logged in, proceed with LINE user registration/login
                            // ... (rest of your existing code for user registration and login)
                        }
                    }
                }
            }
        }
/*        
        function handle_line_callback() {
            if (isset($_GET['code'])) {
                $code = sanitize_text_field($_GET['code']);
                $state = sanitize_text_field($_GET['state']);
        
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
        
                    $profile = json_decode(wp_remote_retrieve_body($profile_response), true);
        
                    if (isset($profile['userId'])) {
                        // You now have the user's LINE ID and can use it to log them in
                        $line_user_id = $profile['userId'];
        
                        // Now you need to check if this LINE user is already registered in your WordPress system
                        $user = get_user_by('meta_value', $line_user_id, 'line_user_id');
        
                        if ($user) {
                            // If the user exists, log them in
                            wp_set_auth_cookie($user->ID);
                            wp_redirect(home_url());
                            exit;
                        } else {
                            // If the user doesn't exist, you can create a new WordPress account for them
                            $new_user_id = wp_insert_user(array(
                                'user_login' => $profile['displayName'],
                                'user_pass'  => wp_generate_password(),
                            ));
        
                            // Save LINE user ID to user meta for future logins
                            update_user_meta($new_user_id, 'line_user_id', $line_user_id);
        
                            // Log the new user in
                            wp_set_auth_cookie($new_user_id);
                            wp_redirect(home_url());
                            exit;
                        }
                    }
                }
            }
        }
*/
        function display_login_button() {
            ob_start();
            ?>
            <a href="https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=<?php echo get_option('line_login_client_id');?>&redirect_uri=<?php echo get_option('line_login_redirect_uri');?>&state=YOUR_STATE&scope=profile%20openid%20email">
                <img src="https://s3.ap-southeast-1.amazonaws.com/app-assets.easystore.co/apps/154/icon.png" alt="LINE Login">
            </a>
            <?php
            return ob_get_clean();
        }

    }
    $line_login_api = new line_login_api();
}
