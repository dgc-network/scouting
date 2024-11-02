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

function get_business_central_access_token() {
    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $client_secret = get_option('client_secret');

    $url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token";

    $body = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'scope' => 'https://api.businesscentral.dynamics.com/.default',
        'grant_type' => 'client_credentials',
    ];

    $response = wp_remote_post($url, [
        'body' => $body,
    ]);

    if (is_wp_error($response)) {
        error_log("Error getting access token: " . $response->get_error_message());
        return false;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    return $data['access_token'] ?? false;
}

function parse_jwt($jwt) {
    // Split the JWT into its three parts: header, payload, and signature
    $tokenParts = explode('.', $jwt);
    if (count($tokenParts) !== 3) {
        return 'Invalid token format';
    }

    // Decode the payload (second part) from base64
    $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));

    // Convert the payload from JSON to an associative array
    $data = json_decode($payload, true);

    return $data;
}

function get_business_central_company_id() {
    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $client_secret = get_option('client_secret');
    $environment = 'Sandbox';

    // Set up the OAuth 2.0 token URL
    $token_url = "https://login.microsoftonline.com/{$tenant_id}/oauth2/v2.0/token";

    // Request access token
    $response = wp_remote_post($token_url, [
        'body' => [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'scope' => 'https://api.businesscentral.dynamics.com/.default',
            //'scope' => 'https://api.businesscentral.dynamics.com/Financials.ReadWrite.All',
            'grant_type' => 'client_credentials'
        ]
    ]);

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($body['error'])) {
        error_log('Token Error: ' . print_r($body, true));
    } else {
        $access_token = $body['access_token'];
    }
    
    if (isset($body['access_token'])) {
        $access_token = $body['access_token'];
        error_log('Access token: ' . print_r($access_token, true));
        //$access_token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6IjNQYUs0RWZ5Qk5RdTNDdGpZc2EzWW1oUTVFMCIsImtpZCI6IjNQYUs0RWZ5Qk5RdTNDdGpZc2EzWW1oUTVFMCJ9.eyJhdWQiOiJodHRwczovL2FwaS5idXNpbmVzc2NlbnRyYWwuZHluYW1pY3MuY29tIiwiaXNzIjoiaHR0cHM6Ly9zdHMud2luZG93cy5uZXQvOGZkNDhjZmQtMTE1Ni00YjNhLWJjMjEtMzJlMGU4OTFlZGE5LyIsImlhdCI6MTczMDU1NzE5OSwibmJmIjoxNzMwNTU3MTk5LCJleHAiOjE3MzA1NjEwOTksImFpbyI6ImsyQmdZT0ROZUJtOTFVTnV0NkpkOEJJbTNnZ21BQT09IiwiYXBwaWQiOiI5MTRlZjRjYi05MzczLTQwZTMtODZkZS01ZWQ3MzIzMDQyNjEiLCJhcHBpZGFjciI6IjEiLCJpZHAiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC84ZmQ0OGNmZC0xMTU2LTRiM2EtYmMyMS0zMmUwZTg5MWVkYTkvIiwiaWR0eXAiOiJhcHAiLCJvaWQiOiIwZTFlYjFiOC0yYzEyLTQyYjctODJiNy02ODg1MGRlM2U4NWMiLCJyaCI6IjEuQWJjQV9ZelVqMVlST2t1OElUTGc2Skh0cVQzdmJabHNzMU5CaGdlbV9Ud0J1Sl84QUFDM0FBLiIsInJvbGVzIjpbIkFkbWluQ2VudGVyLlJlYWRXcml0ZS5BbGwiLCJBUEkuUmVhZFdyaXRlLkFsbCJdLCJzdWIiOiIwZTFlYjFiOC0yYzEyLTQyYjctODJiNy02ODg1MGRlM2U4NWMiLCJ0aWQiOiI4ZmQ0OGNmZC0xMTU2LTRiM2EtYmMyMS0zMmUwZTg5MWVkYTkiLCJ1dGkiOiJ5UWcwUW9tRDVFaUFnY0tFYUNRSEFBIiwidmVyIjoiMS4wIiwieG1zX2lkcmVsIjoiMTYgNyJ9.IejLjISATuVN5YpzvfK8NC36LZT-NAxF-8tVl2kdoZ2FiocGvFHx5TwJF77-c9lFifNJpeErUt847L4BF9_a_6DpcU76HRHLE8ONM1eOT4WWxVtNUVhudhvGnfmElUKFZGgizmjLP72v9sK2xgT7LuJIMl1WWhoKK9OpOVZTS0njRL5JAz3Pyv-GppFMnH1_jadBbbJrFd0arp1znIdwHVht_jUV0GGr7GbohpYR2yw976V_chX0RUzXITqlB-fpMruoz4qLvfyPheS_3YzWtuPsZFrB1JwPz_gngghhklCXt8nM1PtMHgOIiO6_1hjZLtPM2wmPFdYMWCRywinn3w";
        $data = parse_jwt($access_token);
        
        // Log the parsed data
        error_log('Access Token Data: ' . print_r($data, true));
        
        
        // Set up the companies API endpoint
        $url = "https://api.businesscentral.dynamics.com/v2.0/{$environment}/api/v2.0/companies";

        // Set up the headers
        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json'
        ];

        // Make the request to the API
        $response = wp_remote_get($url, ['headers' => $headers]);

        // Check for errors
        if (is_wp_error($response)) {
            return 'Request failed: ' . $response->get_error_message();
        }

        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
        error_log('Companies: ' . print_r($data, true));

        if (!empty($data['value'])) {
            // Return the first company's ID as an example
            return $data['value'][0]['id']; // Replace 0 with the appropriate index if needed
        } else {
            return 'No companies found.';
        }
    } else {
        return 'Failed to retrieve access token.';
    }
}

