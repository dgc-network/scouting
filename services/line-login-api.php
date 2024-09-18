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
        
        function display_shortcode() {


?>
<a href="https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=YOUR_CHANNEL_ID&redirect_uri=YOUR_CALLBACK_URL&state=YOUR_STATE&scope=profile%20openid%20email">
    <img src="https://d.line-scdn.net/liff/1.0/sdk/img/log-in-button.png" alt="LINE Login">
</a>
<?php


        }

    }
    $line_login_api = new line_login_api();
}
