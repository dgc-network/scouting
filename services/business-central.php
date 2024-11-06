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

function get_available_services($environment='Sandbox') {
    // Retrieve the stored access token, if any
    $access_token = get_option('business_central_access_token');
    // Check if the access token exists and is valid
    if (!$access_token || token_is_expired($access_token)) {
        // No valid access token, redirect to Microsoft authorization
        redirect_to_authorization_url();
        exit; // Stop further execution until authorization completes
    }

    $tenant_id = get_option('tenant_id');
    $url = "https://api.businesscentral.dynamics.com/v2.0/{$tenant_id}/{$environment}/ODataV4/$metadata";

    // Set up the headers with the access token
    $args = [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Accept' => 'application/xml'
        ]
    ];

    // Perform the request
    $response = wp_remote_get($url, $args);

    // Check for errors
    if (is_wp_error($response)) {
        error_log("Failed to retrieve metadata: " . $response->get_error_message());
        return [];
    }

    $body = wp_remote_retrieve_body($response);

    // Parse the XML to extract service names
    $services = [];
    if ($body) {
        // Check if the response is JSON
        $data = json_decode($body, true);
    
        if (json_last_error() === JSON_ERROR_NONE && isset($data['value'])) {
            // Loop through the JSON data to extract service names
            foreach ($data['value'] as $entity) {
                if (isset($entity['name'])) {
                    $services[$entity['name']] = $entity['name'];
                }
            }
        } else {
            error_log('Failed to parse JSON response or unexpected structure.');
        }
    } else {
        error_log('Failed to retrieve metadata body.');
    }
    return $services;
}

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

function create_business_central_data($service_name, $company_name, $environment, $data) {
    $access_token = get_option('business_central_access_token');
    if (!$access_token || token_is_expired($access_token)) {
        redirect_to_authorization_url();
        exit;
    }
    
    $tenant_id = get_option('tenant_id');
    $encoded_company_name = rawurlencode($company_name);
    $url = "https://api.businesscentral.dynamics.com/v2.0/{$tenant_id}/{$environment}/ODataV4/Company('{$encoded_company_name}')/{$service_name}";

    $headers = [
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type' => 'application/json'
    ];

    $response = wp_remote_post($url, [
        'headers' => $headers,
        'body' => json_encode($data)
    ]);

    return json_decode(wp_remote_retrieve_body($response), true);
}

function update_business_central_data($service_name, $company_name, $environment, $etag, $data) {
    $tenant_id = get_option('tenant_id');
    $access_token = get_option('business_central_access_token');
    $encoded_company_name = rawurlencode($company_name);

    // Construct the base URL for the entity
    $url = "https://api.businesscentral.dynamics.com/v2.0/{$tenant_id}/{$environment}/ODataV4/Company('{$encoded_company_name}')/{$service_name}";

    // Configure headers including the If-Match header for the etag
    $headers = [
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type' => 'application/json',
        'If-Match' => str_replace('\"', '"', $etag)  // Removes escape characters if any
    ];

    // Send the PUT request with the JSON data
    $put_response = wp_remote_request($url, [
        'method' => 'PUT',
        'headers' => $headers,
        'body' => json_encode($data)
    ]);

    $response_code = wp_remote_retrieve_response_code($put_response);
    $response_body = wp_remote_retrieve_body($put_response);

    if ($response_code === 204) {
        return "Item successfully updated.";
    } else {
        error_log('Error updating item: ' . print_r($response_body, true));
        return json_decode($response_body, true);
    }
}

