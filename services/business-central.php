<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('business_central')) {
    class business_central {

        public function __construct() {
            add_action('admin_init', array( $this, 'business_central_register_settings' ) );

        }

        function business_central_register_settings() {
            // Register Business Central section
            add_settings_section(
                'business-central-section-settings',
                'Business Central Settings',
                array( $this, 'business_central_section_settings_callback' ),
                'web-service-settings'
            );
        
            // Register fields for Business Central section
            add_settings_field(
                'tenant_id',
                'Tenant ID',
                array( $this, 'tenant_id_callback' ),
                'web-service-settings',
                'business-central-section-settings',
            );
            register_setting('web-service-settings', 'tenant_id');
        
            add_settings_field(
                'client_id',
                'Client ID',
                array( $this, 'client_id_callback' ),
                'web-service-settings',
                'business-central-section-settings',
            );
            register_setting('web-service-settings', 'client_id');
        
            add_settings_field(
                'client_secret',
                'Client Secret',
                array( $this, 'client_secret_callback' ),
                'web-service-settings',
                'business-central-section-settings',
            );
            register_setting('web-service-settings', 'client_secret');
        
            add_settings_field(
                'redirect_uri',
                'Redirect URI',
                array( $this, 'redirect_uri_callback' ),
                'web-service-settings',
                'business-central-section-settings',
            );
            register_setting('web-service-settings', 'redirect_uri');
        
            add_settings_field(
                'bc_scope',
                'Scope',
                array( $this, 'bc_scope_callback' ),
                'web-service-settings',
                'business-central-section-settings',
            );
            register_setting('web-service-settings', 'bc_scope');        
        }

        function business_central_section_settings_callback() {
            echo '<p>Settings for Business Central.</p>';
        }
        
        function tenant_id_callback() {
            $value = get_option('tenant_id');
            echo '<input type="text" name="tenant_id" style="width:100%;" value="' . esc_attr($value) . '" />';
        }
        
        function client_id_callback() {
            $value = get_option('client_id');
            echo '<input type="text" name="client_id" style="width:100%;" value="' . esc_attr($value) . '" />';
        }
        
        function client_secret_callback() {
            $value = get_option('client_secret');
            echo '<input type="text" name="client_secret" style="width:100%;" value="' . esc_attr($value) . '" />';
        }
        
        function redirect_uri_callback() {
            $value = get_option('redirect_uri');
            echo '<input type="text" name="redirect_uri" style="width:100%;" value="' . esc_attr($value) . '" />';
        }
        
        function bc_scope_callback() {
            $value = get_option('bc_scope');
            echo '<input type="text" name="bc_scope" style="width:100%;" value="' . esc_attr($value) . '" />';
        }
        
    
    }
    $business_central = new business_central();
}

// Hook the display_customers_list function to init to ensure WP is fully loaded
add_shortcode('display-customers-list', 'display_customers_list');
/*
add_action('init', 'register_customers_list_shortcode');
function register_customers_list_shortcode() {
    add_shortcode('display-customers-list', 'display_customers_list');
}
*/
function display_customers_list() {
    // Error logging
    error_log("Display Customers List Shortcode called.");

    // Check if OAuth result is ready and display it
    if (isset($_GET['oauth_result_ready']) && $_GET['oauth_result_ready'] == '1') {
        error_log("OAuth result ready.");
        $oauth_callback_result = get_transient('oauth_callback_result');
        if (!empty($oauth_callback_result)) {
            echo '<pre>';
            print_r($oauth_callback_result);
            echo '</pre>';
            delete_transient('oauth_callback_result'); // Clean up after displaying result
        }
        return; // Stop further execution since we displayed the result
    }

    // Prepare the parameters (you need to define $params here)
    $params = array(
        'some_param' => 'some_value',  // Example placeholder for parameters
    );

    // Redirect to authorization URL
    redirect_to_authorization_url($params);
    //exit; // Prevent further execution after redirect
}

