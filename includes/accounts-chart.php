<?php


add_shortcode('display-accounts-chart', 'display_accounts_chart');

function display_accounts_chart() {
    // Retrieve necessary options from the WordPress database
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
        return 'Failed to retrieve access token: ' . $response->get_error_message();
    }

    // Decode the response and retrieve the access token
    $token_response = json_decode(wp_remote_retrieve_body($response));
    $access_token = $token_response->access_token ?? '';

    // If access token is empty or not set, return an error
    if (empty($access_token)) {
        return 'Error: Unable to retrieve access token.';
    }

    // Step 2: Get the Chart of Accounts using the access token
    $accounts = get_chart_of_accounts($access_token);

    // Check if the result is a string (indicating an error message)
    if (is_string($accounts)) {
        return $accounts; // Return the error message if any
    }

    // Display the Chart of Accounts data
    ob_start();
    ?>
    <h2>Chart of Accounts</h2>
    <ul>
        <?php foreach ($accounts as $account): ?>
            <li><?php echo esc_html($account['No'] . ' - ' . $account['Name']); ?></li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}

function get_chart_of_accounts($access_token) {
    $tenant_id = get_option('tenant_id');
    $company_name = 'My%20Company'; // Replace with your actual company name
    $url = "https://api.businesscentral.dynamics.com/v2.0/$tenant_id/Sandbox/WS/$company_name/Page/Chart_of_Accounts";

    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json'
        ]
    ]);

    if (is_wp_error($response)) {
        return 'Error: Unable to retrieve Chart of Accounts. ' . $response->get_error_message();
    }

    $body = wp_remote_retrieve_body($response);

    // Log the raw response for analysis
    error_log("Chart of Accounts Response (Raw): " . print_r($body, true));

    $data = json_decode($body, true);

    if (empty($data) || !isset($data['value']) || !is_array($data['value'])) {
        return 'Error: No Chart of Accounts found in the response. Please check the URL, access token, or permissions.';
    }

    return $data['value'];
}
/*
function get_chart_of_accounts($access_token) {
    // Retrieve the tenant ID and company name from WordPress options
    $tenant_id = get_option('tenant_id');
    $company_name = 'My%20Company'; // Replace with your actual company name (use %20 for spaces)

    // Define the URL to access the Chart_of_Accounts page in the Business Central Sandbox environment
    $url = "https://api.businesscentral.dynamics.com/v2.0/$tenant_id/Sandbox/WS/$company_name/Page/Chart_of_Accounts";

    // Make a GET request to the Business Central Web Service API
    $response = wp_remote_get($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,  // Include the access token in the headers
            'Content-Type'  => 'application/json'  // Set the content type to JSON
        ]
    ]);

    // Check if the request failed
    if (is_wp_error($response)) {
        return 'Error: Unable to retrieve Chart of Accounts. ' . $response->get_error_message();
    }

    // Decode the response body to access the Chart of Accounts data
    $body = json_decode(wp_remote_retrieve_body($response), true);

    // Check if the response contains data or an error message
    if (empty($body) || !isset($body['value']) || !is_array($body['value'])) {
        return 'Error: No Chart of Accounts found in the response. Please check the URL, access token, or permissions.';
    }

    // Return the Chart of Accounts data
    return $body['value'];
}
*/