function get_business_central_sales_orders() {
    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $client_secret = get_option('client_secret');
    $environment = 'Sandbox';
    //$company_id = 'CRONUS USA, Inc.';
    // To use the function and print the company ID
    $company_id = get_business_central_company_id();
    error_log('Company ID: ' . print_r($company_id, true));
    //echo 'Company ID: ' . $company_id;
    

    // Set up the OAuth 2.0 token URL
    $token_url = "https://login.microsoftonline.com/{$tenant_id}/oauth2/v2.0/token";

    // Request access token
    $response = wp_remote_post($token_url, [
        'body' => [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'scope' => 'https://api.businesscentral.dynamics.com/.default',
            'grant_type' => 'client_credentials'
        ]
    ]);

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['access_token'])) {
        $access_token = $body['access_token'];

        // Set up the API endpoint
        $url = "https://api.businesscentral.dynamics.com/v2.0/{$environment}/api/v2.0/companies('{$company_id}')/salesOrders";

        // Set up the headers
        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json'
        ];

        // Make the request to the API
        $response = wp_remote_get($url, ['headers' => $headers]);

        // Check for errors
        if (is_wp_error($response)) {
            return 'Request failed: ' . $response->get_error_message();
        }

        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
        error_log('Sales Orders: ' . print_r($data, true));

        if (!empty($data['value'])) {
            return $data['value']; // Returns the array of sales orders
        } else {
            return 'No sales orders found.';
        }
    } else {
        return 'Failed to retrieve access token.';
    }
}
/*
// To use the function and print results
$sales_orders = get_business_central_sales_orders();
echo '<pre>';
print_r($sales_orders);
echo '</pre>';

function get_business_central_orders() {
    $access_token = get_business_central_access_token();
    $tenant_id = get_option('tenant_id');
    $company_name = get_option('company_name');

    if (!$access_token) {
        return 'Failed to retrieve access token';
    }

    $url = "https://api.businesscentral.dynamics.com/v2.0/$tenant_id/Sandbox/ODataV4/Company(\"$company_name\")/SalesOrders";

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json',
        ],
    ]);

    if (is_wp_error($response)) {
        return 'Error retrieving orders: ' . $response->get_error_message();
    }

    $orders = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($orders['value'])) {
        return $orders['value'];
    }

    return 'No orders found';
}
*/
function display_business_central_orders() {
    //$orders = get_business_central_orders();
    $orders = get_business_central_sales_orders();

    if (is_string($orders)) {
        return '<p>' . esc_html($orders) . '</p>';
    }

    $output = '<ul>';
    foreach ($orders as $order) {
        $output .= '<li>Order No: ' . esc_html($order['No']) . ' - Customer: ' . esc_html($order['CustomerName']) . '</li>';
    }
    $output .= '</ul>';

    return $output;
}
add_shortcode('business_central_orders', 'display_business_central_orders');
