<?php
/*
 * Description: Displays a Microsoft Business Central customer card.
 * Version: 1.0
 * Author: Your Name
 */

// Hook to create a custom page in WordPress
add_shortcode('display-customers-list', 'display_customers_list');
function display_customers_list() {
    // Retrieve necessary options from the database
    $client_id = get_option('client_id');
    $client_secret = get_option('client_secret');
    $tenant_id = get_option('tenant_id');
    $scope = 'https://api.businesscentral.dynamics.com/.default';

    // Step 1: Get access token using OAuth 2.0 Client Credentials flow
    $token_url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token";

    // Prepare the request body for the OAuth 2.0 token request
    $body = [
        'grant_type' => 'client_credentials',
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'scope' => $scope
    ];

    $response = wp_remote_post($token_url, [
        'body' => $body
    ]);

    // Check for errors in the response
    if (is_wp_error($response)) {
        return 'Failed to retrieve access token.';
    }

    // Decode the response and retrieve the access token
    $token_response = json_decode(wp_remote_retrieve_body($response));
    $access_token = $token_response->access_token ?? '';

    // If access token is empty or not set, return an error
    if (empty($access_token)) {
        return 'Error: Unable to retrieve access token.';
    }

    // Step 2: Get the company ID
    $company_id = get_company_id($access_token);

    // Check if company_id is valid
    if (empty($company_id)) {
        return 'Error: Unable to retrieve company ID.';
    }

    // Step 3: Get the list of customers using the access token and company ID
    $customers = get_customers($access_token, $company_id);

    // Check if the result is a string (indicating an error message)
    if (is_string($customers)) {
        return $customers; // Return error message if any
    }

    // Display the customer data
    ob_start();
    ?>
    <h2>Customers list</h2>
    <div><?php echo 'Company ID: '.$company_id;?></div>
    <ul>
        <?php foreach ($customers as $customer): ?>
            <li><?php echo esc_html($customer['displayName'] . ' (' . $customer['id'] . ')'); ?></li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}

function get_company_id($access_token) {
    $tenant_id = get_option('tenant_id');
    $url = "https://api.businesscentral.dynamics.com/v2.0/$tenant_id/Sandbox/api/v1.0/companies";

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json'
        ]
    ]);

    // Log the raw response for debugging
    error_log("Response: " . print_r($response, true));

    // Check if the request failed
    if (is_wp_error($response)) {
        return 'Error: Unable to fetch company ID. ' . $response->get_error_message();
    }

    $body = json_decode(wp_remote_retrieve_body($response));

    // Log the decoded body for inspection
    error_log("Response Body: " . print_r($body, true));

    // Check if the response contains company data
    if (empty($body) || !isset($body->value) || !is_array($body->value) || count($body->value) === 0) {
        return 'Error: No companies found in the response. Please check if the access token is valid and if there are companies available.';
    }

    // Return the first company's ID
    return $body->value[0]->id ?? '';
}

// Function to get the customers list
function get_customers($access_token, $company_id) {
    $tenant_id = get_option('tenant_id');
    $url = "https://api.businesscentral.dynamics.com/v2.0/$tenant_id/api/v1.0/companies($company_id)/customers";
    //$url = "https://api.businesscentral.dynamics.com/v2.0/$tenant_id/Sandbox/WS/My%20Company/Page/Chart_of_Accounts";

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json'
        ]
    ]);

    if (is_wp_error($response)) {
        return 'Error fetching customers.';
    }

    $body = json_decode(wp_remote_retrieve_body($response));

    // Return the list of customers if available
    return $body->value ?? [];
}

