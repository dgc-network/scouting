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

        function display_login_button() {
            $state = bin2hex(random_bytes(16)); // Generate a random string
            set_transient('line_login_state', $state, 3600); // Save it for 1 hour
            $line_auth_url = "https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=" . urlencode(get_option('line_login_client_id')) .
                 "&redirect_uri=" . urlencode(get_option('line_login_redirect_uri')) .
                 "&state=" . urlencode($state) .
                 "&scope=profile";

            ob_start();
            ?>
            <a href="<?php echo $line_auth_url;?>">    
                <img src="https://s3.ap-southeast-1.amazonaws.com/app-assets.easystore.co/apps/154/icon.png" alt="LINE Login">
            </a>
            <?php
            return ob_get_clean();
        }

    }
    $line_login_api = new line_login_api();
}
