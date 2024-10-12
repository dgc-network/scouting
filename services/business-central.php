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

if (isset($yourArray[0])) {
    // Your code using $yourArray[0]
} else {
    // Handle the case where $yourArray[0] is not set
}

add_shortcode('display-customers-list', 'display_customers_list');
function display_customers_list() {
    error_log("Display Customers List Shortcode called.");

    // Check for OAuth callback (code present in URL)
    if (isset($_GET['code'])) {
        error_log("Authorization code detected, handling OAuth callback.");
        handle_oauth_callback(); // Ensure this function processes the code correctly
        return; // Stop further execution to avoid redirect loop
    }

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
        return; // Stop further execution after displaying result
    }

    // Prevent redirect if already in OAuth flow
    if (isset($_GET['oauth_in_progress']) && $_GET['oauth_in_progress'] == '1') {
        error_log("OAuth flow in progress, preventing redirection.");
        return; // Stop further execution if we're already in the OAuth process
    }

    // Prepare parameters for the redirect
    $params = array(
        'some_param' => 'some_value',  // Example placeholder for parameters
    );

    // Add a flag to indicate OAuth flow has started
    $redirect_url = add_query_arg('oauth_in_progress', '1', (is_ssl() ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

    // Redirect to authorization URL
    error_log("Redirecting to authorization URL.");
    redirect_to_authorization_url($params, $redirect_url);
    //exit; // Stop further execution after redirect
}

function redirect_to_authorization_url($params, $redirect_url) {
    error_log("Redirecting to authorization URL.");

    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $redirect_uri = get_option('redirect_uri');
    $scope = array('https://api.businesscentral.dynamics.com/.default');

    // Encode the redirect URL for OAuth state
    $encoded_original_url = urlencode($redirect_url);
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

    // Log and redirect
    $redirect_uri_full = $authorize_url . '?' . http_build_query($authorization_params);
    error_log("Redirecting to: " . $redirect_uri_full);
    wp_redirect($redirect_uri_full);
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

                // Add here code to make API request using $access_token

                // Redirect to original URL
                wp_redirect(add_query_arg('oauth_result_ready', '1', home_url()));
                exit;
            } else {
                set_transient('oauth_callback_result', 'Failed to get access token', 60);
                wp_redirect(add_query_arg('oauth_result_ready', '1', home_url()));
                exit;
            }
        } else {
            $error_message = $response->get_error_message();
            set_transient('oauth_callback_result', 'Error: ' . $error_message, 60);
            wp_redirect(add_query_arg('oauth_result_ready', '1', home_url()));
            exit;
        }
    } else {
        set_transient('oauth_callback_result', 'Authorization code not found.', 60);
        wp_redirect(add_query_arg('oauth_result_ready', '1', home_url()));
        exit;
    }
}