function delete_business_central_data($service_name, $company_name, $environment, $etag) {
    $tenant_id = get_option('tenant_id');
    $access_token = get_option('business_central_access_token');
    $encoded_company_name = rawurlencode($company_name);

    // Construct the URL for the specific record
    $url = "https://api.businesscentral.dynamics.com/v2.0/{$tenant_id}/{$environment}/ODataV4/Company('{$encoded_company_name}')/{$service_name}";

    // Set up headers with If-Match header for the etag
    $headers = [
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type' => 'application/json',
        'If-Match' => str_replace('\"', '"', $etag)
    ];

    // Send the DELETE request
    $response = wp_remote_request($url, [
        'method' => 'DELETE',
        'headers' => $headers
    ]);

    $response_code = wp_remote_retrieve_response_code($response);

    if ($response_code === 204) {
        return "Item successfully deleted.";
    } else {
        error_log('Error deleting item: ' . print_r(wp_remote_retrieve_body($response), true));
        return json_decode(wp_remote_retrieve_body($response), true);
    }
}
/*
function update_business_central_data($service_name, $company_name, $environment, $id, $data) {
    $tenant_id = get_option('tenant_id');
    $access_token = get_option('business_central_access_token');
    $encoded_company_name = rawurlencode($company_name);
    $encoded_id = rawurlencode($id);

    // Construct the base URL for the entity
    $url = "https://api.businesscentral.dynamics.com/v2.0/{$tenant_id}/{$environment}/ODataV4/Company('{$encoded_company_name}')/{$service_name}('{$encoded_id}')";

    // Step 1: Get the record to retrieve the etag
    $headers = [
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type' => 'application/json'
    ];
    $get_response = wp_remote_get($url, ['headers' => $headers]);

    if (wp_remote_retrieve_response_code($get_response) !== 200) {
        error_log('Failed to retrieve record for etag: ' . print_r(wp_remote_retrieve_body($get_response), true));
        return json_decode(wp_remote_retrieve_body($get_response), true);
    }

    // Step 2: Extract the etag from the GET response
    $get_data = json_decode(wp_remote_retrieve_body($get_response), true);
    $etag = isset($get_data['@odata.etag']) ? $get_data['@odata.etag'] : null;

    if (!$etag) {
        error_log('No etag found in the GET response.');
        return ['error' => 'Etag not found.'];
    }

    // Remove escape characters from the etag
    $etag = str_replace('\"', '"', $etag);

    // Step 3: Perform the PUT request with the If-Match header
    $headers['If-Match'] = $etag;
    $put_response = wp_remote_request($url, [
        'method' => 'PUT',
        'headers' => $headers,
        'body' => json_encode($data)
    ]);

    $response_code = wp_remote_retrieve_response_code($put_response);
    $response_body = wp_remote_retrieve_body($put_response);

    if ($response_code === 204) {
        // Successful update
        return "Item successfully updated.";
    } else {
        // Log or return error details
        error_log('Error updating item: ' . print_r($response_body, true));
        return json_decode($response_body, true);
    }
}

function delete_business_central_data($service_name, $company_name, $environment, $record_id) {
    $access_token = get_option('business_central_access_token');
    if (!$access_token || token_is_expired($access_token)) {
        redirect_to_authorization_url();
        exit;
    }

    $tenant_id = get_option('tenant_id');
    $encoded_company_name = rawurlencode($company_name);
    $encoded_record_id = rawurlencode($record_id);

    // Construct the URL for the specific record
    $url = "https://api.businesscentral.dynamics.com/v2.0/{$tenant_id}/{$environment}/ODataV4/Company('{$encoded_company_name}')/{$service_name}('{$encoded_record_id}')";

    // Step 1: Retrieve the etag by making a GET request
    $headers = [
        'Authorization' => 'Bearer ' . $access_token,
        'Content-Type' => 'application/json'
    ];
    $get_response = wp_remote_get($url, ['headers' => $headers]);

    if (wp_remote_retrieve_response_code($get_response) !== 200) {
        error_log('Failed to retrieve record for etag: ' . print_r(wp_remote_retrieve_body($get_response), true));
        return json_decode(wp_remote_retrieve_body($get_response), true);
    }

    // Step 2: Extract and format the etag from the GET response
    $get_data = json_decode(wp_remote_retrieve_body($get_response), true);
    $etag = isset($get_data['@odata.etag']) ? $get_data['@odata.etag'] : null;

    if (!$etag) {
        error_log('No etag found in the GET response.');
        return ['error' => 'Etag not found.'];
    }

    // Remove escape characters from the etag
    $etag = str_replace('\"', '"', $etag);

    // Step 3: Perform the DELETE request with the If-Match header
    $headers['If-Match'] = $etag;
    $response = wp_remote_request($url, [
        'method' => 'DELETE',
        'headers' => $headers
    ]);

    $response_code = wp_remote_retrieve_response_code($response);

    if ($response_code === 204) {
        // Successful deletion
        return "Item successfully deleted.";
    } else {
        // Log or return error details
        error_log('Error deleting item: ' . print_r(wp_remote_retrieve_body($response), true));
        return json_decode(wp_remote_retrieve_body($response), true);
    }
}
*/
function display_business_central_data() {
    ob_start();
    $environment = 'Sandbox';
    $company_name = 'CRONUS USA, Inc.';
    $services = get_available_services($environment);

    echo '<ul>';
    foreach ($services as $key => $label) {
        echo '<li><a href="?service=' . urlencode($key) . '">' . esc_html($label) . '</a></li>';
    }
    echo '</ul>';

    if (isset($_GET['service']) && array_key_exists($_GET['service'], $services)) {
        $service_name = sanitize_text_field($_GET['service']);

        // Handle create action
        if (isset($_GET['action']) && $_GET['action'] === 'create') {
            $new_data = [
                'Description' => 'New Item Description',
                'No' => 'NEWITEM001'
            ];
            $created_data = create_business_central_data($service_name, $company_name, $environment, $new_data);
            echo '<p>New item created: <pre>' . print_r($created_data, true) . '</pre></p>';
        }

        // Handle update action
        if (isset($_GET['action'], $_GET['etag']) && $_GET['action'] === 'update') {
            $etag = sanitize_text_field($_GET['etag']);
            $update_data = [
                'Description' => 'Updated Item Description'
            ];
            $updated_data = update_business_central_data($service_name, $company_name, $environment, $etag, $update_data);
            echo '<p>Item updated: <pre>' . print_r($updated_data, true) . '</pre></p>';
        }

        // Handle delete action
        if (isset($_GET['action'], $_GET['etag']) && $_GET['action'] === 'delete') {
            $etag = sanitize_text_field($_GET['etag']);
            $deleted = delete_business_central_data($service_name, $company_name, $environment, $etag);
            if ($deleted) {
                echo '<p>Item deleted successfully.</p>';
            } else {
                echo '<p>Failed to delete item.</p>';
            }
        }

        $data = get_business_central_data($service_name, $company_name, $environment);

        if ($data && isset($data['value']) && is_array($data['value'])) {
            echo '<h3>Data for ' . esc_html($services[$service_name]) . '</h3>';
            echo '<table border="1">';
            echo '<tr><th>ID</th><th>Data</th><th>Actions</th></tr>';

            foreach ($data['value'] as $record) {
                $etag = isset($record['@odata.etag']) ? str_replace('\"', '"', $record['@odata.etag']) : null;

                echo '<tr>';
                echo '<td><pre>' . print_r($record, true) . '</pre></td>';
                echo '<td>';
                
                if ($etag) {
                    echo '<a href="?service=' . urlencode($service_name) . '&action=update&etag=' . urlencode($etag) . '">Update</a> | ';
                    echo '<a href="?service=' . urlencode($service_name) . '&action=delete&etag=' . urlencode($etag) . '">Delete</a>';
                } else {
                    echo 'No etag found';
                }

                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '<a href="?service=' . urlencode($service_name) . '&action=create">Create</a>';

        } else {
            echo '<p>No data available or failed to retrieve data.</p>';
        }
    } else {
        echo '<p>Please select a service to view its data.</p>';
    }

    return ob_get_clean();
}
add_shortcode('business_central_data', 'display_business_central_data');
/*
function display_business_central_data() {
    ob_start();
    $environment = 'Sandbox';
    $company_name = 'CRONUS USA, Inc.';
    $services = get_available_services($environment);

    echo '<ul>';
    foreach ($services as $key => $label) {
        echo '<li><a href="?service=' . urlencode($key) . '">' . esc_html($label) . '</a></li>';
    }
    echo '</ul>';

    if (isset($_GET['service']) && array_key_exists($_GET['service'], $services)) {
        $service_name = sanitize_text_field($_GET['service']);

        // Handle create action
        if (isset($_GET['action']) && $_GET['action'] === 'create') {
            $new_data = [
                // Add your data here; example:
                'Description' => 'New Item Description',
                'No' => 'NEWITEM001'
            ];
            $created_data = create_business_central_data($service_name, $company_name, $environment, $new_data);
            echo '<p>New item created: <pre>' . print_r($created_data, true) . '</pre></p>';
        }

        // Handle update action
        if (isset($_GET['action'], $_GET['id'], $_GET['etag']) && $_GET['action'] === 'update') {
            $id = sanitize_text_field($_GET['id']);
            $etag = sanitize_text_field($_GET['etag']);
            $update_data = [
                // Define fields to update; example:
                'Description' => 'Updated Item Description'
            ];
            $updated_data = update_business_central_data($service_name, $company_name, $environment, $etag, $update_data);
            echo '<p>Item updated: <pre>' . print_r($updated_data, true) . '</pre></p>';
        }

        // Handle delete action
        if (isset($_GET['action'], $_GET['id'], $_GET['etag']) && $_GET['action'] === 'delete') {
            $id = sanitize_text_field($_GET['id']);
            $etag = sanitize_text_field($_GET['etag']);
            $deleted = delete_business_central_data($service_name, $company_name, $environment, $etag);
            if ($deleted) {
                echo '<p>Item deleted successfully.</p>';
            } else {
                echo '<p>Failed to delete item.</p>';
            }
        }

        $data = get_business_central_data($service_name, $company_name, $environment);

        if ($data && isset($data['value']) && is_array($data['value'])) {
            echo '<h3>Data for ' . esc_html($services[$service_name]) . '</h3>';
            echo '<table border="1">';
            echo '<tr><th>ID</th><th>Data</th><th>Actions</th></tr>';

            foreach ($data['value'] as $record) {
                // Try to find the ID field in the record
                $record_id = isset($record['ID']) ? $record['ID'] : (isset($record['No']) ? $record['No'] : (isset($record['id']) ? $record['id'] : null));
                $etag = isset($record['@odata.etag']) ? $record['@odata.etag'] : null;

                echo '<tr>';
                echo '<td>' . esc_html($record_id) . '</td>';
                echo '<td><pre>' . print_r($record, true) . '</pre></td>';
                echo '<td>';
                
                if ($record_id && $etag) {
                    // Encode etag for URL and add CRUD links with both ID and ETag for Update and Delete
                    $encoded_etag = urlencode($etag);
                    echo '<a href="?service=' . urlencode($service_name) . '&action=update&id=' . urlencode($record_id) . '&etag=' . $encoded_etag . '">Update</a> | ';
                    echo '<a href="?service=' . urlencode($service_name) . '&action=delete&id=' . urlencode($record_id) . '&etag=' . $encoded_etag . '">Delete</a>';
                } else {
                    echo 'No ID or ETag found';
                }

                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '<a href="?service=' . urlencode($service_name) . '&action=create">Create</a>';

        } else {
            echo '<p>No data available or failed to retrieve data.</p>';
        }
    } else {
        echo '<p>Please select a service to view its data.</p>';
    }

    return ob_get_clean();
}
add_shortcode('business_central_data', 'display_business_central_data');
/*
function display_business_central_data() {
    ob_start();
    $environment = 'Sandbox';
    $company_name = 'CRONUS USA, Inc.';
    $services = get_available_services($environment);

    echo '<ul>';
    foreach ($services as $key => $label) {
        echo '<li><a href="?service=' . urlencode($key) . '">' . esc_html($label) . '</a></li>';
    }
    echo '</ul>';

    if (isset($_GET['service']) && array_key_exists($_GET['service'], $services)) {
        $service_name = sanitize_text_field($_GET['service']);

        // Handle create action
        if (isset($_GET['action']) && $_GET['action'] === 'create') {
            $new_data = [
                // Add your data here; example:
                'Description' => 'New Item Description',
                'No' => 'NEWITEM001'
            ];
            $created_data = create_business_central_data($service_name, $company_name, $environment, $new_data);
            echo '<p>New item created: <pre>' . print_r($created_data, true) . '</pre></p>';
        }

        // Handle update action
        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'update') {
            $id = sanitize_text_field($_GET['id']);
            $update_data = [
                // Define fields to update; example:
                'Description' => 'Updated Item Description'
            ];
            $updated_data = update_business_central_data($service_name, $company_name, $environment, $id, $update_data);
            echo '<p>Item updated: <pre>' . print_r($updated_data, true) . '</pre></p>';
        }

        // Handle delete action
        if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
            $id = sanitize_text_field($_GET['id']);
            $deleted = delete_business_central_data($service_name, $company_name, $environment, $id);
            if ($deleted) {
                echo '<p>Item deleted successfully.</p>';
            } else {
                echo '<p>Failed to delete item.</p>';
            }
        }

        $data = get_business_central_data($service_name, $company_name, $environment);

        if ($data && isset($data['value']) && is_array($data['value'])) {
            echo '<h3>Data for ' . esc_html($services[$service_name]) . '</h3>';
            echo '<table border="1">';
            echo '<tr><th>ID</th><th>Data</th><th>Actions</th></tr>';

            foreach ($data['value'] as $record) {
                // Try to find the ID field in the record
                $record_id = isset($record['ID']) ? $record['ID'] : (isset($record['No']) ? $record['No'] : (isset($record['id']) ? $record['id'] : null));

                echo '<tr>';
                echo '<td>' . esc_html($record_id) . '</td>';
                echo '<td><pre>' . print_r($record, true) . '</pre></td>';
                echo '<td>';
                
                if ($record_id) {
                    // CRUD Links with actual ID for Update and Delete
                    echo '<a href="?service=' . urlencode($service_name) . '&action=update&id=' . urlencode($record_id) . '">Update</a> | ';
                    echo '<a href="?service=' . urlencode($service_name) . '&action=delete&id=' . urlencode($record_id) . '">Delete</a>';
                } else {
                    echo 'No ID found';
                }

                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
            echo '<a href="?service=' . urlencode($service_name) . '&action=create">Create</a>';

        } else {
            echo '<p>No data available or failed to retrieve data.</p>';
        }
    } else {
        echo '<p>Please select a service to view its data.</p>';
    }

    return ob_get_clean();
}
add_shortcode('business_central_data', 'display_business_central_data');
*/