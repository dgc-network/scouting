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
/*
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
    //$access_token = get_business_central_access_token();

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
    if (isset($body['error'])) {
        error_log('Token Error: ' . print_r($body, true));
    } else {
        $access_token = $body['access_token'];
    }

    if (isset($body['access_token'])) {
        $access_token = $body['access_token'];
        $access_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6IjNQYUs0RWZ5Qk5RdTNDdGpZc2EzWW1oUTVFMCIsImtpZCI6IjNQYUs0RWZ5Qk5RdTNDdGpZc2EzWW1oUTVFMCJ9.eyJhdWQiOiJodHRwczovL2FwaS5idXNpbmVzc2NlbnRyYWwuZHluYW1pY3MuY29tIiwiaXNzIjoiaHR0cHM6Ly9zdHMud2luZG93cy5uZXQvNjQzMWIyODQtMjFiMC00ZjVkLTliZmYtOGY5NjM0MTg3OTRlLyIsImlhdCI6MTczMDYwMDQyNSwibmJmIjoxNzMwNjAwNDI1LCJleHAiOjE3MzA2MDQzODAsImFjciI6IjEiLCJhaW8iOiJBVFFBeS84WUFBQUEzYVhmRE1WamYxK1NhNjIwWUpIaGNlT0JPN3BCSTRMdzBCNkFYZkQ1dXVQa1p1YSt6VmxqTm82WUk4emkxc3JJIiwiYW1yIjpbInB3ZCJdLCJhcHBpZCI6ImYwM2I4NTlmLTE5MDgtNDdlNy1hY2QxLTk3MWY0N2JiODQ3NyIsImFwcGlkYWNyIjoiMSIsImlkdHlwIjoidXNlciIsImlwYWRkciI6IjIwMDE6YjAxMToxOjE3NGM6ZjE3MTpiNzgzOmYzODM6ZDNmMCIsIm5hbWUiOiJTSFUiLCJvaWQiOiI4YjQzMDE3MS1mYzllLTQ4YzAtYTRmZi0xNGZlMjY2YTQ2NTAiLCJwdWlkIjoiMTAwMzIwMDFBOTA1NTcyMiIsInJoIjoiMS5BWEVBaExJeFpMQWhYVS1iXzQtV05CaDVUajN2Ylpsc3MxTkJoZ2VtX1R3QnVKOXhBS054QUEuIiwic2NwIjoiRmluYW5jaWFscy5SZWFkV3JpdGUuQWxsIiwic3ViIjoiWEM3TlZNTEZHTGc2akJoWmtnZzMyOUoyb01oYzJPRC1oc0dUQ2V4a3ItMCIsInRpZCI6IjY0MzFiMjg0LTIxYjAtNGY1ZC05YmZmLThmOTYzNDE4Nzk0ZSIsInVuaXF1ZV9uYW1lIjoic2h1QGJjLmVycC5zaHUuZWR1LnR3IiwidXBuIjoic2h1QGJjLmVycC5zaHUuZWR1LnR3IiwidXRpIjoiRjN6MkFIck9uMGU0RmJOWjJWZ1lBQSIsInZlciI6IjEuMCIsIndpZHMiOlsiOTM2MGZlYjUtZjQxOC00YmFhLTgxNzUtZTJhMDBiYWM0MzAxIiwiNTlkNDZmODgtNjYyYi00NTdiLWJjZWItNWMzODA5ZTU5MDhmIiwiNzU5NDEwMDktOTE1YS00ODY5LWFiZTctNjkxYmZmMTgyNzllIiwiMGY5NzFlZWEtNDFlYi00NTY5LWE3MWUtNTdiYjhhM2VmZjFlIiwiNzRlZjk3NWItNjYwNS00MGFmLWE1ZDItYjk1MzlkODM2MzUzIiwiNmU1OTEwNjUtOWJhZC00M2VkLTkwZjMtZTk0MjQzNjZkMmYwIiwiODhkOGUzZTMtOGY1NS00YTFlLTk1M2EtOWI5ODk4Yjg4NzZiIiwiOWYwNjIwNGQtNzNjMS00ZDRjLTg4MGEtNmVkYjkwNjA2ZmQ4IiwiZjI4YTFmNTAtZjZlNy00NTcxLTgxOGItNmExMmYyYWY2YjZjIiwiZTM5NzNiZGYtNDk4Ny00OWFlLTgzN2EtYmE4ZTIzMWM3Mjg2IiwiMTE2NDg1OTctOTI2Yy00Y2YzLTljMzYtYmNlYmIwYmE4ZGNjIiwiZWIxZjRhOGQtMjQzYS00MWYwLTlmYmQtYzdjZGY2YzVlZjdjIiwiM2YxYWNhZGUtMWUwNC00ZmJjLTliNjktZjAzMDJjZDg0YWVmIiwiZjJlZjk5MmMtM2FmYi00NmI5LWI3Y2YtYTEyNmVlNzRjNDUxIiwiYWFmNDMyMzYtMGMwZC00ZDVmLTg4M2EtNjk1NTM4MmFjMDgxIiwiM2VkYWY2NjMtMzQxZS00NDc1LTlmOTQtNWMzOThlZjZjMDcwIiwiYjc5ZmJmNGQtM2VmOS00Njg5LTgxNDMtNzZiMTk0ZTg1NTA5Il0sInhtc19pZHJlbCI6IjIwIDEifQ.LpcIhQ9SK4A-wzh9gZ8akzKOdJE07VKRc5gleVLXHD0aQ8aixQBPsmTToVFKlTNnaorZMdjD16pfQuzo_kzLYnNvzZeBq3Y-kDZAZA0z1_x6Tx7fvcwC0a4eMQYUIx9JsxOf1msGWA_PCHoIVIf-lBskqb-jVq6qFnAGARZE2Xo7yN2gIs6g7D1pjoKgnzkVxfs7N_IPkmlNCXIMjp0yHGWeupIdFR8FwZov-nvZqdYNPc2_ZV7MizKfjLKh4u5EScv4lFU4eQ-dx_55pG5UWY8ANrUNc9rCv2xLj4vqVIlp6BsgP440q3_AQWyIknBBqs2DIj-3J_cw06kjjrn7TQ';
        error_log('Access token: ' . print_r($access_token, true));
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

function get_business_central_chart_of_accounts() {
    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $client_secret = get_option('client_secret');
    $environment = 'Sandbox';
    $company_name = 'CRONUS USA, Inc.';

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
        $access_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6IjNQYUs0RWZ5Qk5RdTNDdGpZc2EzWW1oUTVFMCIsImtpZCI6IjNQYUs0RWZ5Qk5RdTNDdGpZc2EzWW1oUTVFMCJ9.eyJhdWQiOiJodHRwczovL2FwaS5idXNpbmVzc2NlbnRyYWwuZHluYW1pY3MuY29tIiwiaXNzIjoiaHR0cHM6Ly9zdHMud2luZG93cy5uZXQvNjQzMWIyODQtMjFiMC00ZjVkLTliZmYtOGY5NjM0MTg3OTRlLyIsImlhdCI6MTczMDYwMDQyNSwibmJmIjoxNzMwNjAwNDI1LCJleHAiOjE3MzA2MDQzODAsImFjciI6IjEiLCJhaW8iOiJBVFFBeS84WUFBQUEzYVhmRE1WamYxK1NhNjIwWUpIaGNlT0JPN3BCSTRMdzBCNkFYZkQ1dXVQa1p1YSt6VmxqTm82WUk4emkxc3JJIiwiYW1yIjpbInB3ZCJdLCJhcHBpZCI6ImYwM2I4NTlmLTE5MDgtNDdlNy1hY2QxLTk3MWY0N2JiODQ3NyIsImFwcGlkYWNyIjoiMSIsImlkdHlwIjoidXNlciIsImlwYWRkciI6IjIwMDE6YjAxMToxOjE3NGM6ZjE3MTpiNzgzOmYzODM6ZDNmMCIsIm5hbWUiOiJTSFUiLCJvaWQiOiI4YjQzMDE3MS1mYzllLTQ4YzAtYTRmZi0xNGZlMjY2YTQ2NTAiLCJwdWlkIjoiMTAwMzIwMDFBOTA1NTcyMiIsInJoIjoiMS5BWEVBaExJeFpMQWhYVS1iXzQtV05CaDVUajN2Ylpsc3MxTkJoZ2VtX1R3QnVKOXhBS054QUEuIiwic2NwIjoiRmluYW5jaWFscy5SZWFkV3JpdGUuQWxsIiwic3ViIjoiWEM3TlZNTEZHTGc2akJoWmtnZzMyOUoyb01oYzJPRC1oc0dUQ2V4a3ItMCIsInRpZCI6IjY0MzFiMjg0LTIxYjAtNGY1ZC05YmZmLThmOTYzNDE4Nzk0ZSIsInVuaXF1ZV9uYW1lIjoic2h1QGJjLmVycC5zaHUuZWR1LnR3IiwidXBuIjoic2h1QGJjLmVycC5zaHUuZWR1LnR3IiwidXRpIjoiRjN6MkFIck9uMGU0RmJOWjJWZ1lBQSIsInZlciI6IjEuMCIsIndpZHMiOlsiOTM2MGZlYjUtZjQxOC00YmFhLTgxNzUtZTJhMDBiYWM0MzAxIiwiNTlkNDZmODgtNjYyYi00NTdiLWJjZWItNWMzODA5ZTU5MDhmIiwiNzU5NDEwMDktOTE1YS00ODY5LWFiZTctNjkxYmZmMTgyNzllIiwiMGY5NzFlZWEtNDFlYi00NTY5LWE3MWUtNTdiYjhhM2VmZjFlIiwiNzRlZjk3NWItNjYwNS00MGFmLWE1ZDItYjk1MzlkODM2MzUzIiwiNmU1OTEwNjUtOWJhZC00M2VkLTkwZjMtZTk0MjQzNjZkMmYwIiwiODhkOGUzZTMtOGY1NS00YTFlLTk1M2EtOWI5ODk4Yjg4NzZiIiwiOWYwNjIwNGQtNzNjMS00ZDRjLTg4MGEtNmVkYjkwNjA2ZmQ4IiwiZjI4YTFmNTAtZjZlNy00NTcxLTgxOGItNmExMmYyYWY2YjZjIiwiZTM5NzNiZGYtNDk4Ny00OWFlLTgzN2EtYmE4ZTIzMWM3Mjg2IiwiMTE2NDg1OTctOTI2Yy00Y2YzLTljMzYtYmNlYmIwYmE4ZGNjIiwiZWIxZjRhOGQtMjQzYS00MWYwLTlmYmQtYzdjZGY2YzVlZjdjIiwiM2YxYWNhZGUtMWUwNC00ZmJjLTliNjktZjAzMDJjZDg0YWVmIiwiZjJlZjk5MmMtM2FmYi00NmI5LWI3Y2YtYTEyNmVlNzRjNDUxIiwiYWFmNDMyMzYtMGMwZC00ZDVmLTg4M2EtNjk1NTM4MmFjMDgxIiwiM2VkYWY2NjMtMzQxZS00NDc1LTlmOTQtNWMzOThlZjZjMDcwIiwiYjc5ZmJmNGQtM2VmOS00Njg5LTgxNDMtNzZiMTk0ZTg1NTA5Il0sInhtc19pZHJlbCI6IjIwIDEifQ.LpcIhQ9SK4A-wzh9gZ8akzKOdJE07VKRc5gleVLXHD0aQ8aixQBPsmTToVFKlTNnaorZMdjD16pfQuzo_kzLYnNvzZeBq3Y-kDZAZA0z1_x6Tx7fvcwC0a4eMQYUIx9JsxOf1msGWA_PCHoIVIf-lBskqb-jVq6qFnAGARZE2Xo7yN2gIs6g7D1pjoKgnzkVxfs7N_IPkmlNCXIMjp0yHGWeupIdFR8FwZov-nvZqdYNPc2_ZV7MizKfjLKh4u5EScv4lFU4eQ-dx_55pG5UWY8ANrUNc9rCv2xLj4vqVIlp6BsgP440q3_AQWyIknBBqs2DIj-3J_cw06kjjrn7TQ';

        // Set up the API endpoint
        $url = "https://api.businesscentral.dynamics.com/v2.0/{$tenant_id}/{$environment}/ODataV4/Company('{$company_name}')/Chart_of_Accounts";
        $url = "https://api.businesscentral.dynamics.com/v2.0/6431b284-21b0-4f5d-9bff-8f963418794e/Copilot/ODataV4/Company('CRONUS%20USA%2C%20Inc.')/Customers";
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
        error_log('Chart of Accounts: ' . print_r($data, true));

        if (!empty($data['value'])) {
            return $data['value']; // Returns the array of Chart of Accounts
        } else {
            return 'No Chart of Accounts found.';
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
            'scope' => 'https://dynamics.microsoft.com/business-central/overview/Financials.ReadWrite.All',
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

function display_business_central_orders() {
    //$orders = get_business_central_orders();
    //$orders = get_business_central_sales_orders();
    $orders = get_business_central_chart_of_accounts();

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
*/

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
/*
function handle_authorization_redirect() {
    if (isset($_GET['code']) && isset($_GET['state']) && wp_verify_nonce($_GET['state'], 'microsoft_auth')) {
        $auth_code = sanitize_text_field($_GET['code']);

        update_option('microsoft_auth_code', $auth_code); // Store the auth code

        error_log('Authorization code stored successfully.');

        // Exchange the authorization code for an access token
        $access_token = exchange_authorization_code_for_token($auth_code);
        
        if ($access_token) {
            error_log('Access Token: ' . $access_token);

            // Call the function to retrieve Business Central data
            $data = get_business_central_data($access_token);

            // Log or handle the returned data
            error_log('Business Central Data: ' . print_r($data, true));
        } else {
            error_log('Failed to retrieve access token.');
        }

    } else {
        error_log('Authorization failed or invalid state.');
    }
}
add_action('template_redirect', 'handle_authorization_redirect');
*/
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

