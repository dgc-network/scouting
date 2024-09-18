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

if (!class_exists('line_bot_api')) {
    class line_bot_api {
        private $channel_id;
        private $channel_access_token;
        //public $channel_access_token;

        public function __construct() {
            $this->channel_id = get_option('line_bot_channel_id');
            $this->channel_access_token = get_option('line_bot_token_option');
            add_action( 'admin_init', array( $this, 'line_bot_register_settings' ) );
            //add_action( 'init', array( $this, 'handle_line_callback' ) );
            //add_action( 'wp', array( $this, 'check_otp_form' ) );
            //add_action('template_redirect', array( $this, 'line_user_login'));
            //add_action('wp_footer', array( $this, 'check_login_status'));                
        }

        function line_bot_register_settings() {
            // Register Line bot section
            add_settings_section(
                'line-bot-section-settings',
                'Line bot Settings',
                array( $this, 'line_bot_section_settings_callback' ),
                'web-service-settings'
            );

            // Register fields for Line bot section
            add_settings_field(
                'line-bot-token-option',
                'Line bot Token',
                array( $this, 'line_bot_token_option_callback' ),
                'web-service-settings',
                'line-bot-section-settings'
            );
            register_setting('web-service-settings', 'line-bot-token-option');

            add_settings_field(
                'line-official-account',
                'Line official account',
                array( $this, 'line_official_account_callback' ),
                'web-service-settings',
                'line-bot-section-settings'
            );
            register_setting('web-service-settings', 'line-official-account');

            add_settings_field(
                'line-official-qr-code',
                'Line official qr-code',
                array( $this, 'line_official_qr_code_callback' ),
                'web-service-settings',
                'line-bot-section-settings'
            );
            register_setting('web-service-settings', 'line-official-qr-code');
        }

        function line_bot_section_settings_callback() {
            echo '<p>Settings for Line bot.</p>';
        }

        function line_bot_token_option_callback() {
            $value = get_option('line_bot_token_option');
            echo '<input type="text" id="line-bot-token-option" name="line_bot_token_option" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        function line_official_account_callback() {
            $value = get_option('line_official_account');
            echo '<input type="text" id="line-official-account" name="line_official_account" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        function line_official_qr_code_callback() {
            $value = get_option('line_official_qr_code');
            echo '<input type="text" id="line-official-qr-code" name="line_official_qr_code" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        // login callback
        function line_user_login() {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
            
            session_start();
        
            // Set a cooldown period (e.g., 10 seconds)
            $cooldown_period = 10;
        
            // Get the last execution time from the session
            $last_execution = isset($_SESSION['last_line_user_login']) ? $_SESSION['last_line_user_login'] : 0;
            $current_time = time();
        
            // Check if the cooldown period has passed
            if (($current_time - $last_execution) < $cooldown_period) {
                error_log('Throttled: too many requests');
                return; // Exit if within cooldown period
            }
        
            // Update the last execution time
            $_SESSION['last_line_user_login'] = $current_time;
        
            // Start output buffering to avoid premature output
            ob_start();
        
            // Example Line user ID (replace with actual logic to get the Line user ID)
            $line_user_id = 'U1b08294900a36077765643d8ae14a402';
        
            // Check if the user is already logged in
            if (is_user_logged_in()) {
                error_log('User is already logged in');
                ob_end_flush();
                return;
            }
        
            // Attempt to find the user by their login
            $user = get_user_by('login', $line_user_id);
        
            if (!$user) {
                // User does not exist, create a new user
                $user_id = wp_create_user($line_user_id, wp_generate_password(), $line_user_id);
                if (is_wp_error($user_id)) {
                    // Handle user creation error
                    error_log('Failed to create user: ' . $user_id->get_error_message());
                    ob_end_flush();
                    return;
                }
                $user = get_user_by('id', $user_id);
                error_log('Created new user with ID: ' . $user_id);
            } else {
                error_log('User exists with ID: ' . $user->ID);
            }
        
            if ($user) {
                // Log the user in
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID);
                do_action('wp_login', $user->user_login);
                error_log('User logged in with ID: ' . $user->ID);
        
                // Flush the output buffer and set cookies
                ob_end_flush();
        
                // Redirect to avoid direct output and refresh session
                wp_redirect(home_url());
                exit;
            } else {
                error_log('User authentication failed');
                ob_end_flush();
                return;
            }
        }

        function check_login_status() {
            if (is_user_logged_in()) {
                echo '<h1>Hi, ' . wp_get_current_user()->display_name . '</h1>';
            } else {
                echo '<h1>Login failed or user not logged in</h1>';
            }
        }
        
        function handle_line_callback() {
            if (isset($_GET['code']) && isset($_GET['state'])) {
/*            
                $code = $_GET['code'];
                // Exchange code for access token
                $token_response = wp_remote_post('https://api.line.me/oauth2/v2.1/token', array(
                    'body' => array(
                        'grant_type' => 'authorization_code',
                        'code' => $code,
                        'redirect_uri' => 'YOUR_CALLBACK_URL',
                        'client_id' => 'YOUR_CHANNEL_ID',
                        'client_secret' => 'YOUR_CHANNEL_SECRET'
                    )
                ));
                $token_data = json_decode(wp_remote_retrieve_body($token_response), true);
                $access_token = $token_data['access_token'];
*/        
                // Get user profile
                $profile_response = wp_remote_get('https://api.line.me/v2/profile', array(
                    'headers' => array(
                        //'Authorization' => 'Bearer ' . $access_token
                        'Authorization' => 'Bearer ' . $this->channel_access_token,
                    )
                ));
                $profile_data = json_decode(wp_remote_retrieve_body($profile_response), true);
                $line_user_id = $profile_data['userId'];

                // Check if user exists, if not, create a new user
                $user = get_user_by('login', $line_user_id);
                if (!$user) {
                    //$user_id = wp_create_user($line_user_id, wp_generate_password(), $line_user_id . '@example.com');
                    $user_id = wp_create_user($line_user_id, wp_generate_password(), $line_user_id);
                    $user = get_user_by('id', $user_id);
                }
        
                // Log the user in
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID);
                wp_redirect(home_url());
                exit;
            }
        }
        
        function generate_otp() {
            return rand(100000, 999999); // Simple 6-digit OTP
        }

        function send_otp_to_line($line_user_id, $otp) {
            //$access_token = 'YOUR_LINE_CHANNEL_ACCESS_TOKEN';
            $message = array(
                'to' => $line_user_id,
                'messages' => array(
                    array(
                        'type' => 'text',
                        'text' => 'Your OTP is: ' . $otp
                    )
                )
            );
        
            wp_remote_post('https://api.line.me/v2/bot/message/push', array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    //'Authorization' => 'Bearer ' . $access_token
                    'Authorization' => 'Bearer ' . $this->channel_access_token,
                ),
                'body' => json_encode($message)
            ));
        }
        
        function check_otp_form() {
            if (isset($_POST['otp'])) {
                $entered_otp = sanitize_text_field($_POST['otp']);
                $stored_otp = get_user_meta(get_current_user_id(), 'otp', true);
        
                if ($entered_otp === $stored_otp) {
                    // OTP verified
                    delete_user_meta(get_current_user_id(), 'otp'); // Remove OTP after verification
                    // Redirect to the desired page
                    wp_redirect(home_url());
                    exit;
                } else {
                    // OTP verification failed
                    echo 'Invalid OTP.';
                }
            }
        }
        
        // line-bot-api
        public function broadcastMessage($message) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($message),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/message/broadcast', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param array<string, mixed> $message
         * @return void
         */
        public function replyMessage($message) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    //'content' => json_encode($message, JSON_UNESCAPED_UNICODE),
                    'content' => json_encode($message),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/message/reply', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param array<string, mixed> $message
         * @return void
         */
        public function pushMessage($message) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($message),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/message/push', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param array<string, mixed> $content
         * @return void
         */
        public function createRichMenu($content) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($content),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/richmenu', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param string $richMenuId
         * @return void
         */
        public function uploadImageToRichMenu($richMenuId, $imagePath, $content) {
    
            $header = array(
                'Content-Type: image/png',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    //'content' => json_encode($content),
                    'content' => $imagePath,
                ],
            ]);
    
            $response = file_get_contents('https://api-data.line.me/v2/bot/richmenu/'.$richMenuId.'/content', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param array<string, mixed> $content
         * $content['richMenuId']
         * $content['userIds']
         * @return void
         */
        public function setDefaultRichMenu($content) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($content),
                ],
            ]);
    
            if (is_null($content['userIds'])) {
                $response = file_get_contents('https://api.line.me/v2/bot/user/all/richmenu/'.$content['richMenuId'], false, $context);
            } else {
                $response = file_get_contents('https://api.line.me/v2/bot/richmenu/bulk/link', false, $context);
            }
            
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param string $userId
         * @return object
         */
        public function getProfile($userId) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'GET',
                    'header' => implode("\r\n", $header),
                    //'content' => json_encode($userId),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/profile/'.$userId, false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
    
            $response = stripslashes($response);
            $response = json_decode($response, true);
            
            return $response;
        }
    
        /**
         * @param string $groupId
         * @return object
         */
        public function getGroupSummary($groupId) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'GET',
                    'header' => implode("\r\n", $header),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/group/'.$groupId.'/summary', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
    
            $response = stripslashes($response);
            $response = json_decode($response, true);
            
            return $response;
        }
    
        /**
         * @param string $groupId, $userId
         * @return object
         */
        public function getGroupMemberProfile($groupId, $userId) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'GET',
                    'header' => implode("\r\n", $header),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/group/'.$groupId.'/member'.'/'.$userId, false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
    
            $response = stripslashes($response);
            $response = json_decode($response, true);
            
            return $response;
        }
    
        /**
         * @param string $messageId
         * @return object
         */
        public function getContent($messageId) {
    
            $header = array(
                //'Content-Type: application/octet-stream',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'GET',
                    'header' => implode("\r\n", $header),
                ],
            ]);
            $response = file_get_contents('https://api-data.line.me/v2/bot/message/'.$messageId.'/content', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
            return $this->save_temp_image($response);
            //return var_dump($response);
            //return $response;
    
        }
    
        /**
         * Save the submitted image as a temporary file.
         *
         * @todo Revisit file handling.
         *
         * @param string $img Base64 encoded image.
         * @return false|string File name on success, false on failure.
         */
        protected function save_temp_image($img) {
    
            // Strip the "data:image/png;base64," part and decode the image.
            $img = explode(',', $img);
            $img = isset($img[1]) ? base64_decode($img[1]) : base64_decode($img[0]);
            if (!$img) {
                return false;
            }
            // Upload to tmp folder.
            $filename = 'user-feedback-' . date('Y-m-d-H-i-s');
            $tempfile = wp_tempnam($filename, sys_get_temp_dir());
            if (!$tempfile) {
                return false;
            }
            // WordPress adds a .tmp file extension, but we want .png.
            if (rename($tempfile, $filename . '.png')) {
                $tempfile = $filename . '.png';
            }
            if (!WP_Filesystem(request_filesystem_credentials(''))) {
                return false;
            }
            /**
             * WordPress Filesystem API.
             *
             * @var \WP_Filesystem_Base $wp_filesystem
             */
            global $wp_filesystem;
            //$wp_filesystem->chdir(get_temp_dir());
            $success = $wp_filesystem->put_contents($tempfile, $img);
            if (!$success) {
                return false;
            }
            //return $tempfile;
            $upload = wp_get_upload_dir();
            $url = '<img src="'.$upload['url'].'/'.$filename. '.png">';
            $url = '<img src="'.sys_get_temp_dir().$filename. '.png">';
            //$url = $wp_filesystem->wp_content_dir().'/'.$filename;
            return $url;
        }
      
        /**
         * @param string $body
         * @return string
         */
        private function sign($body) {
    
            $hash = hash_hmac('sha256', $body, $this->channelSecret, true);
            $signature = base64_encode($hash);
            return $signature;
        }
    }
    $line_bot_api = new line_bot_api();
}
