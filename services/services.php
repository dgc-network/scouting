<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//require_once plugin_dir_path( __FILE__ ) . 'iot-messages.php';
//require_once plugin_dir_path( __FILE__ ) . 'business-central.php';
require_once plugin_dir_path( __FILE__ ) . 'line-bot-api.php';
require_once plugin_dir_path( __FILE__ ) . 'line-login-api.php';
require_once plugin_dir_path( __FILE__ ) . 'open-ai-api.php';

function web_service_menu() {
    add_options_page(
        'Web Service Settings',
        'Web Service',
        'manage_options',
        'web-service-settings',
        'web_service_settings_page'
    );
}
add_action('admin_menu', 'web_service_menu');

function web_service_settings_page() {
    ?>
    <div class="wrap">
        <h2>Web Service Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('web-service-settings');
            do_settings_sections('web-service-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Operation section
function operation_register_settings() {
    // Register Operation section
    add_settings_section(
        'operation-section-settings',
        'Operation Settings',
        'operation_section_settings_callback',
        'web-service-settings'
    );

    // Register fields for Operation section
    add_settings_field(
        'default_video_url',
        'Default video URL',
        'default_video_url_callback',
        'web-service-settings',
        'operation-section-settings'
    );
    register_setting('web-service-settings', 'default_video_url');
    
    add_settings_field(
        'default_image_url',
        'Default image URL',
        'default_image_url_callback',
        'web-service-settings',
        'operation-section-settings'
    );
    register_setting('web-service-settings', 'default_image_url');
    
    add_settings_field(
        'operation_row_counts',
        'Row counts',
        'operation_row_counts_callback',
        'web-service-settings',
        'operation-section-settings'
    );
    register_setting('web-service-settings', 'operation_row_counts');
    
    add_settings_field(
        'operation_fee_rate',
        'Operation fee rate',
        'operation_fee_rate_callback',
        'web-service-settings',
        'operation-section-settings'
    );
    register_setting('web-service-settings', 'operation_fee_rate');
    
    add_settings_field(
        'operation_wallet_address',
        'Wallet address',
        'operation_wallet_address_callback',
        'web-service-settings',
        'operation-section-settings'
    );
    register_setting('web-service-settings', 'operation_wallet_address');
    
}
add_action('admin_init', 'operation_register_settings');

function operation_section_settings_callback() {
    echo '<p>Settings for operation.</p>';
}

function default_video_url_callback() {
    $value = get_option('default_video_url');
    echo '<input type="text" name="default_video_url" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function default_image_url_callback() {
    $value = get_option('default_image_url');
    echo '<input type="text" name="default_image_url" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function operation_row_counts_callback() {
    $value = get_option('operation_row_counts');
    echo '<input type="number" name="operation_row_counts" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function operation_fee_rate_callback() {
    $value = get_option('operation_fee_rate');
    echo '<input type="number" name="operation_fee_rate" style="width:100%;" value="' . esc_attr($value) . '" />';
}

function operation_wallet_address_callback() {
    $value = get_option('operation_wallet_address');
    echo '<input type="text" name="operation_wallet_address" style="width:100%;" value="' . esc_attr($value) . '" />';
}

