<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!class_exists('iot_messages')) {
    class iot_messages {

        public function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_iot_message_scripts' ) );
            add_action( 'init', array( $this, 'register_iot_message_meta' ) );
            add_action( 'init', array( $this, 'register_iot_message_post_type' ) );

            add_filter('cron_schedules', array( $this, 'custom_cron_schedules'));
            if (!wp_next_scheduled('five_minutes_action_process_event')) {
                wp_schedule_event(time(), 'every_five_minutes', 'five_minutes_action_process_event');
            }
            add_action('five_minutes_action_process_event', array( $this, 'update_iot_message_meta_data'));
            register_deactivation_hook(__FILE__, array( $this, 'custom_cron_deactivation'));
        }

        function enqueue_iot_message_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);
            wp_enqueue_script('leaflet-script', "https://unpkg.com/leaflet/dist/leaflet.js");
            wp_enqueue_style('leaflet-style', "https://unpkg.com/leaflet@1.7.1/dist/leaflet.css");

            wp_enqueue_script('iot-messages', plugins_url('iot-messages.js', __FILE__), array('jquery'), time());
            wp_localize_script('iot-messages', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('iot-messages-nonce'), // Generate nonce
            ));                
        }        

        // iot-message operation
        function register_iot_message_post_type() {
            register_post_type('iot-message', array(
                'labels' => array(
                    'name' => 'IoT Messages',
                    'singular_name' => 'IoT Message',
                ),
                'public' => true,
                'show_in_rest' => true,
                'supports' => array('title', 'editor', 'custom-fields'),
                'capability_type' => 'post',
            ));
        }
        
        function register_iot_message_meta() {
            register_post_meta('iot-message', 'deviceID', array(
                'show_in_rest' => true,
                'single' => true,
                'type' => 'string',
            ));
            register_post_meta('iot-message', 'temperature', array(
                'show_in_rest' => true,
                'single' => true,
                'type' => 'number',
            ));
            register_post_meta('iot-message', 'humidity', array(
                'show_in_rest' => true,
                'single' => true,
                'type' => 'number',
            ));
        }

        function update_iot_message_meta_data() {
            // Retrieve all 'iot-message' posts from the last 5 minutes that haven't been processed
            $args = array(
                'post_type' => 'iot-message',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'processed',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
                'date_query' => array(
                    array(
                        'after' => '5 minutes ago',
                        'inclusive' => true,
                    ),
                ),
            );
            $iot_query = new WP_Query($args);

            if ($iot_query->have_posts()) {
                // Collect all instrument codes to minimize database queries
                $instrument_codes = array();
                while ($iot_query->have_posts()) {
                    $iot_query->the_post();
                    $instrument_codes[get_the_ID()] = get_post_meta(get_the_ID(), 'deviceID', true);
                }

                // Query 'instrument-card' posts that match any of the collected instrument codes
                $inner_args = array(
                    'post_type' => 'instrument-card',
                    'meta_query' => array(
                        array(
                            'key'     => 'instrument_code',
                            'value'   => array_values($instrument_codes),
                            'compare' => 'IN',
                        )
                    ),
                    'posts_per_page' => -1,
                );
                $inner_query = new WP_Query($inner_args);

                if ($inner_query->have_posts()) {
                    while ($inner_query->have_posts()) {
                        $inner_query->the_post();
                        $instrument_code = get_post_meta(get_the_ID(), 'instrument_code', true);
                        // Find the corresponding iot-message post
                        $iot_post_id = array_search($instrument_code, $instrument_codes);
                        if ($iot_post_id !== false) {
                            $temperature = get_post_meta($iot_post_id, 'temperature', true);
                            $humidity = get_post_meta($iot_post_id, 'humidity', true);
                            // Update 'temperature' and 'humidity' metadata
                            if ($temperature !== '') {
                                update_post_meta(get_the_ID(), 'temperature', $temperature);
                                $this->create_exception_notification_events(get_the_ID(), 'temperature', $temperature);
                            }
                            if ($humidity !== '') {
                                update_post_meta(get_the_ID(), 'humidity', $humidity);
                                $this->create_exception_notification_events(get_the_ID(), 'humidity', $humidity);
                            }
                            // Mark the 'iot-message' post as processed
                            update_post_meta($iot_post_id, 'processed', 1);
                        }
                    }
                    wp_reset_postdata();
                }
                wp_reset_postdata();
            }
        }

        function create_exception_notification_events($instrument_id=false, $iot_sensor=false, $sensor_value=false) {
            $instrument_code = get_post_meta($instrument_id, 'instrument_code', true);
            $documents_class = new display_documents();
            $query = $documents_class->get_doc_reports_by_doc_field('_instrument', $instrument_id);

            if ($query->have_posts()) {
                foreach ($query->posts as $report_id) {
                    $max_value = get_post_meta($report_id, '_max_value', true);
                    $min_value = get_post_meta($report_id, '_min_value', true);
                    $employee_ids = get_post_meta($report_id, '_employees', true);

                    // Prepare the notification message
                    $five_minutes_ago = time() - (5 * 60);
                    $five_minutes_ago_formatted = wp_date(get_option('date_format'), $five_minutes_ago) . ' ' . wp_date(get_option('time_format'), $five_minutes_ago);

                    foreach ($employee_ids as $employee_id) {
                        if ($max_value && $sensor_value>$max_value) {
                            if ($iot_sensor=='temperature') {
                                $text_message = '#'.$instrument_code.' '.get_the_title($instrument_id).'在'.$five_minutes_ago_formatted.'的溫度是'.$sensor_value.'°C，已經大於設定的'.$max_value.'°C了。';
                            }
                            if ($iot_sensor=='humidity') {
                                $text_message = '#'.$instrument_code.' '.get_the_title($instrument_id).'在'.$five_minutes_ago_formatted.'的濕度是'.$sensor_value.'%，已經大於設定的'.$max_value.'%了。';
                            }
                        }
                        if ($min_value && $sensor_value<$min_value) {
                            if ($iot_sensor=='temperature') {
                                $text_message = '#'.$instrument_code.' '.get_the_title($instrument_id).'在'.$five_minutes_ago_formatted.'的溫度是'.$sensor_value.'°C，已經小於設定的'.$min_value.'°C了。';
                            }
                            if ($iot_sensor=='humidity') {
                                $text_message = '#'.$instrument_code.' '.get_the_title($instrument_id).'在'.$five_minutes_ago_formatted.'的濕度是'.$sensor_value.'%，已經小於設定的'.$min_value.'%了。';
                            }
                        }
                        $this->prepare_exception_notification_event($instrument_id, $employee_id, $text_message);
                    }
                }
                return $query->posts; // Return the array of post IDs
            }
        }

        function prepare_exception_notification_event($instrument_id=false, $user_id=false, $text_message=false) {
            // Check if a notification has been sent today
            $last_notification_time = get_user_meta($user_id, 'last_notification_time_' . $instrument_id, true);
            $today = wp_date('Y-m-d');

            if ($last_notification_time && wp_date('Y-m-d', $last_notification_time) === $today) {
                // Notification already sent today, do not send again
                return;
            }

            // Parameters to pass to the notification function
            $params = [
                'user_id' => $user_id,
                'text_message' => $text_message,
            ];

            // Schedule the event to run after 5 minutes (300 seconds)
            wp_schedule_single_event(time() + 300, 'send_delayed_notification', [$params]);

            // Update the last notification time
            update_user_meta($user_id, 'last_notification_time_' . $instrument_id, time());
        }

        function send_delayed_notification_handler($params) {
            $user_id = $params['user_id'];
            $text_message = $params['text_message'];

            $user_data = get_userdata($user_id);
            $line_user_id = get_user_meta($user_id, 'line_user_id', true);

            // Prepare the flex message
            $flexMessage = set_flex_message([
                'display_name' => $user_data->display_name,
                'link_uri' => home_url() . '/display-profiles/?_id=' . $user_id,
                'text_message' => $text_message,
            ]);

            // Send the message via the LINE API
            $line_bot_api = new line_bot_api();
            $line_bot_api->pushMessage([
                'to' => $line_user_id,
                'messages' => [$flexMessage],
            ]);
        }

        function custom_cron_schedules($schedules) {
            if (!isset($schedules['every_five_minutes'])) {
                $schedules['every_five_minutes'] = array(
                    'interval' => 300, // 300 seconds = 5 minutes
                    'display' => __('Every Five Minutes')
                );
            }
            return $schedules;
        }

        function custom_cron_deactivation() {
            $timestamp = wp_next_scheduled('five_minutes_action_process_event');
            if ($timestamp) {
                wp_unschedule_event($timestamp, 'five_minutes_action_process_event');
            }
        }        

        // iot message
        function display_iot_message_list() {
            ob_start();
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();
            if (current_user_can('administrator')) $is_site_admin = true;
            $todo_class = new to_do_list();
            $current_user_id = get_current_user_id();
            $current_user = get_userdata($current_user_id);
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            ?>
            <div class="ui-widget" id="result-container">
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( 'IoT Messages', 'your-text-domain' );?></h2>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $todo_class->display_select_todo('iot-message');?></div>
                <div style="text-align: right"></div>                        
            </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Time', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Device', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Sensor', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Vlue', 'your-text-domain' );?></th>
                        <th></th>
                        <th></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_iot_message_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages

                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            // Get post creation time
                            $post_time = get_post_time('Y-m-d H:i:s', false, get_the_ID());
                            $topic = get_the_title();
                            $message = get_the_content();
                            $deviceID = get_post_meta(get_the_ID(), 'deviceID', true);
                            $temperature = get_post_meta(get_the_ID(), 'temperature', true);
                            $humidity = get_post_meta(get_the_ID(), 'humidity', true);
                            $latitude = get_post_meta(get_the_ID(), 'latitude', true);
                            $longitude = get_post_meta(get_the_ID(), 'longitude', true);
                            ?>
                            <tr id="edit-iot-message-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html($post_time);?></td>
                                <td style="text-align:center;"><?php echo esc_html($deviceID);?></td>
                                <?php if ($temperature) {?>
                                    <td style="text-align:center;"><?php echo __( 'Temperature', 'your-text-domain' );?></td>
                                    <td style="text-align:center;"><?php echo esc_html($temperature);?></td>
                                <?php }?>
                                <?php if ($humidity) {?>
                                    <td style="text-align:center;"><?php echo __( 'Humidity(%)', 'your-text-domain' );?></td>
                                    <td style="text-align:center;"><?php echo esc_html($humidity);?><span style="font-size:small">%</span></td>
                                <?php }?>
                                <?php if ($latitude) {?>
                                    <td style="text-align:center;"><?php echo __( 'Latitude', 'your-text-domain' );?></td>
                                    <td style="text-align:center;"><?php echo esc_html($latitude);?></td>
                                <?php }?>
                                <?php if ($longitude) {?>
                                    <td style="text-align:center;"><?php echo __( 'Longitude', 'your-text-domain' );?></td>
                                    <td style="text-align:center;"><?php echo esc_html($longitude);?></td>
                                <?php }?>
                            </tr>
                            <?php 
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>
            </fieldset>
            </div>
            <?php
            return ob_get_clean();
        }
        
        function retrieve_iot_message_data($paged = 1) {
            $args = array(
                'post_type'      => 'iot-message',
                'posts_per_page' => get_option('operation_row_counts'), // Show 20 records per page
                'orderby'        => 'date',
                'order'          => 'DESC',
                'paged'          => $paged,
            );
            $query = new WP_Query($args);
            return $query;
        }
    }
    $iot_messages = new iot_messages();
}
