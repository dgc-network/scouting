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
                'company_name',
                'Company name',
                array( $this, 'company_name_callback' ),
                'web-service-settings',
                'business-central-section-settings',
            );
            register_setting('web-service-settings', 'company_name');        
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
        
        function company_name_callback() {
            $value = get_option('company_name');
            echo '<input type="text" name="company_name" style="width:100%;" value="' . esc_attr($value) . '" />';
        }
        
    
    }
    $business_central = new business_central();
}

// Redirect to authorization endpoint
function redirect_to_authorization_url() {
    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $redirect_uri = urlencode(site_url('/your-redirect-handler')); // Replace with your handler URL
    
    // The authorization URL
    $auth_url = "https://login.microsoftonline.com/{$tenant_id}/oauth2/v2.0/authorize";
    $auth_url .= "?client_id={$client_id}";
    $auth_url .= "&response_type=code";
    $auth_url .= "&redirect_uri={$redirect_uri}";
    $auth_url .= "&response_mode=query";
    $auth_url .= "&scope=" . urlencode('https://api.businesscentral.dynamics.com/.default');
    $auth_url .= "&state=" . wp_create_nonce('microsoft_auth');

    // Redirect the user
    wp_redirect($auth_url);
    exit;
}

function exchange_authorization_code_for_token($auth_code) {
    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $client_secret = get_option('client_secret');
    $redirect_uri = site_url('/your-redirect-handler'); // Must match the redirect URI used earlier

    $token_url = "https://login.microsoftonline.com/{$tenant_id}/oauth2/v2.0/token";

    $response = wp_remote_post($token_url, [
        'body' => [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'grant_type' => 'authorization_code',
            'code' => $auth_code,
            'redirect_uri' => $redirect_uri,
            'scope' => 'https://api.businesscentral.dynamics.com/.default'
        ]
    ]);

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['access_token'])) {
        return $body['access_token'];
    } else {
        error_log('Error retrieving access token: ' . print_r($body, true));
        return false;
    }
}

function token_is_expired($access_token) {
    // Split the token into its three parts: header, payload, and signature
    $token_parts = explode('.', $access_token);

    if (count($token_parts) === 3) {
        // Decode the payload (second part of the token) from base64
        $payload = json_decode(base64_decode($token_parts[1]), true);

        // Check if the payload contains the 'exp' claim
        if (isset($payload['exp'])) {
            $expiration = $payload['exp'];

            // Check if the current time is past the token's expiration time
            return time() >= $expiration;
        }
    }

    // If the token format is invalid or 'exp' claim is missing, assume token is expired
    return true;
}

// Handle the authorization callback
function handle_authorization_redirect() {
    if (isset($_GET['code']) && isset($_GET['state'])) {
        $state = sanitize_text_field($_GET['state']);
        
        error_log('Received state: ' . $state); // Log received state for debugging

        // Verify nonce to check state validity
        if (wp_verify_nonce($state, 'microsoft_auth')) {
            $auth_code = sanitize_text_field($_GET['code']);
            update_option('microsoft_auth_code', $auth_code);

            error_log('Authorization code stored successfully.');
            $access_token = exchange_authorization_code_for_token($auth_code);

            if ($access_token) {
                update_option('business_central_access_token', $access_token);
                //error_log('Access token: ' . print_r($access_token, true));
            } else {
                error_log('Failed to retrieve access token.');
            }
        } else {
            error_log('Authorization failed or invalid state.');
        }
    }
}
add_action('template_redirect', 'handle_authorization_redirect');

function get_business_central_data($service_name='Chart_of_Accounts', $company_name='CRONUS USA, Inc.', $environment='Sandbox') {

    // Retrieve the stored access token, if any
    $access_token = get_option('business_central_access_token');
    //error_log('Access token: ' . print_r($access_token, true));

    // Check if the access token exists and is valid
    if (!$access_token || token_is_expired($access_token)) {
        // No valid access token, redirect to Microsoft authorization
        redirect_to_authorization_url();
        exit; // Stop further execution until authorization completes
    }
    $tenant_id = get_option('tenant_id');
    $encoded_company_name = rawurlencode($company_name);
    $url = "https://api.businesscentral.dynamics.com/v2.0/{$tenant_id}/{$environment}/ODataV4/Company('{$encoded_company_name}')/{$service_name}";

    $headers = [
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type' => 'application/json'
    ];

    $response = wp_remote_get($url, ['headers' => $headers]);
    $data = json_decode(wp_remote_retrieve_body($response), true);
    //error_log('Business Central Data: ' . print_r($data, true));

    return $data;
}

function display_business_central_data() {
    // Define available services
    $services = [
        'Chart_of_Accounts' => 'Chart of Accounts',
        'ItemSalesAndProfit' => 'Item Sales and Profit',
        'FixedAssets' => 'Fixed Assets',
        'ItemCards' => 'Item Cards',
        'Projects' => 'Projects'
    ];

    // Environment and company name configuration
    $environment = 'Sandbox';
    $company_name = 'CRONUS USA, Inc.';

    // Display list of clickable links for each service
    echo '<ul>';
    foreach ($services as $key => $label) {
        echo '<li><a href="?service=' . urlencode($key) . '">' . esc_html($label) . '</a></li>';
    }
    echo '</ul>';

    // Check if a service name is provided in the URL
    if (isset($_GET['service']) && array_key_exists($_GET['service'], $services)) {
        $service_name = sanitize_text_field($_GET['service']);

        // Retrieve data for the selected service
        $data = get_business_central_data($service_name, $company_name, $environment);

        // Display the retrieved data
        if ($data) {
            echo '<h3>Data for ' . esc_html($services[$service_name]) . '</h3>';
            echo '<pre>' . print_r($data, true) . '</pre>';
        } else {
            echo '<p>No data available or failed to retrieve data.</p>';
        }
    } else {
        echo '<p>Please select a service to view its data.</p>';
    }
}

// Register the shortcode to display Business Central data
add_shortcode('business_central_data', 'display_business_central_data');
/*
function display_business_central_data() {

    $service_name='Chart_of_Accounts';
    $service_name='ItemSalesAndProfit';
    $service_name='FixedAssets';
    $service_name='ItemCards';
    $service_name='Projects';

    $environment = 'Sandbox';
    $company_name = 'CRONUS USA, Inc.';  // Original company name
    $data = get_business_central_data($service_name, $company_name, $environment);
    
    if ($data) {
        // Display the data (or handle it as needed)
        echo '<pre>' . print_r($data, true) . '</pre>';
    } else {
        echo 'No data available or failed to retrieve data.';
    }
}
add_shortcode('business_central_data', 'display_business_central_data');
*/