function get_business_central_data($access_token) {
    $company_id = 'your_company_id'; // Replace with your actual company ID
    $company_id = 'CRONUS USA, Inc.';
    $url = "https://api.businesscentral.dynamics.com/v2.0/{$tenant_id}/Sandbox/api/v2.0/companies({$company_id})/chartOfAccounts";

    $headers = [
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type' => 'application/json'
    ];

    $response = wp_remote_get($url, ['headers' => $headers]);
    $data = json_decode(wp_remote_retrieve_body($response), true);

    error_log('Business Central Data: ' . print_r($data, true));

    return $data;
}
/*
// Shortcode function to retrieve and display Business Central data
function display_business_central_data() {
    // Retrieve the authorization code (ensure you have previously stored it after user login)
    $auth_code = get_option('microsoft_auth_code'); // Replace with how you handle storing/retrieving the auth code
    
    if (!$auth_code) {
        return 'Authorization code not found. Please authenticate first.';
    }

    // Exchange the authorization code for an access token
    $access_token = exchange_authorization_code_for_token($auth_code);

    if (!$access_token) {
        return 'Failed to retrieve access token.';
    }

    // Retrieve Business Central data using the access token
    $data = get_business_central_data($access_token);

    if (is_array($data)) {
        // Format the data for output
        ob_start(); // Start output buffering to capture HTML output
        echo '<h2>Business Central Data:</h2><pre>';
        print_r($data); // Print the data in a readable format
        echo '</pre>';
        return ob_get_clean(); // Return the buffered output
    } else {
        return 'No data found or failed to retrieve data.';
    }
}

// Register the shortcode
add_shortcode('business_central_data', 'display_business_central_data');
*/
function display_business_central_data() {
    // Retrieve the stored access token, if any
    $access_token = get_option('business_central_access_token');
    
    // Check if the access token exists and is valid
    if (!$access_token || token_is_expired($access_token)) {
        // No valid access token, redirect to Microsoft authorization
        redirect_to_microsoft_auth();
        exit; // Stop further execution until authorization completes
    }
    
    // Use the access token to retrieve and display Business Central data
    $data = get_business_central_data($access_token);
    
    if ($data) {
        // Display the data (or handle it as needed)
        echo '<pre>' . print_r($data, true) . '</pre>';
    } else {
        echo 'No data available or failed to retrieve data.';
    }
}
add_shortcode('business_central_data', 'display_business_central_data');

function token_is_expired($access_token) {
    // Retrieve token's expiration time from options
    $expiration = get_option('business_central_token_expiration');
    
    // Check if the current time is past the token's expiration time
    return time() >= $expiration;
}

// Redirect to authorization endpoint
function redirect_to_microsoft_auth() {
    $state = wp_create_nonce('microsoft_auth');
    $tenant_id = get_option('tenant_id');
    $client_id = get_option('client_id');
    $redirect_uri = urlencode(site_url('/your-redirect-handler')); // Define your callback URL
    $scope = 'https://api.businesscentral.dynamics.com/.default';

    $authorization_url = "https://login.microsoftonline.com/{$tenant_id}/oauth2/v2.0/authorize?client_id={$client_id}&response_type=code&redirect_uri={$redirect_uri}&response_mode=query&scope={$scope}&state={$state}";

    error_log('Generated state: ' . $state); // Log for debugging
    wp_redirect($authorization_url);
    exit;
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
                $data = get_business_central_data($access_token);
                error_log('Business Central Data: ' . print_r($data, true));
            } else {
                error_log('Failed to retrieve access token.');
            }
        } else {
            error_log('Authorization failed or invalid state.');
        }
    }
}

add_action('template_redirect', 'handle_authorization_redirect');
