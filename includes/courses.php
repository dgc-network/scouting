<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('courses')) {
    class courses {
        // Class constructor
        public function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_course_scripts' ) );
            //add_action( 'init', array( $this, 'register_course_post_type' ) );
            add_shortcode('display-course-contains', array( $this, 'display_course_contains' ) );

            add_action( 'wp_ajax_get_course_dialog_data', array( $this, 'get_course_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_course_dialog_data', array( $this, 'get_course_dialog_data' ) );
            add_action( 'wp_ajax_set_course_dialog_data', array( $this, 'set_course_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_course_dialog_data', array( $this, 'set_course_dialog_data' ) );
            add_action( 'wp_ajax_del_course_dialog_data', array( $this, 'del_course_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_course_dialog_data', array( $this, 'del_course_dialog_data' ) );
            
            add_action( 'wp_ajax_sort_course_list_data', array( $this, 'sort_course_list_data' ) );
            add_action( 'wp_ajax_nopriv_sort_course_list_data', array( $this, 'sort_course_list_data' ) );
        }

        function enqueue_course_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);
            wp_enqueue_style('wp-enqueue-css', plugins_url('/assets/css/wp-enqueue.css', __DIR__), '', time());

            wp_enqueue_script('courses', plugins_url('js/courses.js', __FILE__), array('jquery'), time());
            wp_localize_script('courses', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('course-nonce'), // Generate nonce
            ));                
        }
     
        // course
        function register_course_post_type() {
            $labels = array(
                'menu_name'     => _x('course', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'course', $args );
        }
        
        function display_course_contains($atts) {
            ob_start();
            // Extract and sanitize the shortcode attributes
            $atts = shortcode_atts(
                array(
                    'course_id' => false,
                    'course_category' => false,
                ), $atts
            );
        
            $course_id = $atts['course_id'];
            $course_category = $atts['course_category'];
            $course_title = isset($_GET['_course_title']) ? sanitize_text_field($_GET['_course_title']) : '';

            if (!$course_category && !$course_id && !$course_title) {
                echo $this->display_course_list();
            }

            if ($course_title) {
                ?>
                <div class="course-content">
                    <?php echo $this->get_course_content_by_title($course_title); ?>
                </div>
                <?php
            }
            return ob_get_clean();
        }
                
        function get_course_content_by_title($post_title) {
            $posts = get_posts(array(
                'post_type'   => 'course',
                'title'       => $post_title,
                'post_status' => 'publish',
                'numberposts' => 1,
            ));
        
            if (!empty($posts)) {
                return $posts[0]->post_content;
            }
        
            return '';
        }
        
        function display_course_list() {
            ob_start();
            ?>
            <div class="ui-widget" id="result-container">
                <?php //echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( 'Courses', 'textdomain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php //$profiles_class->display_select_profile('iso-standard');?></div>
                    <div style="text-align:right"></div>                        
                </div>

                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Course', 'textdomain' );?></th>
                            <th><?php echo __( 'No.', 'textdomain' );?></th>
                        </thead>
                        <tbody id="sortable-course-list">
                        <?php
                        $query = $this->retrieve_course_data();
                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                $course_id = get_the_ID();
                                $course_title = get_the_title();
                                $course_content = get_the_content();
                                $course_number = get_post_meta($course_id, 'course_number', true);
                                $course_category = get_post_meta($course_id, 'course_category', true);
                                ?>
                                <tr id="edit-course-<?php echo $course_id;?>" data-field-id="<?php echo $course_id;?>">
                                    <td><?php echo $course_title;?></td>
                                    <td style="text-align:center;"><?php echo $course_number;?></td>
                                </tr>
                                <?php 
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                        </tbody>
                    </table>
                    <?php if (current_user_can('administrator')) {?>
                        <div id="new-course" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                    <?php }?>
                </fieldset>
                <div id="course-dialog" title="course dialog"></div>
            </div>
            <?php
            return ob_get_clean();
        }

        function retrieve_course_data() {
            $args = array(
                'post_type'      => 'course',
                'posts_per_page' => -1,        
                'meta_key'       => 'sorting_key',
                'orderby'        => 'meta_value_num', // Specify meta value as numeric
                'order'          => 'ASC',
            );
            $query = new WP_Query($args);
            return $query;
        }

        function display_course_dialog($course_id=false) {
            $course_title = get_the_title($course_id);
            $course_content = get_post_field('post_content', $course_id);
            $course_number = get_post_meta($course_id, 'course_number', true);
            $course_category = get_post_meta($course_id, 'course_category', true);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="course-id" value="<?php echo esc_attr($course_id);?>" />
                <label for="course-title"><?php echo __( 'Title', 'textdomain' );?></label>
                <input type="button" id="course-preview" value="Preview" class="button" style="font-size:xx-small" />
                <input type="text" id="course-title" value="<?php echo esc_attr($course_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="course-content"><?php echo __( 'Content', 'textdomain' );?></label>
                <textarea id="course-content" rows="10" style="width:100%;"><?php echo esc_html($course_content);?></textarea>
                <label for="course-number"><?php echo __( 'Number', 'textdomain' );?></label>
                <input type="text" id="course-number" value="<?php echo esc_attr($course_number);?>" class="text ui-widget-content ui-corner-all" />
                <label for="course-category"><?php echo __( 'Category', 'textdomain' );?></label>
                <select id="course-category" class="select ui-widget-content ui-corner-all"><?php echo $this->select_course_category_options($parent_category);?></select>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_course_dialog_data() {
            $response = array();
            $course_id = sanitize_text_field($_POST['_course_id']);
            $response['title'] = get_the_title($course_id);
            if (current_user_can('administrator')) {
                $response['html_contain'] = $this->display_course_dialog($course_id);
            }
            wp_send_json($response);
        }

        function set_course_dialog_data() {
            if( isset($_POST['_course_id']) ) {
                $course_id = sanitize_text_field($_POST['_course_id']);
                $course_title = isset($_POST['_course_title']) ? sanitize_text_field($_POST['_course_title']) : '';
                $course_number = isset($_POST['_course_number']) ? sanitize_text_field($_POST['_course_number']) : '';
                $course_category = isset($_POST['_course_category']) ? sanitize_text_field($_POST['_course_category']) : '';
                $data = array(
                    'ID'           => $course_id,
                    'post_title'   => $course_title,
                    'post_content' => $_POST['_course_content'],
                );
                wp_update_post( $data );
                update_post_meta($course_id, 'course_number', $course_number);
                update_post_meta($course_id, 'course_category', $course_category);
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_type'     => 'course',
                    'post_title'    => 'new course',
                    'post_content'  => __( 'Your post content goes here.', 'textdomain' ),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'sorting_key', 999);
            }
            $response = array('html_contain' => $this->display_course_list());
            wp_send_json($response);
        }

        function del_course_dialog_data() {
            wp_delete_post($_POST['_course_id'], true);
            $response = array('html_contain' => $this->display_course_list());
            wp_send_json($response);
        }

        function sort_course_list_data() {
            $response = array('success' => false, 'error' => 'Invalid data format');
            if (isset($_POST['_field_id_array']) && is_array($_POST['_field_id_array'])) {
                $field_id_array = array_map('absint', $_POST['_field_id_array']);        
                foreach ($field_id_array as $index => $field_id) {
                    if (current_user_can('administrator')) {
                        update_post_meta($field_id, 'sorting_key', $index);
                    }
                }
                $response = array('success' => true);
            }
            wp_send_json($response);
        }

        function select_course_options($selected_option=0) {
            $query = $this->retrieve_course_data();
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
            while ($query->have_posts()) : $query->the_post();
                $course_id = get_the_ID();
                $course_title = get_the_title();
                $selected = ($selected_option == $course_id) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($course_id) . '" '.$selected.' >' . esc_html($course_title) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function select_course_category_options($selected_option=0) {
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
    $courses = new courses();
}