<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('erp_cards')) {
    class erp_cards {
        // Class constructor
        public function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_erp_cards_scripts' ) );
            add_shortcode('business_central_customers', array( $this, 'display_customer_card_list'));
            //add_action( 'init', array( $this, 'register_customer_card_post_type' ) );
            //add_action( 'init', array( $this, 'register_vendor_card_post_type' ) );
            //add_action( 'init', array( $this, 'register_product_card_post_type' ) );
            //add_action( 'init', array( $this, 'register_equipment_card_post_type' ) );
            //add_action( 'init', array( $this, 'register_instrument_card_post_type' ) );
            //add_action( 'init', array( $this, 'register_department_card_post_type' ) );

            add_action( 'wp_ajax_get_customer_card_dialog_data', array( $this, 'get_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_customer_card_dialog_data', array( $this, 'get_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_set_customer_card_dialog_data', array( $this, 'set_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_customer_card_dialog_data', array( $this, 'set_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_del_customer_card_dialog_data', array( $this, 'del_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_customer_card_dialog_data', array( $this, 'del_customer_card_dialog_data' ) );

            add_action( 'wp_ajax_get_vendor_card_dialog_data', array( $this, 'get_vendor_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_vendor_card_dialog_data', array( $this, 'get_vendor_card_dialog_data' ) );
            add_action( 'wp_ajax_set_vendor_card_dialog_data', array( $this, 'set_vendor_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_vendor_card_dialog_data', array( $this, 'set_vendor_card_dialog_data' ) );
            add_action( 'wp_ajax_del_vendor_card_dialog_data', array( $this, 'del_vendor_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_vendor_card_dialog_data', array( $this, 'del_vendor_card_dialog_data' ) );

            add_action( 'wp_ajax_get_product_card_dialog_data', array( $this, 'get_product_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_product_card_dialog_data', array( $this, 'get_product_card_dialog_data' ) );
            add_action( 'wp_ajax_set_product_card_dialog_data', array( $this, 'set_product_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_product_card_dialog_data', array( $this, 'set_product_card_dialog_data' ) );
            add_action( 'wp_ajax_del_product_card_dialog_data', array( $this, 'del_product_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_product_card_dialog_data', array( $this, 'del_product_card_dialog_data' ) );

            add_action( 'wp_ajax_get_equipment_card_dialog_data', array( $this, 'get_equipment_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_equipment_card_dialog_data', array( $this, 'get_equipment_card_dialog_data' ) );
            add_action( 'wp_ajax_set_equipment_card_dialog_data', array( $this, 'set_equipment_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_equipment_card_dialog_data', array( $this, 'set_equipment_card_dialog_data' ) );
            add_action( 'wp_ajax_del_equipment_card_dialog_data', array( $this, 'del_equipment_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_equipment_card_dialog_data', array( $this, 'del_equipment_card_dialog_data' ) );

            add_action( 'wp_ajax_get_instrument_card_dialog_data', array( $this, 'get_instrument_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_instrument_card_dialog_data', array( $this, 'get_instrument_card_dialog_data' ) );
            add_action( 'wp_ajax_set_instrument_card_dialog_data', array( $this, 'set_instrument_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_instrument_card_dialog_data', array( $this, 'set_instrument_card_dialog_data' ) );
            add_action( 'wp_ajax_del_instrument_card_dialog_data', array( $this, 'del_instrument_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_instrument_card_dialog_data', array( $this, 'del_instrument_card_dialog_data' ) );

            add_action( 'wp_ajax_get_department_card_dialog_data', array( $this, 'get_department_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_department_card_dialog_data', array( $this, 'get_department_card_dialog_data' ) );
            add_action( 'wp_ajax_set_department_card_dialog_data', array( $this, 'set_department_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_department_card_dialog_data', array( $this, 'set_department_card_dialog_data' ) );
            add_action( 'wp_ajax_del_department_card_dialog_data', array( $this, 'del_department_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_department_card_dialog_data', array( $this, 'del_department_card_dialog_data' ) );

            add_action( 'wp_ajax_get_department_user_list_data', array( $this, 'get_department_user_list_data' ) );
            add_action( 'wp_ajax_nopriv_get_department_user_list_data', array( $this, 'get_department_user_list_data' ) );
            add_action( 'wp_ajax_add_department_user_dialog_data', array( $this, 'add_department_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_add_department_user_dialog_data', array( $this, 'add_department_user_dialog_data' ) );
            add_action( 'wp_ajax_del_department_user_dialog_data', array( $this, 'del_department_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_department_user_dialog_data', array( $this, 'del_department_user_dialog_data' ) );
        }

        function enqueue_erp_cards_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);
            wp_enqueue_style('wp-enqueue-css', plugins_url('/assets/css/wp-enqueue.css', __DIR__), '', time());

            wp_enqueue_script('erp-cards', plugins_url('js/erp-cards.js', __FILE__), array('jquery'), time());
            wp_localize_script('erp-cards', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('erp-cards-nonce'), // Generate nonce
            ));                
        }

        // customer-card
        function display_customer_card_list() {
            ob_start();
            $service_name = 'ProjectCards';
            $company_name = 'CRONUS USA, Inc.';
            $environment = 'Sandbox';
            $data = get_business_central_data($service_name, $company_name, $environment);

            if ($data && isset($data['value']) && is_array($data['value'])) {
                ?>
                <?php //echo display_iso_helper_logo(); ?>
                <h2 style="display:inline;"><?php echo __( '客戶列表', 'textdomain' ); ?></h2>
    
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php //$profiles_class->display_select_profile('customer-card'); ?></div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-customer" style="display:inline" placeholder="Search..." />
                    </div>
                </div>
    
                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <tr>
                                <th><?php echo __( 'Number', 'textdomain' ); ?></th>
                                <th><?php echo __( 'Title', 'textdomain' ); ?></th>
                                <th><?php echo __( 'Phone', 'textdomain' ); ?></th>
                                <th><?php echo __( 'Address', 'textdomain' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                    foreach ($data['value'] as $record) {
                        $etag = isset($record['@odata.etag']) ? str_replace('\"', '"', $record['@odata.etag']) : null;
                        $record_id = isset($record['No']) ? $record['No'] : null;
                        $No = isset($record['No']) ? $record['No'] : null;
                        $Description = isset($record['Description']) ? $record['Description'] : null;
                        $Sell_to_Customer_Name = isset($record['Sell_to_Customer_Name']) ? $record['Sell_to_Customer_Name'] : null;
                        $Sell_to_Address = isset($record['Sell_to_Address']) ? $record['Sell_to_Address'] : null;
                        ?>
                        <tr id="edit-customer-card-<?php echo esc_attr($record_id);?>">
                            <td style="text-align:center;"><?php echo esc_html($No);?></td>
                            <td><?php echo $Description;?></td>
                            <td style="text-align:center;"><?php echo esc_html($Sell_to_Customer_Name);?></td>
                            <td><?php echo esc_html($Sell_to_Address);; ?></td>
                        </tr>
                        <?php
/*
                        echo '<tr>';
                        echo '<td>';
                        
                        if ($etag) {
                            echo '<a href="?service=' . urlencode($service_name) . '&action=update&etag=' . urlencode($etag) . '">Update</a> | ';
                            echo '<a href="?service=' . urlencode($service_name) . '&action=delete&etag=' . urlencode($etag) . '">Delete</a>';
                        } else {
                            echo 'No etag found';
                        }
        
                        echo '</td>';
                        echo '<td><pre>' . print_r($record, true) . '</pre></td>';
                        echo '</tr>';
*/
                    }
                        $paged = max(1, get_query_var('paged')); // Get the current page number
                        ?>
                        </tbody>
                    </table>
                    <?php //if (is_site_admin()) {?>
                        <div id="new-customer-card" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                    <?php //}?>
                    <div class="pagination">
                        <?php
                        // Display pagination links
                        if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                        echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                        if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                        ?>
                    </div>
                </fieldset>
                <div id="customer-card-dialog" title="Customer dialog"></div>
                <?php        
            }
            return ob_get_clean();
        }

        function get_customer_card_dialog_data() {
            $record_id = sanitize_text_field($_POST['_record_id']);
            $response = array('html_contain' => $this->display_customer_card_dialog($record_id));
            wp_send_json($response);
        }

        function display_customer_card_dialog($record_id = false) {
            ob_start();
            $service_name = 'ProjectCards';
            $company_name = 'CRONUS USA, Inc.';
            $environment = 'Sandbox';
            $data = get_business_central_data($service_name, $company_name, $environment, $record_id);

            //if ($data && isset($data['value']) && is_array($data['value'])) {
                //$unified_number = get_post_meta($customer_id, 'unified_number', true);
                ?>
                <fieldset>
                    <input type="hidden" id="record-id" value="<?php echo esc_attr($data['No']);?>" />
                    <label for="customer-code"><?php echo __( 'Number: ', 'textdomain' );?></label>
                    <input type="text" id="customer-code" value="<?php echo esc_attr($data['No']);?>" class="text ui-widget-content ui-corner-all" />
                    <label for="customer-title"><?php echo __( 'Title: ', 'textdomain' );?></label>
                    <input type="text" id="customer-title" value="<?php echo esc_attr($data['Description']);?>" class="text ui-widget-content ui-corner-all" />
                    <label for="company-phone"><?php echo __( 'Phone: ', 'textdomain' );?></label>
                    <input type="text" id="company-phone" value="<?php echo esc_attr($data['Description']);?>" class="text ui-widget-content ui-corner-all" />
                    <label for="company-address"><?php echo __( 'Address: ', 'textdomain' );?></label>
                    <textarea id="company-address" rows="2" style="width:100%;"><?php echo esc_html($data['Description']); ?></textarea>
                    <label for="unified-number"><?php echo __( '統一編號: ', 'textdomain' );?></label>
                    <input type="text" id="unified-number" value="<?php echo esc_attr($data['Description']);?>" class="text ui-widget-content ui-corner-all" />
                </fieldset>
                <?php
    
            //}
            return ob_get_clean();

            // Get the current user's site ID
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            // Retrieve the site_customer_data meta field
            $site_customer_data = get_post_meta($customer_id, 'site_customer_data', true);
            // Check if site_customer_data is an array and contains the site_id key
            if (is_array($site_customer_data) && isset($site_customer_data[$site_id])) {
                $customer_code = $site_customer_data[$site_id];
            } else {
                // Handle the case where customer_code doesn't exist or site_customer_data is not an array
                $customer_code = ''; // Default value if the customer code is not found
            }

            // Retrieve other post data and meta fields
            $customer_title = get_the_title($customer_id);
            $customer_content = get_post_field('post_content', $customer_id);
            $company_phone = get_post_meta($customer_id, 'company_phone', true);
            $company_address = get_post_meta($customer_id, 'company_address', true);
            $unified_number = get_post_meta($customer_id, 'unified_number', true);
            ?>
            <fieldset>
                <input type="hidden" id="customer-id" value="<?php echo esc_attr($customer_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="customer-code"><?php echo __( 'Number: ', 'textdomain' );?></label>
                <input type="text" id="customer-code" value="<?php echo esc_attr($customer_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="customer-title"><?php echo __( 'Title: ', 'textdomain' );?></label>
                <input type="text" id="customer-title" value="<?php echo esc_attr($customer_title);?>" class="text ui-widget-content ui-corner-all" />
                <?php
                // transaction data vs card key/value
                $key_value_pair = array(
                    '_customer'   => $customer_id,
                );
                $documents_class = new display_documents();
                $documents_class->get_transactions_by_key_value_pair($key_value_pair);
                ?>
                <label for="company-phone"><?php echo __( 'Phone: ', 'textdomain' );?></label>
                <input type="text" id="company-phone" value="<?php echo esc_attr($company_phone);?>" class="text ui-widget-content ui-corner-all" />
                <label for="company-address"><?php echo __( 'Address: ', 'textdomain' );?></label>
                <textarea id="company-address" rows="2" style="width:100%;"><?php echo esc_html($company_address); ?></textarea>
                <label for="unified-number"><?php echo __( '統一編號: ', 'textdomain' );?></label>
                <input type="text" id="unified-number" value="<?php echo esc_attr($unified_number);?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function set_customer_card_dialog_data() {
            if (isset($_POST['_customer_id'])) {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $customer_id = sanitize_text_field($_POST['_customer_id']);
                $customer_code = sanitize_text_field($_POST['_customer_code']);
                $company_phone = sanitize_text_field($_POST['_company_phone']);
                $company_address = $_POST['_company_address'];
                $unified_number = sanitize_text_field($_POST['_unified_number']);
        
                $data = array(
                    'ID'           => $customer_id,
                    'post_title'   => sanitize_text_field($_POST['_customer_title']),
                );
                wp_update_post($data);
        
                // Retrieve the existing site_customer_data
                $site_customer_data = get_post_meta($customer_id, 'site_customer_data', true);
                
                // Check if site_customer_data is an array and the site_id key exists
                if (!is_array($site_customer_data)) {
                    $site_customer_data = array();
                }
                
                // Update or add the site_id key with the customer_code value
                $site_customer_data[$site_id] = $customer_code;

                // Update the meta field with the modified array
                update_post_meta($customer_id, 'site_customer_data', $site_customer_data);
                update_post_meta($customer_id, 'company_phone', $company_phone);
                update_post_meta($customer_id, 'company_address', $company_address);
                update_post_meta($customer_id, 'unified_number', $unified_number);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $customer_code = time();

                $args = array(
                    'post_type'   => 'site-profile',
                    'post_status' => 'publish', // Only look for published pages
                    'title'       => 'iso-helper.com',
                    'numberposts' => 1,         // Limit the number of results to one
                );
                $posts = get_posts($args); // get_posts returns an array
                $post_content = get_post_field('post_content', $posts[0]->ID);

                $post_content = get_post_field('post_content', $posts[0]->ID);
    
                $new_post = array(
                    'post_title'    => 'New customer',
                    'post_content'  => $post_content,
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'site-profile',
                );    
                $post_id = wp_insert_post($new_post);
                
                // Initialize the site_customer_data array with the site_id and customer_code
                $site_customer_data = array(
                    $site_id => $customer_code,
                );
                // Store the array as a serialized meta value
                update_post_meta($post_id, 'site_customer_data', $site_customer_data);

                // Retrieve the existing site_customer_data
                $site_vendor_data = get_post_meta($post_id, 'site_vendor_data', true);
                // Check if site_customer_data is an array and the site_id key exists
                if (!is_array($site_vendor_data)) {
                    $site_vendor_data = array();
                }
                // Update or add the site_id key with the customer_code value
                $site_vendor_data[$post_id] = $customer_code;
                // Store the array as a serialized meta value
                update_post_meta($site_id, 'site_vendor_data', $site_vendor_data);
            }
        
            $response = array('html_contain' => $this->display_customer_card_list());
            wp_send_json($response);
        }

        function del_customer_card_dialog_data() {
            $record_id = sanitize_text_field($_POST['_record_id']);
            $service_name = 'ProjectCards';
            $company_name = 'CRONUS USA, Inc.';
            $environment = 'Sandbox';
            $data = get_business_central_data($service_name, $company_name, $environment, $record_id);
            // Extract the etag
            $etag = $data['@odata.etag'] ?? null;
            $deleted = delete_business_central_data($service_name, $company_name, $environment, $etag);
            if ($deleted) {
                echo '<p>Item deleted successfully.</p>';
            } else {
                echo '<p>Failed to delete item.</p>';
            }
            $response = array('html_contain' => $this->display_customer_card_list());
            wp_send_json($response);



            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            // Retrieve the current site_customer_data array
            $site_customer_data = get_post_meta($customer_id, 'site_customer_data', true);
    
            // Check if it's an array and contains the 'customer_code' key
            if (is_array($site_customer_data) && isset($site_customer_data[$site_id])) {
                // Remove the 'customer_code' key
                unset($site_customer_data[$site_id]);
                
                // Update the post meta with the modified array
                update_post_meta($customer_id, 'site_customer_data', $site_customer_data);
            }

            //wp_delete_post($_POST['_customer_id'], true);
            $response = array('html_contain' => $this->display_customer_card_list());
            wp_send_json($response);
        }

        function retrieve_customer_card_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            $args = array(
                'post_type'      => 'site-profile',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    array(
                        'key'     => 'site_customer_data',
                        'compare' => 'EXISTS',
                    ),
                ),
            );

            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }

            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['paged'] = 1;
                $args['s'] = $search_query;
            }

            $query = new WP_Query($args);

            // Check if the result is empty and the search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Remove the initial search query
                unset($args['s']);

                // Retrieve all meta keys associated with the post type 'site-profile'
                $meta_keys = get_post_type_meta_keys('site-profile');
                
                // Prepare meta query to search across all meta keys
                $meta_query_all_keys = array('relation' => 'OR');
                foreach ($meta_keys as $meta_key) {
                    $meta_query_all_keys[] = array(
                        'key'     => $meta_key,
                        'value'   => $search_query,
                        'compare' => 'LIKE',
                    );
                }
        
                // Add this meta query to the original arguments
                $args['meta_query'][] = $meta_query_all_keys;
        
                // Re-run the query with the updated arguments
                $query = new WP_Query($args);
            }

            $filtered_posts = array_filter($query->posts, function($post) use ($site_id) {
                $site_customer_data = get_post_meta($post->ID, 'site_customer_data', true);
                return isset($site_customer_data[$site_id]);
            });
        
            // Sort posts based on the value associated with site_id
            usort($filtered_posts, function($a, $b) use ($site_id) {
                $site_customer_data_a = get_post_meta($a->ID, 'site_customer_data', true);
                $site_customer_data_b = get_post_meta($b->ID, 'site_customer_data', true);
                
                // Extract values associated with site_id
                $value_a = isset($site_customer_data_a[$site_id]) ? $site_customer_data_a[$site_id] : 0;
                $value_b = isset($site_customer_data_b[$site_id]) ? $site_customer_data_b[$site_id] : 0;
                
                // Compare values for sorting
                return $value_a <=> $value_b;
            });

            // Create a new WP_Query-like object with filtered posts
            $filtered_query = new WP_Query();
            $filtered_query->posts = $filtered_posts;
            $filtered_query->post_count = count($filtered_posts);
        
            return $filtered_query;
        }

        function select_customer_card_options($selected_option=0) {
            $query = $this->retrieve_customer_card_data(0);
            $options = '<option value="">Select customer</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // vendor-card post
        function display_vendor_card_list() {
            ob_start();
            $profiles_class = new display_profiles();
            ?>
            <?php echo display_iso_helper_logo(); ?>
            <h2 style="display:inline;"><?php echo __( '廠商列表', 'textdomain' ); ?></h2>
    
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $profiles_class->display_select_profile('vendor-card'); ?></div>
                <div style="text-align:right; display:flex;">
                    <input type="text" id="search-vendor" style="display:inline" placeholder="Search..." />
                </div>
            </div>
    
            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Number', 'textdomain' ); ?></th>
                            <th><?php echo __( 'Title', 'textdomain' ); ?></th>
                            <th><?php echo __( 'Phone', 'textdomain' ); ?></th>
                            <th><?php echo __( 'Address', 'textdomain' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_vendor_card_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                    
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
    
                            $site_vendor_data = get_post_meta(get_the_ID(), 'site_vendor_data', true);
                            $company_phone = get_post_meta(get_the_ID(), 'company_phone', true);
                            $company_address = get_post_meta(get_the_ID(), 'company_address', true);
    
                            // Assuming site_vendor_data is an associative array with site_id as a key
                            $current_user_id = get_current_user_id();
                            $site_id = get_user_meta($current_user_id, 'site_id', true);
    
                            if (is_array($site_vendor_data) && isset($site_vendor_data[$site_id])) {
                                $vendor_code = $site_vendor_data[$site_id];
                            } else {
                                // Handle the case where vendor_code doesn't exist or site_vendor_data is not an array
                                $vendor_code = ''; // or any default value you prefer
                            }
    
                            ?>
                            <tr id="edit-vendor-card-<?php the_ID(); ?>">
                                <td style="text-align:center;"><?php echo esc_html($vendor_code);?></td>
                                <td><?php the_title(); ?></td>
                                <td style="text-align:center;"><?php echo esc_html($company_phone);?></td>
                                <td><?php echo esc_html($company_address); ?></td>
                            </tr>
                            <?php 
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-vendor-card" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>    
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>
    
            </fieldset>
            <div id="vendor-card-dialog" title="Vendor dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_vendor_card_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            $args = array(
                'post_type'      => 'site-profile',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    array(
                        'key'     => 'site_vendor_data',
                        'compare' => 'EXISTS',
                    ),
                ),
            );
        
            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }
        
            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['paged'] = 1;
                $args['s'] = $search_query;
            }
        
            $query = new WP_Query($args);
        
            // Check if the result is empty and the search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Remove the initial search query
                unset($args['s']);

                // Retrieve all meta keys associated with the post type 'site-profile'
                $meta_keys = get_post_type_meta_keys('site-profile');
                
                // Prepare meta query to search across all meta keys
                $meta_query_all_keys = array('relation' => 'OR');
                foreach ($meta_keys as $meta_key) {
                    $meta_query_all_keys[] = array(
                        'key'     => $meta_key,
                        'value'   => $search_query,
                        'compare' => 'LIKE',
                    );
                }
        
                // Add this meta query to the original arguments
                $args['meta_query'][] = $meta_query_all_keys;
        
                // Re-run the query with the updated arguments
                $query = new WP_Query($args);
            }

            $filtered_posts = array_filter($query->posts, function($post) use ($site_id) {
                $site_vendor_data = get_post_meta($post->ID, 'site_vendor_data', true);
                return isset($site_vendor_data[$site_id]);
            });
        
            // Sort posts based on the value associated with site_id
            usort($filtered_posts, function($a, $b) use ($site_id) {
                $site_vendor_data_a = get_post_meta($a->ID, 'site_vendor_data', true);
                $site_vendor_data_b = get_post_meta($b->ID, 'site_vendor_data', true);
                
                // Extract values associated with site_id
                $value_a = isset($site_vendor_data_a[$site_id]) ? $site_vendor_data_a[$site_id] : 0;
                $value_b = isset($site_vendor_data_b[$site_id]) ? $site_vendor_data_b[$site_id] : 0;
                
                // Compare values for sorting
                return $value_a <=> $value_b;
            });

            // Create a new WP_Query-like object with filtered posts
            $filtered_query = new WP_Query();
            $filtered_query->posts = $filtered_posts;
            $filtered_query->post_count = count($filtered_posts);
        
            return $filtered_query;
        }

        function display_vendor_card_dialog($vendor_id = false) {
            ob_start();
            // Get the current user's site ID
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            // Retrieve the site_vendor_data meta field
            $site_vendor_data = get_post_meta($vendor_id, 'site_vendor_data', true);
        
            // Check if site_vendor_data is an array and contains the site_id key
            if (is_array($site_vendor_data) && isset($site_vendor_data[$site_id])) {
                $vendor_code = $site_vendor_data[$site_id];
            } else {
                // Handle the case where vendor_code doesn't exist or site_vendor_data is not an array
                $vendor_code = ''; // Default value if the vendor code is not found
            }
        
            // Retrieve other post data and meta fields
            $vendor_title = get_the_title($vendor_id);
            $vendor_content = get_post_field('post_content', $vendor_id);
            $company_phone = get_post_meta($vendor_id, 'company_phone', true);
            $company_address = get_post_meta($vendor_id, 'company_address', true);
            $unified_number = get_post_meta($vendor_id, 'unified_number', true);
            ?>
            <fieldset>
                <input type="hidden" id="vendor-id" value="<?php echo esc_attr($vendor_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="vendor-code"><?php echo __( 'Number: ', 'textdomain' );?></label>
                <input type="text" id="vendor-code" value="<?php echo esc_attr($vendor_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="vendor-title"><?php echo __( 'Title: ', 'textdomain' );?></label>
                <input type="text" id="vendor-title" value="<?php echo esc_attr($vendor_title);?>" class="text ui-widget-content ui-corner-all" />
                <?php
                // transaction data vs card key/value
                $key_value_pair = array(
                    '_vendor'   => $vendor_id,
                );
                $documents_class = new display_documents();
                $documents_class->get_transactions_by_key_value_pair($key_value_pair);
                ?>
                <label for="company-phone"><?php echo __( 'Phone: ', 'textdomain' );?></label>
                <input type="text" id="company-phone" value="<?php echo esc_attr($company_phone);?>" class="text ui-widget-content ui-corner-all" />
                <label for="company-address"><?php echo __( 'Address: ', 'textdomain' );?></label>
                <textarea id="company-address" rows="2" style="width:100%;"><?php echo esc_html($company_address); ?></textarea>
                <label for="unified-number"><?php echo __( '統一編號: ', 'textdomain' );?></label>
                <input type="text" id="unified-number" value="<?php echo esc_attr($unified_number); ?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_vendor_card_dialog_data() {
            $vendor_id = sanitize_text_field($_POST['_vendor_id']);
            $response = array('html_contain' => $this->display_vendor_card_dialog($vendor_id));
            wp_send_json($response);
        }

        function set_vendor_card_dialog_data() {
            if (isset($_POST['_vendor_id'])) {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $vendor_id = sanitize_text_field($_POST['_vendor_id']);
                $vendor_code = sanitize_text_field($_POST['_vendor_code']);
                $company_phone = sanitize_text_field($_POST['_company_phone']);
                $company_address = $_POST['_company_address'];
                $unified_number = sanitize_text_field($_POST['_unified_number']);
        
                $data = array(
                    'ID'           => $vendor_id,
                    'post_title'   => sanitize_text_field($_POST['_vendor_title']),
                );
                wp_update_post($data);
        
                // Retrieve the existing site_vendor_data
                $site_vendor_data = get_post_meta($vendor_id, 'site_vendor_data', true);
                // Check if site_vendor_data is an array and the site_id key exists
                if (!is_array($site_vendor_data)) {
                    $site_vendor_data = array();
                }
                // Update or add the site_id key with the vendor_code value
                $site_vendor_data[$site_id] = $vendor_code;

                // Update the meta field with the modified array
                update_post_meta($vendor_id, 'site_vendor_data', $site_vendor_data);
                update_post_meta($vendor_id, 'company_phone', $company_phone);
                update_post_meta($vendor_id, 'company_address', $company_address);
                update_post_meta($vendor_id, 'unified_number', $unified_number);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $vendor_code = time();

                $args = array(
                    'post_type'   => 'site-profile',
                    'post_status' => 'publish', // Only look for published pages
                    'title'       => 'iso-helper.com',
                    'numberposts' => 1,         // Limit the number of results to one
                );
                $posts = get_posts($args); // get_posts returns an array
                $post_content = get_post_field('post_content', $posts[0]->ID);

                $new_post = array(
                    'post_title'    => 'New vendor',
                    'post_content'  => $post_content,
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'site-profile',
                );                    
                $post_id = wp_insert_post($new_post);

                // Initialize the site_vendor_data array with the site_id and vendor_code
                $site_vendor_data = array(
                    $site_id => $vendor_code,
                );
                // Store the array as a serialized meta value
                update_post_meta($post_id, 'site_vendor_data', $site_vendor_data);

                // Retrieve the existing site_vendor_data
                $site_customer_data = get_post_meta($site_id, 'site_vendor_data', true);
                // Check if site_vendor_data is an array and the site_id key exists
                if (!is_array($site_customer_data)) {
                    $site_customer_data = array();
                }
                // Update or add the site_id key with the vendor_code value
                $site_customer_data[$post_id] = $vendor_code;
                // Store the array as a serialized meta value
                update_post_meta($site_id, 'site_customer_data', $site_customer_data);
            }
        
            $response = array('html_contain' => $this->display_vendor_card_list());
            wp_send_json($response);
        }

        function del_vendor_card_dialog_data() {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            $vendor_id = sanitize_text_field($_POST['_vendor_id']);
            // Retrieve the current site_vendor_data array
            $site_vendor_data = get_post_meta($vendor_id, 'site_vendor_data', true);
    
            // Check if it's an array and contains the 'vendor_code' key
            if (is_array($site_vendor_data) && isset($site_vendor_data[$site_id])) {
                // Remove the 'vendor_code' key
                unset($site_vendor_data[$site_id]);
                
                // Update the post meta with the modified array
                update_post_meta($vendor_id, 'site_vendor_data', $site_vendor_data);
            }

            $response = array('html_contain' => $this->display_vendor_card_list());
            wp_send_json($response);
        }

        function select_vendor_card_options($selected_option=0) {
            $query = $this->retrieve_vendor_card_data(0);
            $options = '<option value="">Select vendor</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // product-card post
        function register_product_card_post_type() {
            $labels = array(
                'menu_name'     => _x('Product', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'product-card', $args );
        }

        function display_product_card_list() {
            ob_start();
            $profiles_class = new display_profiles();
            ?>
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( '產品列表', 'textdomain' );?></h2>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $profiles_class->display_select_profile('product-card');?></div>
                <div style="text-align:right; display:flex;">
                    <input type="text" id="search-product" style="display:inline" placeholder="Search..." />
                </div>
            </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Number', 'textdomain' );?></th>
                        <th><?php echo __( 'Title', 'textdomain' );?></th>
                        <th><?php echo __( 'Description', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_product_card_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $product_code = get_post_meta(get_the_ID(), 'product_code', true);
                            ?>
                            <tr id="edit-product-card-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo $product_code;?></td>
                                <td><?php the_title();?></td>
                                <td><?php the_content();?></td>
                            </tr>
                            <?php 
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-product-card" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>

            </fieldset>
            <div id="product-card-dialog" title="Product dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_product_card_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            $args = array(
                'post_type'      => 'product-card',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                'meta_key'       => 'product_code', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );
        
            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }
        
            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['paged'] = 1;
                $args['s'] = $search_query;
            }
        
            $query = new WP_Query($args);
        
            // Check if query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Remove the initial search query
                unset($args['s']);

                // Add meta query for searching across all meta keys
                $meta_keys = get_post_type_meta_keys('product-card');
                $meta_query_all_keys = array('relation' => 'OR');
                foreach ($meta_keys as $meta_key) {
                    $meta_query_all_keys[] = array(
                        'key'     => $meta_key,
                        'value'   => $search_query,
                        'compare' => 'LIKE',
                    );
                }
                $args['meta_query'][] = $meta_query_all_keys;
                $query = new WP_Query($args);
            }
        
            return $query;
        }

        function display_product_card_dialog($product_id=false) {
            ob_start();
            $product_code = get_post_meta($product_id, 'product_code', true);
            $product_title = get_the_title($product_id);
            $product_content = get_post_field('post_content', $product_id);
            ?>
            <fieldset>
                <input type="hidden" id="product-id" value="<?php echo esc_attr($product_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="product-code"><?php echo __( 'Number: ', 'textdomain' );?></label>
                <input type="text" id="product-code" value="<?php echo esc_attr($product_code);?>" <?php echo $disabled;?> class="text ui-widget-content ui-corner-all" />
                <label for="product-title"><?php echo __( 'Title: ', 'textdomain' );?></label>
                <input type="text" id="product-title" value="<?php echo esc_attr($product_title);?>" <?php echo $disabled;?> class="text ui-widget-content ui-corner-all" />
                <label for="product-content"><?php echo __( 'Description: ', 'textdomain' );?></label>
                <textarea id="product-content" rows="3" <?php echo $disabled;?> style="width:100%;"><?php echo esc_html($product_content);?></textarea>
                <?php
                // transaction data vs card key/value
                $key_value_pair = array(
                    '_product'   => $product_id,
                );
                $documents_class = new display_documents();
                $documents_class->get_transactions_by_key_value_pair($key_value_pair);
                ?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_product_card_dialog_data() {
            $product_id = sanitize_text_field($_POST['_product_id']);
            $response = array('html_contain' => $this->display_product_card_dialog($product_id));
            wp_send_json($response);
        }

        function set_product_card_dialog_data() {
            if( isset($_POST['_product_id']) ) {
                $product_id = sanitize_text_field($_POST['_product_id']);
                $product_code = sanitize_text_field($_POST['_product_code']);
                $data = array(
                    'ID'           => $product_id,
                    'post_title'   => sanitize_text_field($_POST['_product_title']),
                    'post_content' => $_POST['_product_content'],
                );
                wp_update_post( $data );
                update_post_meta($product_id, 'product_code', $product_code);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New product',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'product-card',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'product_code', time());
            }
            $response = array('html_contain' => $this->display_product_card_list());
            wp_send_json($response);
        }

        function del_product_card_dialog_data() {
            wp_delete_post($_POST['_product_id'], true);
            $response = array('html_contain' => $this->display_product_card_list());
            wp_send_json($response);
        }

        function select_product_card_options($selected_option=0) {
            $query = $this->retrieve_product_card_data(0);
            $options = '<option value="">Select product</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // Register equipment-card post type
        function register_equipment_card_post_type() {
            $labels = array(
                'menu_name'     => _x('Equipment', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                //'show_in_menu'  => false,
            );
            register_post_type( 'equipment-card', $args );
        }

        function display_equipment_card_list() {
            ob_start();
            $profiles_class = new display_profiles();
            ?>
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( '設備列表', 'textdomain' );?></h2>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $profiles_class->display_select_profile('equipment-card');?></div>
                <div style="text-align:right; display:flex;">
                    <input type="text" id="search-equipment" style="display:inline" placeholder="Search..." />
                </div>
            </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Number', 'textdomain' );?></th>
                        <th><?php echo __( 'Title', 'textdomain' );?></th>
                        <th><?php echo __( 'Description', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_equipment_card_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $equipment_code = get_post_meta(get_the_ID(), 'equipment_code', true);
                            ?>
                            <tr id="edit-equipment-card-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo $equipment_code;?></td>
                                <td><?php the_title();?></td>
                                <td><?php the_content();?></td>
                            </tr>
                            <?php 
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-equipment-card" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>

            </fieldset>
            <div id="equipment-card-dialog" title="Equipment dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_equipment_card_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            $args = array(
                'post_type'      => 'equipment-card',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                'meta_key'       => 'equipment_code', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );
        
            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }
        
            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['paged'] = 1;
                $args['s'] = $search_query;
            }
        
            $query = new WP_Query($args);
        
            // Check if query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Remove the initial search query
                unset($args['s']);

                // Add meta query for searching across all meta keys
                $meta_keys = get_post_type_meta_keys('equipment-card');
                $meta_query_all_keys = array('relation' => 'OR');
                foreach ($meta_keys as $meta_key) {
                    $meta_query_all_keys[] = array(
                        'key'     => $meta_key,
                        'value'   => $search_query,
                        'compare' => 'LIKE',
                    );
                }
                $args['meta_query'][] = $meta_query_all_keys;
                $query = new WP_Query($args);
            }
        
            return $query;
        }

        function display_equipment_card_dialog($equipment_id=false) {
            ob_start();
            $equipment_code = get_post_meta($equipment_id, 'equipment_code', true);
            $equipment_title = get_the_title($equipment_id);
            $equipment_content = get_post_field('post_content', $equipment_id);
            ?>
            <fieldset>
                <input type="hidden" id="equipment-id" value="<?php echo esc_attr($equipment_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="equipment-code"><?php echo __( 'Number: ', 'textdomain' );?></label>
                <input type="text" id="equipment-code" value="<?php echo esc_attr($equipment_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="equipment-title"><?php echo __( 'Title: ', 'textdomain' );?></label>
                <input type="text" id="equipment-title" value="<?php echo esc_attr($equipment_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="equipment-content"><?php echo __( 'Description: ', 'textdomain' );?></label>
                <textarea id="equipment-content" rows="3" style="width:100%;"><?php echo esc_html($equipment_content);?></textarea>
                <?php
                // transaction data vs card key/value
                $key_value_pair = array(
                    '_equipment'   => $equipment_id,
                );
                $documents_class = new display_documents();
                $documents_class->get_transactions_by_key_value_pair($key_value_pair);
                ?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_equipment_card_dialog_data() {
            $equipment_id = sanitize_text_field($_POST['_equipment_id']);
            $response = array('html_contain' => $this->display_equipment_card_dialog($equipment_id));
            wp_send_json($response);
        }

        function set_equipment_card_dialog_data() {
            if( isset($_POST['_equipment_id']) ) {
                $equipment_id = sanitize_text_field($_POST['_equipment_id']);
                $equipment_code = sanitize_text_field($_POST['_equipment_code']);
                $data = array(
                    'ID'           => $equipment_id,
                    'post_title'   => sanitize_text_field($_POST['_equipment_title']),
                    'post_content' => $_POST['_equipment_content'],
                );
                wp_update_post( $data );
                update_post_meta($equipment_id, 'equipment_code', $equipment_code);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New equipment',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'equipment-card',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'equipment_code', time());
            }
            $response = array('html_contain' => $this->display_equipment_card_list());
            wp_send_json($response);
        }

        function del_equipment_card_dialog_data() {
            wp_delete_post($_POST['_equipment_id'], true);
            $response = array('html_contain' => $this->display_equipment_card_list());
            wp_send_json($response);
        }

        function select_equipment_card_options($selected_option=0) {
            $query = $this->retrieve_equipment_card_data(0);
            $options = '<option value="">Select equipment</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // instrument-card post type
        function register_instrument_card_post_type() {
            $labels = array(
                'menu_name'     => _x('Instrument', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'instrument-card', $args );
        }

        function display_instrument_card_list() {
            ob_start();
            $profiles_class = new display_profiles();
            ?>
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( '儀器列表', 'textdomain' );?></h2>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $profiles_class->display_select_profile('instrument-card');?></div>
                <div style="text-align:right; display:flex;">
                    <input type="text" id="search-instrument" style="display:inline" placeholder="Search..." />
                </div>
            </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Number', 'textdomain' );?></th>
                        <th><?php echo __( 'Title', 'textdomain' );?></th>
                        <th><?php echo __( 'Description', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_instrument_card_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $instrument_code = get_post_meta(get_the_ID(), 'instrument_code', true);
                            ?>
                            <tr id="edit-instrument-card-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo $instrument_code;?></td>
                                <td><?php the_title();?></td>
                                <td><?php the_content();?></td>
                            </tr>
                            <?php 
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-instrument-card" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>

            </fieldset>
            <div id="instrument-card-dialog" title="Instrument dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_instrument_card_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            $args = array(
                'post_type'      => 'instrument-card',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                'meta_key'       => 'instrument_code', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );

            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }
        
            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['paged'] = 1;
                $args['s'] = $search_query;
            }
        
            $query = new WP_Query($args);
        
            // Check if query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Remove the initial search query
                unset($args['s']);

                // Add meta query for searching across all meta keys
                $meta_keys = get_post_type_meta_keys('instrument-card');
                $meta_query_all_keys = array('relation' => 'OR');
                foreach ($meta_keys as $meta_key) {
                    $meta_query_all_keys[] = array(
                        'key'     => $meta_key,
                        'value'   => $search_query,
                        'compare' => 'LIKE',
                    );
                }
                $args['meta_query'][] = $meta_query_all_keys;
                $query = new WP_Query($args);
            }
        
            return $query;
        }

        function display_instrument_card_dialog($instrument_id=false) {
            ob_start();
            $instrument_code = get_post_meta($instrument_id, 'instrument_code', true);
            $instrument_title = get_the_title($instrument_id);
            $instrument_content = get_post_field('post_content', $instrument_id);
            ?>
            <fieldset>
                <input type="hidden" id="instrument-id" value="<?php echo esc_attr($instrument_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="instrument-code"><?php echo __( 'Number: ', 'textdomain' );?></label>
                <input type="text" id="instrument-code" value="<?php echo esc_attr($instrument_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="instrument-title"><?php echo __( 'Title: ', 'textdomain' );?></label>
                <input type="text" id="instrument-title" value="<?php echo esc_attr($instrument_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="instrument-content"><?php echo __( 'Description: ', 'textdomain' );?></label>
                <textarea id="instrument-content" rows="3" style="width:100%;"><?php echo esc_html($instrument_content);?></textarea>
                <?php
                // transaction data vs card key/value
                $key_value_pair = array(
                    '_instrument'   => $instrument_id,
                );
                $documents_class = new display_documents();
                $documents_class->get_transactions_by_key_value_pair($key_value_pair);
                ?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_instrument_card_dialog_data() {
            $instrument_id = sanitize_text_field($_POST['_instrument_id']);
            $response = array('html_contain' => $this->display_instrument_card_dialog($instrument_id));
            wp_send_json($response);
        }

        function set_instrument_card_dialog_data() {
            if( isset($_POST['_instrument_id']) ) {
                $instrument_id = sanitize_text_field($_POST['_instrument_id']);
                $instrument_code = sanitize_text_field($_POST['_instrument_code']);
                $data = array(
                    'ID'           => $instrument_id,
                    'post_title'   => sanitize_text_field($_POST['_instrument_title']),
                    'post_content' => $_POST['_instrument_content'],
                );
                wp_update_post( $data );
                update_post_meta($instrument_id, 'instrument_code', $instrument_code);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New instrument',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'instrument-card',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'instrument_code', time());
            }
            $response = array('html_contain' => $this->display_instrument_card_list());
            wp_send_json($response);
        }

        function del_instrument_card_dialog_data() {
            wp_delete_post($_POST['_instrument_id'], true);
            $response = array('html_contain' => $this->display_instrument_card_list());
            wp_send_json($response);
        }

        function select_instrument_card_options($selected_option=0) {
            $query = $this->retrieve_instrument_card_data(0);
            $options = '<option value="">Select instrument</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // department-card post
        function register_department_card_post_type() {
            $labels = array(
                'menu_name'     => _x('Department', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'department-card', $args );
        }

        function display_department_card_list() {
            ob_start();
            $profiles_class = new display_profiles();
            ?>
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( '部門資料', 'textdomain' );?></h2>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $profiles_class->display_select_profile('department-card');?></div>
                <div style="text-align:right; display:flex;">
                    <input type="text" id="search-department" style="display:inline" placeholder="Search..." />
                </div>
            </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Number', 'textdomain' );?></th>
                        <th><?php echo __( 'Title', 'textdomain' );?></th>
                        <th><?php echo __( 'Description', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_department_card_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $department_number = get_post_meta(get_the_ID(), 'department_number', true);
                            ?>
                            <tr id="edit-department-card-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo $department_number;?></td>
                                <td><?php the_title();?></td>
                                <td><?php the_content();?></td>
                            </tr>
                            <?php 
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-department-card" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>

            </fieldset>
            <div id="department-card-dialog" title="Department dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_department_card_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            $args = array(
                'post_type'      => 'department-card',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                'meta_key'       => 'department_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );
        
            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }
        
            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['paged'] = 1;
                $args['s'] = $search_query;
            }
        
            $query = new WP_Query($args);
        
            // Check if query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Remove the initial search query
                unset($args['s']);

                // Add meta query for searching across all meta keys
                $meta_keys = get_post_type_meta_keys('department-card');
                $meta_query_all_keys = array('relation' => 'OR');
                foreach ($meta_keys as $meta_key) {
                    $meta_query_all_keys[] = array(
                        'key'     => $meta_key,
                        'value'   => $search_query,
                        'compare' => 'LIKE',
                    );
                }
                $args['meta_query'][] = $meta_query_all_keys;
                $query = new WP_Query($args);
            }
        
            return $query;
        }

        function display_department_card_dialog($department_id=false) {
            ob_start();
            $department_number = get_post_meta($department_id, 'department_number', true);
            $department_title = get_the_title($department_id);
            $department_content = get_post_field('post_content', $department_id);
            ?>
            <fieldset>
                <input type="hidden" id="department-id" value="<?php echo esc_attr($department_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="department-number"><?php echo __( 'Number: ', 'textdomain' );?></label>
                <input type="text" id="department-number" value="<?php echo esc_attr($department_number);?>" class="text ui-widget-content ui-corner-all" />
                <label for="department-title"><?php echo __( 'Title: ', 'textdomain' );?></label>
                <input type="text" id="department-title" value="<?php echo esc_attr($department_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="department-content"><?php echo __( 'Description: ', 'textdomain' );?></label>
                <textarea id="department-content" rows="3" style="width:100%;"><?php echo esc_html($department_content);?></textarea>
                <label for="department-members"><?php echo __( '部門成員：', 'textdomain' );?></label>
                <?php echo $this->display_department_user_list($department_id);?>
                <?php
                // transaction data vs card key/value
                $key_value_pair = array(
                    '_department'   => $department_id,
                );
                $documents_class = new display_documents();
                $documents_class->get_transactions_by_key_value_pair($key_value_pair);
                ?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_department_card_dialog_data() {
            $department_id = sanitize_text_field($_POST['_department_id']);
            $response = array('html_contain' => $this->display_department_card_dialog($department_id));
            wp_send_json($response);
        }

        function set_department_card_dialog_data() {
            if( isset($_POST['_department_id']) ) {
                $department_id = sanitize_text_field($_POST['_department_id']);
                $department_number = sanitize_text_field($_POST['_department_number']);
                $data = array(
                    'ID'           => $department_id,
                    'post_title'   => sanitize_text_field($_POST['_department_title']),
                    'post_content' => $_POST['_department_content'],
                );
                wp_update_post( $data );
                update_post_meta($department_id, 'department_number', $department_number);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New department',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'department-card',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'department_number', time());
            }
            $response = array('html_contain' => $this->display_department_card_list());
            wp_send_json($response);
        }

        function del_department_card_dialog_data() {
            wp_delete_post($_POST['_department_id'], true);
            $response = array('html_contain' => $this->display_department_card_list());
            wp_send_json($response);
        }

        function select_department_card_options($selected_option=0) {
            $query = $this->retrieve_department_card_data(0);
            $options = '<option value="">Select department</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function display_department_user_list($department_id=false) {
            ob_start();
            $user_ids = array();            
            if ($department_id==false) {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                    $meta_query_args = array(
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                        //'compare' => '=',
                    ),
                );
                $users = get_users(array('meta_query' => $meta_query_args));
                foreach ($users as $user) {
                    $user_ids[] = $user->ID;
                }    
            } else {
                $user_ids = get_post_meta($department_id, 'user_ids', true);
            }
            ?>
            <div id="department-user-list">
                <fieldset style="margin-top:5px;">
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Name', 'textdomain' );?></th>
                            <th><?php echo __( 'Email', 'textdomain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($user_ids as $user_id) {
                            $user_data = get_userdata($user_id);
                            ?>
                            <tr id="edit-department-user-<?php echo $user_id; ?>">
                                <td style="text-align:center;"><?php echo $user_data->display_name; ?></td>
                                <td style="text-align:center;"><?php echo $user_data->user_email; ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                    <div id="new-department-user" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                </fieldset>
            </div>
            <div id="department-user-dialog" title="User dialog"></div>
            <?php
            return ob_get_clean();
        }

        function get_department_user_list_data() {
            $response = array();
            $response = array('html_contain' => $this->display_department_user_list());
            wp_send_json($response);
        }

        function add_department_user_dialog_data() {
            $response = array();
        
            // Check if both _user_id and _department_id are set and valid
            if (isset($_POST['_user_id']) && isset($_POST['_department_id'])) {
                $user_id = absint($_POST['_user_id']);
                $department_id = absint($_POST['_department_id']);
        
                // Retrieve the current user_ids meta value
                $user_ids = get_post_meta($department_id, 'user_ids', true);
        
                // If there are no user_ids, initialize an empty array
                if (!$user_ids) {
                    $user_ids = array();
                }
        
                // Check if the user_id is not already in the user_ids array
                if (!in_array($user_id, $user_ids)) {
                    // Add the user_id to the user_ids array
                    $user_ids[] = $user_id;
        
                    // Update the user_ids meta value
                    update_post_meta($department_id, 'user_ids', $user_ids);
        
                    $response['success'] = true;
                    $response['message'] = 'User ID added successfully.';
                } else {
                    $response['success'] = false;
                    $response['message'] = 'User ID already exists.';
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Invalid user ID or department ID.';
            }
        
            $department_id = absint($_POST['_department_id']);
            $response = array('html_contain' => $this->display_department_user_list($department_id));
            wp_send_json($response);
        }

        function del_department_user_dialog_data() {
            $response = array();
        
            // Check if both _user_id and _department_id are set and valid
            if (isset($_POST['_user_id']) && isset($_POST['_department_id'])) {
                $user_id = absint($_POST['_user_id']);
                $department_id = absint($_POST['_department_id']);
        
                // Retrieve the current user_ids meta value
                $user_ids = get_post_meta($department_id, 'user_ids', true);
        
                // If there are no user_ids, initialize an empty array
                if (!$user_ids) {
                    $user_ids = array();
                }
        
                // Check if the user_id is in the user_ids array
                if (in_array($user_id, $user_ids)) {
                    // Remove the user_id from the user_ids array
                    $user_ids = array_diff($user_ids, array($user_id));
        
                    // Update the user_ids meta value
                    update_post_meta($department_id, 'user_ids', $user_ids);
        
                    $response['success'] = true;
                    $response['message'] = 'User ID removed successfully.';
                } else {
                    $response['success'] = false;
                    $response['message'] = 'User ID does not exist.';
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Invalid user ID or department ID.';
            }
        
            $department_id = absint($_POST['_department_id']);
            $response = array('html_contain' => $this->display_department_user_list($department_id));
            wp_send_json($response);
        }
        
        // employees
        function select_multiple_employees_options($selected_options = array()) {
            if (!is_array($selected_options)) {
                $selected_options = array();
            }

            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
        
            // Retrieve users based on site_id
            $meta_query_args = array(
                array(
                    'key'     => 'site_id',
                    'value'   => $site_id,
                    //'compare' => '=',
                ),
            );
            $users = get_users(array('meta_query' => $meta_query_args));
        
            // Initialize options HTML
            $options = '';
        
            // Loop through the users
            foreach ($users as $user) {
                // Check if the current user ID is in the selected options array
                $selected = in_array($user->ID, $selected_options) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name) . '</option>';
            }
        
            // Return the options HTML
            return $options;
        }
    }
    $cards_class = new erp_cards();
}


