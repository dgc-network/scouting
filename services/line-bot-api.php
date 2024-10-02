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
                'line_bot_token_option',
                'Line bot Token',
                array( $this, 'line_bot_token_option_callback' ),
                'web-service-settings',
                'line-bot-section-settings'
            );
            register_setting('web-service-settings', 'line_bot_token_option');

            add_settings_field(
                'line_official_account',
                'Line official account',
                array( $this, 'line_official_account_callback' ),
                'web-service-settings',
                'line-bot-section-settings'
            );
            register_setting('web-service-settings', 'line_official_account');

            add_settings_field(
                'line_official_qr_code',
                'Line official qr-code',
                array( $this, 'line_official_qr_code_callback' ),
                'web-service-settings',
                'line-bot-section-settings'
            );
            register_setting('web-service-settings', 'line_official_qr_code');
        }

        function line_bot_section_settings_callback() {
            echo '<p>Settings for Line bot.</p>';
        }

        function line_bot_token_option_callback() {
            $value = get_option('line_bot_token_option');
            echo '<input type="text" name="line_bot_token_option" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        function line_official_account_callback() {
            $value = get_option('line_official_account');
            echo '<input type="text" name="line_official_account" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        function line_official_qr_code_callback() {
            $value = get_option('line_official_qr_code');
            echo '<input type="text" name="line_official_qr_code" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        // Flex message
        function set_bubble_message($params) {
            $display_name = $params['display_name'];
            $link_uri = $params['link_uri'];
            $text_message = $params['text_message'];
        
            $header_contents = $params['header_contents'];
            $body_contents = $params['body_contents'];
            $footer_contents = $params['footer_contents'];
        /*
            // Header contents can be modified as needed or left empty if not used
            if (empty($header_contents)) {
                $header_contents = array(
                    array(
                        'type' => 'text',
                        'text' => 'Hello, ' . $display_name,
                        'size' => 'lg',
                        'weight' => 'bold',
                    ),
                );
            }
        
            // Body contents with text and message details
            if (empty($body_contents)) {
                $body_contents = array(
                    array(
                        'type' => 'text',
                        'text' => $text_message,
                        'wrap' => true,
                    ),
                );
            }
        
            // Footer contents with a button
            if (empty($footer_contents)) {
                $footer_contents = array(
                    array(
                        'type' => 'button',
                        'action' => array(
                            'type' => 'uri',
                            'label' => 'Click me!',
                            'uri' => $link_uri, // Use the desired URI
                        ),
                        'style' => 'primary',
                        'margin' => 'sm',
                    ),
                );
            }
        */
            // Initial bubble message structure
            $bubble_message = array(
                'type' => 'flex',
                'altText' => $text_message,
                'contents' => array(
                    'type' => 'bubble',
                ),
            );
        
            // Add header contents if not empty
            if (is_array($header_contents) && !empty($header_contents)) {
                $bubble_message['contents']['header'] = array(
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => $header_contents,
                );
            }
        
            // Add body contents if not empty
            if (is_array($body_contents) && !empty($body_contents)) {
                $bubble_message['contents']['body'] = array(
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => $body_contents,
                );
            }
        
            // Add footer contents if not empty
            if (is_array($footer_contents) && !empty($footer_contents)) {
                $bubble_message['contents']['footer'] = array(
                    'type' => 'box',
                    'layout' => 'vertical',
                    'contents' => $footer_contents,
                );
            }
        
            return $bubble_message;
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
                    'content' => json_encode($message),
                ],
            ]);
            $response = file_get_contents('https://api.line.me/v2/bot/message/reply', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
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
