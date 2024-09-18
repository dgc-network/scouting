<?php
/**
 * Copyright 2022 dgc.network
 *
 * dgc.network licenses this file to you under the Apache License,
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

if (!class_exists('open_ai_api')) {
    class open_ai_api {

        private $openai_api_key;
    
        public function __construct($openai_api_key='') {
            add_action('admin_init', array( $this, 'open_ai_register_settings' ) );
            $this->openai_api_key = get_option('open_ai_api_key');
        }
    
        function open_ai_register_settings() {
            // Register Open AI section
            add_settings_section(
                'open-ai-section-settings',
                'Open AI Settings',
                array( $this, 'open_ai_section_settings_callback' ),
                'web-service-settings'
            );
        
            // Register fields for Open AI section
            add_settings_field(
                'open-ai-api-key',
                'API KEY',
                array( $this, 'open_ai_api_key_callback' ),
                'web-service-settings',
                'open-ai-section-settings'
            );
            register_setting('web-service-settings', 'open-ai-api-key');
        }
        
        function open_ai_section_settings_callback() {
            echo '<p>Settings for Open AI.</p>';
        }
        
        function open_ai_api_key_callback() {
            $value = get_option('open_ai_api_key');
            echo '<input type="text" id="open_ai_api_key" name="open_ai_api_key" style="width:100%;" value="' . esc_attr($value) . '" />';
        }
                
        public function createCompletion($param) {
    
            $param["model"]="text-davinci-003";
            $param["max_tokens"]=1000;

            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openai_api_key,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($param),
                ],
            ]);
    
            $response = file_get_contents('https://api.openai.com/v1/completions', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
    
            //return $response;
            $data = json_decode($response, true);
            return $data['choices'][0];
        }
        
        public function createChatCompletion($userMessage) {
            $param = array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    // Fixed system role for maintaining the subject
                    array('role' => 'system', 'content' => 'iso-helper'),
                    // User's message
                    array('role' => 'user', 'content' => $userMessage),
                ),
                'temperature' => 1.0,
                'max_tokens' => 4000,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
            );
        
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openai_api_key,
            );
        
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($param),
                ],
            ]);
        
            $response = file_get_contents('https://api.openai.com/v1/chat/completions', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        
            $data = json_decode($response, true);
            $responseContent = $data['choices'][0]['message']['content'];
        
            return $responseContent;
        }

        function enter_your_prompt() {
            if (isset($_POST['submit'])) {
                // Get the prompt from the form
                $prompt = $_POST['prompt'];
            
                // Generate proposal based on prompt and existing attachment data
                $proposed_data = $this->generate_openai_proposal($prompt);
            
                // Display the proposed data to the user
                echo "<p>Proposed Data: $proposed_data</p>";
            }

            ?>
            <form method="post">
                <label for="prompt">Enter your prompt:</label><br>
                <textarea id="prompt" name="prompt" rows="4" cols="50"></textarea><br>
                <input type="submit" name="submit" value="Generate Proposal">
            </form>
            <?php
        }
        
        function generate_openai_proposal($prompt) {
            // Initialize an empty string to store error messages
            $error_messages = '';
            $max_token_length = 16385;
            $current_token_length = strlen($prompt);
        
            // Get all attachment post IDs
            $attachment_ids = get_posts(array(
                'post_type' => 'attachment',
                'numberposts' => -1,
                'post_status' => null,
                'fields' => 'ids',
            ));
        
            // Loop through each attachment
            foreach ($attachment_ids as $attachment_id) {
                // Check if adding more content would exceed the token limit
                if ($current_token_length >= $max_token_length) {
                    $error_message = 'Token limit reached, some attachments were skipped.';
                    error_log($error_message);
                    $error_messages .= $error_message . "<br>";
                    break;
                }
        
                // Get the attachment URL
                $attachment_url = wp_get_attachment_url($attachment_id);
                if ($attachment_url) {
                    // Get the file type and check if it's a text-based file
                    $file_type = wp_check_filetype($attachment_url);
                    if (in_array($file_type['ext'], array('txt', 'csv', 'json', 'md'))) {
                        // Attempt to retrieve the attachment content
                        $content = @file_get_contents($attachment_url);
                        if ($content !== false) {
                            // Check if adding this content will exceed the token limit
                            if ($current_token_length + strlen($content) < $max_token_length) {
                                // Add the attachment content to the prompt
                                $prompt .= "\n" . $content;
                                $current_token_length += strlen($content);
                            } else {
                                // Truncate the content to fit within the token limit
                                $available_length = $max_token_length - $current_token_length;
                                $prompt .= "\n" . substr($content, 0, $available_length);
                                $current_token_length += $available_length;
                                $error_message = 'Attachment content truncated to fit token limit: ' . $attachment_url;
                                error_log($error_message);
                                $error_messages .= $error_message . "<br>";
                            }
                        } else {
                            // Log error and skip this attachment
                            $error_message = 'Unable to read attachment: ' . $attachment_url;
                            error_log($error_message);
                            $error_messages .= $error_message . "<br>";
                        }
                    } else {
                        // Skip non-text files
                        $error_message = 'Skipped non-text attachment: ' . $attachment_url;
                        error_log($error_message);
                        $error_messages .= $error_message . "<br>";
                    }
                } else {
                    // Log error if the URL cannot be retrieved
                    $error_message = 'Unable to retrieve attachment URL for ID: ' . $attachment_id;
                    error_log($error_message);
                    $error_messages .= $error_message . "<br>";
                }
            }
        
            // Prepare OpenAI API parameters
            $param = array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    // Fixed system role for maintaining the subject
                    array('role' => 'system', 'content' => 'iso-helper'),
                    // User's message
                    array('role' => 'user', 'content' => $prompt),
                ),
                'temperature' => 1.0,
                'max_tokens' => 4000,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
            );
        
            // Set up request headers
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openai_api_key,
            );
        
            // Create the request context
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($param),
                ],
            ]);
        
            // Call the OpenAI API
            $response = @file_get_contents('https://api.openai.com/v1/chat/completions', false, $context);
            if ($response === false) {
                // Handle error if the request fails
                $error = error_get_last();
                $error_message = 'Request failed: ' . $error['message'];
                error_log($error_message);
                $error_messages .= $error_message . "<br>";
                return 'Error: Unable to connect to the OpenAI API. Please try again later.<br>' . $error_messages;
            }
        
            // Parse the response
            $data = json_decode($response, true);
            if (isset($data['choices'][0]['message']['content'])) {
                $responseContent = $data['choices'][0]['message']['content'];
            } else {
                $error_message = 'Error: Failed to get a valid response from the OpenAI API.';
                $error_log_message = 'Invalid API response: ' . $response;
                error_log($error_log_message);
                $error_messages .= $error_log_message . "<br>";
                $responseContent = $error_message;
            }
        
            // Return the generated response with any error messages
            return $responseContent . '<br>' . $error_messages;
        }

        function parse_and_store_attachment_content($attachment_id) {
            $attachment_url = wp_get_attachment_url($attachment_id);
            $file_type = wp_check_filetype($attachment_url);
            $content = false;
        
            if (in_array($file_type['ext'], array('txt', 'csv', 'json', 'md', 'pdf', 'doc', 'docx'))) {
                if ($file_type['ext'] === 'pdf') {
                    // Parse PDF
                    try {
                        $content = Pdf::getText($attachment_url);
                    } catch (Exception $e) {
                        error_log('Unable to read PDF attachment: ' . $attachment_url);
                    }
                } elseif (in_array($file_type['ext'], array('doc', 'docx'))) {
                    // Parse Word Document
                    try {
                        $phpWord = IOFactory::load($attachment_url);
                        $text = '';
                        foreach ($phpWord->getSections() as $section) {
                            foreach ($section->getElements() as $element) {
                                if (method_exists($element, 'getText')) {
                                    $text .= $element->getText();
                                }
                            }
                        }
                        $content = $text;
                    } catch (Exception $e) {
                        error_log('Unable to read Word attachment: ' . $attachment_url);
                    }
                } else {
                    // Read other text-based files
                    $content = @file_get_contents($attachment_url);
                }
        
                if ($content !== false) {
                    // Store parsed content in post meta
                    update_post_meta($attachment_id, 'parsed_content', $content);
                }
            }
        }
        
        // Hook into the attachment upload/update process
        //add_action('add_attachment', 'parse_and_store_attachment_content');
        //add_action('edit_attachment', 'parse_and_store_attachment_content');

        function generate_openai_proposal_2($prompt) {
            $error_messages = '';
            $max_token_length = 16385;
            $current_token_length = strlen($prompt);
        
            // Get all attachment post IDs
            $attachment_ids = get_posts(array(
                'post_type' => 'attachment',
                'numberposts' => -1,
                'post_status' => null,
                'fields' => 'ids',
            ));
        
            foreach ($attachment_ids as $attachment_id) {
                if ($current_token_length >= $max_token_length) {
                    $error_message = 'Token limit reached, some attachments were skipped.';
                    error_log($error_message);
                    $error_messages .= $error_message . "<br>";
                    break;
                }
        
                // Retrieve parsed content from post meta
                $content = get_post_meta($attachment_id, 'parsed_content', true);
        
                if ($content) {
                    if ($current_token_length + strlen($content) < $max_token_length) {
                        $prompt .= "\n" . $content;
                        $current_token_length += strlen($content);
                    } else {
                        $available_length = $max_token_length - $current_token_length;
                        $prompt .= "\n" . substr($content, 0, $available_length);
                        $current_token_length += $available_length;
                        $error_message = 'Attachment content truncated to fit token limit: ' . wp_get_attachment_url($attachment_id);
                        error_log($error_message);
                        $error_messages .= $error_message . "<br>";
                    }
                } else {
                    $error_message = 'No parsed content found for attachment: ' . wp_get_attachment_url($attachment_id);
                    error_log($error_message);
                    $error_messages .= $error_message . "<br>";
                }
            }
        
            $param = array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    array('role' => 'system', 'content' => 'iso-helper'),
                    array('role' => 'user', 'content' => $prompt),
                ),
                'temperature' => 1.0,
                'max_tokens' => 4000,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
            );
        
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openai_api_key,
            );
        
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($param),
                ],
            ]);
        
            $response = @file_get_contents('https://api.openai.com/v1/chat/completions', false, $context);
            if ($response === false) {
                $error = error_get_last();
                $error_message = 'Request failed: ' . $error['message'];
                error_log($error_message);
                $error_messages .= $error_message . "<br>";
                return 'Error: Unable to connect to the OpenAI API. Please try again later.<br>' . $error_messages;
            }
        
            $data = json_decode($response, true);
            if (isset($data['choices'][0]['message']['content'])) {
                $responseContent = $data['choices'][0]['message']['content'];
            } else {
                $error_message = 'Error: Failed to get a valid response from the OpenAI API.';
                $error_log_message = 'Invalid API response: ' . $response;
                error_log($error_log_message);
                $error_messages .= $error_log_message . "<br>";
                $responseContent = $error_message;
            }
        
            return $responseContent . '<br>' . $error_messages;
        }
    }
    $open_ai_api = new open_ai_api();
}