function redirect_to_authorization_url($params) {
    // Error logging
    error_log("Redirecting to authorization URL.");

    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $redirect_uri = get_option('redirect_uri');
    $scope = array('https://api.businesscentral.dynamics.com/.default');

    // Get the current URL and encode it for the redirect state
    $original_url = (is_ssl() ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $encoded_original_url = urlencode($original_url);
    $params['encoded_original_url'] = $encoded_original_url;

    // Construct the authorize URL
    $authorize_url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/authorize";
    $state = base64_encode(json_encode($params));

    // Authorization request parameters
    $authorization_params = array(
        'client_id' => $client_id,
        'response_type' => 'code',
        'redirect_uri' => $redirect_uri,
        'scope' => implode(' ', $scope),
        'state' => $state,
    );

    // Redirect to the authorization URL
    wp_redirect($authorize_url . '?' . http_build_query($authorization_params));
    exit;
}

function handle_oauth_callback_redirect() {
    global $wp_query;
    if (isset($wp_query->query_vars['oauth_callback'])) {
        handle_oauth_callback();
        exit;
    }
}
add_action('template_redirect', 'handle_oauth_callback_redirect');

// Register OAuth callback rewrite rule
function register_oauth_callback_endpoint() {
    add_rewrite_rule('^oauth-callback/?', 'index.php?oauth_callback=1', 'top');
}
add_action('init', 'register_oauth_callback_endpoint');

function add_oauth_callback_query_var($vars) {
    $vars[] = 'oauth_callback';
    return $vars;
}
add_filter('query_vars', 'add_oauth_callback_query_var');

// Flush rewrite rules after theme switch
function flush_rewrite_rules_once() {
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'flush_rewrite_rules_once');

// Handle the OAuth callback from Microsoft
function handle_oauth_callback() {
    if (isset($_GET['code'])) {
        $code = sanitize_text_field($_GET['code']);
        $state = isset($_GET['state']) ? json_decode(base64_decode(sanitize_text_field($_GET['state'])), true) : array();

        // Retrieve OAuth settings
        $tenant_id = get_option('tenant_id');
        $client_id = get_option('client_id');
        $client_secret = get_option('client_secret');
        $redirect_uri = get_option('redirect_uri');
        $scope = 'https://api.businesscentral.dynamics.com/.default';

        // Token endpoint
        $token_endpoint = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token";
        $response = wp_remote_post($token_endpoint, array(
            'body' => array(
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirect_uri,
                'scope' => $scope,
            ),
        ));

        // Handle token response
        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body);

            if (isset($data->access_token)) {
                $access_token = $data->access_token;
                $company = isset($state['company']) ? $state['company'] : 'CRONUS USA, Inc.';
                $service = isset($state['service']) ? $state['service'] : 'dgCompanies';
                $post_type = isset($state['post_type']) ? $state['post_type'] : 'GET';
                $body_data = isset($state['body_data']) ? $state['body_data'] : array();
                $etag_data = isset($state['etag_data']) ? $state['etag_data'] : array();

                // Decode and use original URL
                $original_url = isset($state['original_url']) ? urldecode($state['original_url']) : home_url() . '/display-profiles/';

                // Define API endpoint URL
                $endpoint_url = "https://api.businesscentral.dynamics.com/v2.0/$tenant_id/Production/ODataV4/Company('$company')/$service";

                // Handle ETag data if available
                if (!empty($etag_data)) {
                    $filters = [];
                    foreach ($etag_data as $key => $value) {
                        if (is_string($value)) {
                            $filters[] = "$key eq '" . esc_attr($value) . "'";
                        } elseif (is_numeric($value)) {
                            $filters[] = "$key gt " . esc_attr($value);
                        }
                    }
                    if (!empty($filters)) {
                        $endpoint_url = add_query_arg('$filter', implode(' and ', $filters), $endpoint_url);
                    }
                }

                // Make the API request based on post type
                if ($post_type == 'GET') {
                    $response = wp_remote_get($endpoint_url, array(
                        'headers' => array('Authorization' => 'Bearer ' . $access_token),
                    ));
                } else if ($post_type == 'POST') {
                    $response = wp_remote_post($endpoint_url, array(
                        'headers' => array(
                            'Authorization' => 'Bearer ' . $access_token,
                            'Content-Type' => 'application/json',
                        ),
                        'body' => json_encode($body_data),
                    ));
                } else if (in_array($post_type, ['PATCH', 'DELETE'])) {
                    // Handle PATCH and DELETE operations
                    $etag_response = wp_remote_get($endpoint_url, array(
                        'headers' => array('Authorization' => 'Bearer ' . $access_token),
                    ));

                    if (!is_wp_error($etag_response)) {
                        $etag_body = wp_remote_retrieve_body($etag_response);
                        $etag_data = json_decode($etag_body, true);

                        if (isset($etag_data['value']) && is_array($etag_data['value'])) {
                            $etag_header = $etag_data['value'][0]['@odata.etag'];

                            if ($post_type == 'PATCH') {
                                $response = wp_remote_request($endpoint_url, array(
                                    'method' => 'PATCH',
                                    'headers' => array(
                                        'Authorization' => 'Bearer ' . $access_token,
                                        'Content-Type' => 'application/json',
                                        'If-Match' => $etag_header,
                                    ),
                                    'body' => json_encode($body_data),
                                ));
                            } else if ($post_type == 'DELETE') {
                                $response = wp_remote_request($endpoint_url, array(
                                    'method' => 'DELETE',
                                    'headers' => array(
                                        'Authorization' => 'Bearer ' . $access_token,
                                        'If-Match' => $etag_header,
                                    ),
                                ));
                            }
                        }
                    }
                }

                // Handle API response
                if (!is_wp_error($response)) {
                    $body = wp_remote_retrieve_body($response);
                    $properties = json_decode($body, true);

                    if ($properties !== null) {
                        set_transient('oauth_callback_result', $properties, 60);
                    } else {
                        set_transient('oauth_callback_result', 'Error decoding JSON', 60);
                    }
                } else {
                    $error_message = $response->get_error_message();
                    set_transient('oauth_callback_result', 'Error: ' . $error_message, 60);
                }

                // Redirect to original URL
                wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
                exit;
            } else {
                set_transient('oauth_callback_result', 'Failed to get access token', 60);
                wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
                exit;
            }
        } else {
            $error_message = $response->get_error_message();
            set_transient('oauth_callback_result', 'Error: ' . $error_message, 60);
            wp_redirect(add_query_arg('oauth_result_ready', '1', $original_url));
            exit;
        }
    } else {
        set_transient('oauth_callback_result', 'Authorization code not found.', 60);
        wp_redirect(add_query_arg('oauth_result_ready', '1', home_url()));
        exit;
    }
}
