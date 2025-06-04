<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('itinerary')) {
    class itinerary {
        // Class constructor
        public function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_itinerary_scripts' ) );
            add_action( 'init', array( $this, 'register_itinerary_post_type' ) );
            add_shortcode('display-itinerary-contains', array( $this, 'display_itinerary_contains' ) );

            add_action( 'wp_ajax_get_itinerary_dialog_data', array( $this, 'get_itinerary_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_itinerary_dialog_data', array( $this, 'get_itinerary_dialog_data' ) );
            add_action( 'wp_ajax_set_itinerary_dialog_data', array( $this, 'set_itinerary_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_itinerary_dialog_data', array( $this, 'set_itinerary_dialog_data' ) );
            add_action( 'wp_ajax_del_itinerary_dialog_data', array( $this, 'del_itinerary_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_itinerary_dialog_data', array( $this, 'del_itinerary_dialog_data' ) );
            

        }

        function enqueue_itinerary_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);
            wp_enqueue_style('wp-enqueue-css', plugins_url('/assets/css/wp-enqueue.css', __DIR__), '', time());

            wp_enqueue_script('itinerary', plugins_url('js/itinerary.js', __FILE__), array('jquery'), time());
            wp_localize_script('itinerary', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('itinerary-nonce'), // Generate nonce
            ));                
        }
     
        // itinerary
        function register_itinerary_post_type() {
            $labels = array(
                'menu_name'     => _x('itinerary', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'itinerary', $args );
        }
        
        function display_itinerary_contains($atts) {
            ob_start();
            // Extract and sanitize the shortcode attributes
            $atts = shortcode_atts(
                array(
                    'itinerary_id' => false,
                    'itinerary_category' => false,
                ), $atts
            );
        
            $itinerary_id = $atts['itinerary_id'];
            $itinerary_category = $atts['itinerary_category'];
            $itinerary_title = isset($_GET['_itinerary_title']) ? sanitize_text_field($_GET['_itinerary_title']) : '';

            if (!$itinerary_category && !$itinerary_id && !$itinerary_title) {
                echo $this->display_itinerary_list();
            }

            if ($itinerary_title) {
                ?>
                <div class="itinerary-content">
                    <?php echo $this->get_itinerary_content_by_title($itinerary_title); ?>
                </div>
                <?php
            }
            return ob_get_clean();
        }
                
        function get_itinerary_content_by_title($post_title) {
            $posts = get_posts(array(
                'post_type'   => 'itinerary',
                'title'       => $post_title,
                'post_status' => 'publish',
                'numberposts' => 1,
            ));
        
            if (!empty($posts)) {
                return $posts[0]->post_content;
            }
        
            return '';
        }
        
        function display_itinerary_list() {
            ob_start();
            ?>
            <div class="ui-widget" id="result-container">
                <?php //echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( 'Itinerary', 'textdomain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php //$profiles_class->display_select_profile('iso-standard');?></div>
                    <div style="text-align:right"></div>                        
                </div>

                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Itinerary', 'textdomain' );?></th>
                            <th><?php echo __( 'Category', 'textdomain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        $query = $this->retrieve_itinerary_data();
                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                $itinerary_id = get_the_ID();
                                $itinerary_title = get_the_title();
                                $itinerary_content = get_the_content();
                                $itinerary_category = get_post_meta($itinerary_id, 'itinerary_category', true);
                                ?>
                                <tr id="edit-itinerary-<?php echo $itinerary_id;?>">
                                    <td><?php echo $itinerary_title;?></td>
                                    <td style="text-align:center;"><?php echo $itinerary_category;?></td>
                                </tr>
                                <?php 
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                        </tbody>
                    </table>
                    <?php if (current_user_can('administrator')) {?>
                        <div id="new-itinerary" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                    <?php }?>
                </fieldset>
                <div id="itinerary-dialog" title="Itinerary dialog"></div>
            </div>
            <?php
            return ob_get_clean();
        }

        function retrieve_itinerary_data() {
            $args = array(
                'post_type'      => 'itinerary',
                'posts_per_page' => -1,        
                //'orderby'        => 'title',  // Order by post title
                //'order'          => 'ASC',    // Order in ascending order (or use 'DESC' for descending)
            );
            $query = new WP_Query($args);
            return $query;
        }

        function display_itinerary_dialog($itinerary_id=false) {
            $itinerary_title = get_the_title($itinerary_id);
            $itinerary_content = get_post_field('post_content', $itinerary_id);
            $itinerary_url = get_post_meta($itinerary_id, 'itinerary_url', true);
            $itinerary_category = get_post_meta($itinerary_id, 'itinerary_category', true);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="itinerary-id" value="<?php echo esc_attr($itinerary_id);?>" />
                <label for="itinerary-title"><?php echo __( 'Title', 'textdomain' );?></label>
                <input type="button" id="itinerary-preview" value="Preview" class="button" style="font-size:xx-small" />
                <input type="text" id="itinerary-title" value="<?php echo esc_attr($itinerary_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="itinerary-content"><?php echo __( 'Content', 'textdomain' );?></label>
                <textarea id="itinerary-content" rows="10" style="width:100%;"><?php echo esc_html($itinerary_content);?></textarea>
                <label for="itinerary-url"><?php echo __( 'URL', 'textdomain' );?></label>
                <input type="text" id="itinerary-url" value="<?php echo esc_attr($itinerary_url);?>" class="text ui-widget-content ui-corner-all" />
                <label for="itinerary-category"><?php echo __( 'Category', 'textdomain' );?></label>
                <select id="itinerary-category" class="select ui-widget-content ui-corner-all"><?php echo $this->select_itinerary_category_options($parent_category);?></select>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_itinerary_dialog_data() {
            $response = array();
            $itinerary_id = sanitize_text_field($_POST['_itinerary_id']);
            $response['title'] = get_the_title($itinerary_id);
            if (current_user_can('administrator')) {
                $response['html_contain'] = $this->display_itinerary_dialog($itinerary_id);
            }
            wp_send_json($response);
        }

        function set_itinerary_dialog_data() {
            if( isset($_POST['_itinerary_id']) ) {
                $itinerary_id = sanitize_text_field($_POST['_itinerary_id']);
                $itinerary_title = isset($_POST['_itinerary_title']) ? sanitize_text_field($_POST['_itinerary_title']) : '';
                $itinerary_url = isset($_POST['_itinerary_url']) ? sanitize_text_field($_POST['_itinerary_url']) : '';
                $itinerary_category = isset($_POST['_itinerary_category']) ? sanitize_text_field($_POST['_itinerary_category']) : '';
                $data = array(
                    'ID'           => $itinerary_id,
                    'post_title'   => $itinerary_title,
                    'post_content' => $_POST['_itinerary_content'],
                );
                wp_update_post( $data );
                update_post_meta($itinerary_id, 'itinerary_url', $itinerary_url);
                update_post_meta($itinerary_id, 'itinerary_category', $itinerary_category);
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_type'     => 'itinerary',
                    'post_title'    => '-',
                    'post_content'  => __( 'Your post content goes here.', 'textdomain' ),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $post_id = wp_insert_post($new_post);
            }
            $response = array('html_contain' => $this->display_itinerary_list());
            wp_send_json($response);
        }

        function del_itinerary_dialog_data() {
            wp_delete_post($_POST['_itinerary_id'], true);
            $response = array('html_contain' => $this->display_itinerary_list());
            wp_send_json($response);
        }

        function select_itinerary_options($selected_option=0) {
            $query = $this->retrieve_itinerary_data();
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
            while ($query->have_posts()) : $query->the_post();
                $itinerary_id = get_the_ID();
                $itinerary_title = get_the_title();
                $selected = ($selected_option == $itinerary_id) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($itinerary_id) . '" '.$selected.' >' . esc_html($itinerary_title) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function select_itinerary_category_options($selected_option=0) {
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
            $economic_selected = ($selected_option == 'economic-growth') ? 'selected' : '';
            $environmental_selected = ($selected_option == 'environmental-protection') ? 'selected' : '';
            $social_selected = ($selected_option == 'social-responsibility') ? 'selected' : '';
            $options .= '<option value="economic-growth" '.$economic_selected.'>' . __( 'Economic Growth', 'textdomain' ) . '</option>';
            $options .= '<option value="environmental-protection" '.$environmental_selected.'>' . __( 'environmental protection', 'textdomain' ) . '</option>';
            $options .= '<option value="social-responsibility" '.$social_selected.'>' . __( 'social responsibility', 'textdomain' ) . '</option>';    
            return $options;
        }

    }
    $itinerary = new itinerary();
}