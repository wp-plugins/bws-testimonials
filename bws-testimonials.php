<?php
/*
Plugin Name: Testimonials by BestWebSoft
Plugin URI: http://bestwebsoft.com/products/
Description: Plugin for displaying Testimonials.
Author: BestWebSoft
Version: 0.1.2
Author URI: http://bestwebsoft.com/
License: GPLv3 or later
*/

/*  @ Copyright 2015  BestWebSoft  ( http://support.bestwebsoft.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Add option page in admin menu */
if ( ! function_exists( 'tstmnls_admin_menu' ) ) {
	function tstmnls_admin_menu() {
		bws_add_general_menu( plugin_basename( __FILE__ ) );
		add_submenu_page( 'bws_plugins', __( 'Testimonials Settings', 'testimonials' ), 'Testimonials', 'manage_options', "testimonials.php", 'tstmnls_settings_page' );
	}
}

if ( ! function_exists ( 'tstmnls_init' ) ) {
	function tstmnls_init() {
		global $tstmnls_plugin_info;
		load_plugin_textdomain( 'testimonials', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_functions.php' );
		
		if ( empty( $tstmnls_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$tstmnls_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Function check if plugin is compatible with current WP version  */
		bws_wp_version_check( plugin_basename( __FILE__ ), $tstmnls_plugin_info, "3.5" );

		tstmnls_register_testimonial_post_type();
	}
}

if ( ! function_exists ( 'tstmnls_admin_init' ) ) {
	function tstmnls_admin_init() {
		global $bws_plugin_info, $tstmnls_plugin_info, $pagenow;

		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '180', 'version' => $tstmnls_plugin_info["Version"] );

		add_meta_box( 'custom-metabox', __( 'Testimonials Info', 'testimonials' ), 'tstmnls_custom_metabox', 'bws-testimonial', 'normal', 'high' );

		/* Call register settings function */
		if ( 'widgets.php' == $pagenow || ( isset( $_REQUEST['page'] ) && 'testimonials.php' == $_REQUEST['page'] ) )
			tstmnls_register_settings();
	}
}

if ( ! function_exists ( 'tstmnls_register_testimonial_post_type' ) ) {
	function tstmnls_register_testimonial_post_type() {
		$args = array(
			'label'				=>	__( 'Testimonials', 'testimonials' ),
			'singular_label'	=>	__( 'Testimonial', 'testimonials' ),
			'public'			=>	true,
			'show_ui'			=>	true,
			'capability_type' 	=>	'post',
			'hierarchical'		=>	false,
			'rewrite'			=>	true,
			'supports'			=>	array( 'title', 'editor' ),
			'labels'			=>	array(
				'add_new_item'			=>	__( 'Add a new testimonial', 'testimonials' ),
				'edit_item'				=>	__( 'Edit testimonials', 'testimonials' ),
				'new_item'				=>	__( 'New testimonial', 'testimonials' ),
				'view_item'				=>	__( 'View testimonials', 'testimonials' ),
				'search_items'			=>	__( 'Search testimonials', 'testimonials' ),
				'not_found'				=>	__( 'No testimonials found', 'testimonials' ),
				'not_found_in_trash'	=>	__( 'No testimonials found in Trash', 'testimonials' )
			)
		);
		register_post_type( 'bws-testimonial' , $args );
	}
}

/**
	* Register settings for plugin 
	*/
if ( ! function_exists( 'tstmnls_register_settings' ) ) {
	function tstmnls_register_settings() {
		global $tstmnls_options, $tstmnls_plugin_info;

		$tstmnls_option_defaults = array(
			'plugin_option_version' 	=> $tstmnls_plugin_info["Version"],
			'widget_title'				=>	__( 'Testimonials', 'testimonials' ),
			'count'						=>	'5'
		);

		/* Install the option defaults */
		if ( ! get_option( 'tstmnls_options' ) )
			add_option( 'tstmnls_options', $tstmnls_option_defaults );

		$tstmnls_options = get_option( 'tstmnls_options' );

		if ( ! isset( $tstmnls_options['plugin_option_version'] ) || $tstmnls_options['plugin_option_version'] != $tstmnls_plugin_info["Version"] ) {
			$tstmnls_options = array_merge( $tstmnls_option_defaults, $tstmnls_options );
			$tstmnls_options['plugin_option_version'] = $tstmnls_plugin_info["Version"];
			update_option( 'tstmnls_options', $tstmnls_options );
		}		
	}
}

/**
	* Add settings page in admin area
	*/
if ( ! function_exists( 'tstmnls_settings_page' ) ) {
	function tstmnls_settings_page(){ 
		global $title, $tstmnls_options, $tstmnls_plugin_info;
		$message = $error = ''; 
		
		if ( isset( $_POST['tstmnls_form_submit'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'tstmnls_check_field' ) ) {

			$tstmnls_options['widget_title'] = stripslashes( esc_html( $_POST['tstmnls_widget_title'] ) );
			$tstmnls_options['count'] = intval( $_POST['tstmnls_count'] );

			update_option( 'tstmnls_options', $tstmnls_options ); 
			$message = __( 'Changes saved', 'testimonials' );
		} ?>
		<div class="wrap">
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2><?php echo $title; ?></h2>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="admin.php?page=testimonials.php"><?php _e( 'Settings', 'testimonials' ); ?></a>
				<a class="nav-tab" href="http://bestwebsoft.com/products/testimonials/faq/" target="_blank"><?php _e( 'FAQ', 'testimonials' ); ?></a>
			</h2>
			<div id="tstmnls_settings_notice" class="updated fade" style="display:none"><p><strong><?php _e( "Notice:", 'testimonials' ); ?></strong> <?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'testimonials' ); ?></p></div>
			<div class="updated fade" <?php if ( ! isset( $_REQUEST['tstmnls_submit'] ) || $error != "" ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error" <?php if ( "" == $error ) echo "style=\"display:none\""; ?>><p><?php echo $error; ?></p></div>
			<form id="tstmnls_settings_form" method='post' action='admin.php?page=testimonials.php'>
				<p><?php printf(
						'%1$s "<strong>%2$s</strong>" %3$s.',
						__( 'If you would like to display testimonials with a widget, you need to add the widget', 'testimonials' ),
						__( 'Testimonials Widget', 'testimonials' ),
						__( 'on the Widgets tab', 'testimonials' )
					); ?>
				</p>	
				<?php _e( "If you would like to add testimonials to your website, just copy and paste this shortcode into your post or page:", 'testimonials' ); ?> <span class="tstmnls_code">[bws_testimonials]</span>.
				</p>
				<p>
					<?php _e( "Also, you can paste the following strings into the template source code", 'testimonials' ); ?> 
					<code>
						&lt;?php if ( has_action( 'tstmnls_show_testimonials' ) ) {
							do_action( 'tstmnls_show_testimonials' );
						} ?&gt;
					</code>
				</p>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><?php _e( 'Widget title', 'testimonials' ); ?></th>
							<td>
								<input type="text" class="text" value="<?php echo $tstmnls_options['widget_title']; ?>" name="tstmnls_widget_title" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Number of testimonials to be displayed', 'testimonials' ); ?></th>
							<td>
								<input type="number" class="text" value="<?php echo $tstmnls_options['count']; ?>" name="tstmnls_count" />
							</td>
						</tr>
					</tbody>
				</table>
				<p class="submit">
					<input type="submit" value="<?php _e( 'Save Changes', 'testimonials' ); ?>" class="button button-primary" id="submit" name="tstmnls_submit">
					<input type="hidden" name="tstmnls_form_submit" value="submit" />
					<?php wp_nonce_field( plugin_basename( __FILE__ ), 'tstmnls_check_field' ) ?>
				</p>
			</form>
			<?php bws_plugin_reviews_block( $tstmnls_plugin_info["Name"], 'bws-testimonials' ); ?>
		</div>
	<?php }
}

if ( ! function_exists( 'tstmnls_custom_metabox' ) ) {
	function tstmnls_custom_metabox() {
		global $post;
		$testimonials_info = get_post_meta( $post->ID, '_testimonials_info', true ); ?>
		<p>
			<label for="tstmnls_author"><?php _e( 'Author', 'testimonials' ); ?>:<br />
			<input type="text" id="tstmnls_author" size="100" name="tstmnls_author" value="<?php if ( ! empty( $testimonials_info['author'] ) ) echo $testimonials_info['author']; ?>"/></label>
		</p>
		<p>
			<label for="tstmnls_company_name"><?php _e( 'Company Name', 'testimonials' ); ?>:</label><br />
			<input type="text" id="tstmnls_company_name" size="100" name="tstmnls_company_name" value="<?php if ( ! empty( $testimonials_info['company_name'] ) ) echo $testimonials_info['company_name']; ?>"/>
		</p>
	<?php }
}

if ( ! function_exists( 'tstmnls_save_postdata' ) ) {
	function tstmnls_save_postdata( $post_id ) {
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */
		/* If this is an autosave, our form has not been submitted, so we don't want to do anything. */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;
		/* Check if our nonce is set. */
		if ( get_post_type( $post_id ) != 'bws-testimonial' )
			return $post_id;
		else {
			if ( isset( $_POST[ 'tstmnls_author' ] ) ) {
				$testimonials_info = array();
				$testimonials_info['author'] = esc_js( $_POST[ 'tstmnls_author' ] );
				$testimonials_info['company_name'] = esc_js( $_POST[ 'tstmnls_company_name' ] );
				/* Update the meta field in the database. */
				update_post_meta( $post_id, '_testimonials_info', $testimonials_info );
			}
		}
	}
}

if ( ! class_exists( 'Testimonials' ) ) {
	class Testimonials extends WP_Widget {

		function Testimonials() {
			/* Instantiate the parent object */
			parent::__construct( 
				'tstmnls_testimonails_widget', 
				__( 'Testimonials Widget', 'testimonials' ),
				array( 'description' => __( 'Widget for displaying Testimonials.', 'testimonials' ) )
			);
		}

		function widget( $args, $instance ) {
			global $tstmnls_options;
			if ( empty( $tstmnls_options ) )
				$tstmnls_options = get_option( 'tstmnls_options' );
			$widget_title   = isset( $instance['widget_title'] ) ? $instance['widget_title'] : $tstmnls_options['widget_title'];
			$count  		= isset( $instance['count'] ) ? $instance['count'] : $tstmnls_options['count'];
			echo $args['before_widget'];
			if ( ! empty( $widget_title ) ) { 
				echo $args['before_title'] . $widget_title . $args['after_title'];
			} 
			tstmnls_show_testimonials( $count );		
			echo $args['after_widget'];
		}

		function form( $instance ) {
			global $tstmnls_options;
			if ( empty( $tstmnls_options ) )
				$tstmnls_options = get_option( 'tstmnls_options' );
			$widget_title  	= isset( $instance['widget_title'] ) ? $instance['widget_title'] : $tstmnls_options['widget_title'];
			$count  		= isset( $instance['count'] ) ? $instance['count'] : $tstmnls_options['count']; ?>
			<p>
				<label for="<?php echo $this->get_field_id( 'widget_title' ); ?>"><?php _e( 'Widget Title', 'testimonials' ); ?>: </label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'widget_title' ); ?>" name="<?php echo $this->get_field_name( 'widget_title' ); ?>" type="text" value="<?php echo esc_attr( $widget_title ); ?>"/>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Number of testimonials to be displayed', 'testimonials' ); ?>: </label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="number" value="<?php echo esc_attr( $count ); ?>"/>
			</p>
		<?php }

		function update( $new_instance, $old_instance ) {
			global $tstmnls_options;
			if ( empty( $tstmnls_options ) )
				$tstmnls_options = get_option( 'tstmnls_options' );
			$instance = array();
			$instance['widget_title']	= ( isset( $new_instance['widget_title'] ) ) ? stripslashes( esc_html( $new_instance['widget_title'] ) ) : $tstmnls_options['widget_title'];
			$instance['count']			= ( ! empty( $new_instance['count'] ) ) ? intval( $new_instance['count'] ) : $tstmnls_options['count'];
			return $instance;
		}
	}
}

/**
 * Display Featured Post
 * @return echo Featured Post block
 */
if ( ! function_exists( 'tstmnls_show_testimonials' ) ) {
	function tstmnls_show_testimonials( $count = false ) {
		if ( ! $count ) {
			global $tstmnls_options;
			if ( empty( $tstmnls_options ) )
				$tstmnls_options = get_option( 'tstmnls_options' );
			$count = $tstmnls_options['count'];
		}
		$query_args = array(
			'post_type'			=>	'bws-testimonial',
			'post_status'		=>	'publish',
			'posts_per_page'	=>	$count
		);
		query_posts( $query_args ); ?>
		<div class="bws-testimonials">
			<?php while ( have_posts() ) {
				the_post(); 
				global $post;
				$testimonials_info = get_post_meta( $post->ID, '_testimonials_info', true ); ?>
				<div class="testimonials_quote">
					<blockquote><?php the_content(); ?></blockquote>
					<div class="testimonial_quote_footer">
						<div class="testimonial_quote_author"><?php echo $testimonials_info['author']; ?></div>
						<span><?php echo $testimonials_info['company_name']; ?></span>
					</div>
				</div>
			<?php } 
			wp_reset_query(); ?>
		</div><!-- .bws-testimonials -->
	<?php }
}

if ( ! function_exists ( 'tstmnls_admin_head' ) ) {
	function tstmnls_admin_head() {
		global $wp_version;
		if ( 3.8 > $wp_version )
			wp_enqueue_style( 'tstmnls_stylesheet', plugins_url( 'css/style_wp_before_3.8.css', __FILE__ ) );	
		else
			wp_enqueue_style( 'tstmnls_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
		
		if ( isset( $_REQUEST['page'] ) && ( 'testimonials.php' == $_REQUEST['page'] ) ) {			
			wp_enqueue_script( 'tstmnls_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ) );
		}
	}
}

if ( ! function_exists ( 'tstmnls_wp_head' ) ) {
	function tstmnls_wp_head() {
		wp_enqueue_style( 'tstmnls_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
	}
}

/**
 * Function to handle action links
 */
if ( ! function_exists( 'tstmnls_plugin_action_links' ) ) {
	function tstmnls_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin )
				$this_plugin = plugin_basename(__FILE__);

			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=testimonials.php">' . __( 'Settings', 'testimonials' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

if ( ! function_exists ( 'tstmnls_register_plugin_links' ) ) {
	function tstmnls_register_plugin_links( $links, $file ) {
		$base = plugin_basename(__FILE__);
		if ( $file == $base ) {
			if ( ! is_network_admin() )
				$links[] = '<a href="admin.php?page=testimonials.php">' . __( 'Settings','testimonials' ) . '</a>';
			$links[] = '<a href="http://wordpress.org/plugins/bws-testimonials/faq/" target="_blank">' . __( 'FAQ','testimonials' ) . '</a>';
			$links[] = '<a href="http://support.bestwebsoft.com">' . __( 'Support','testimonials' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists ( 'tstmnls_register_widgets' ) ) {
	function tstmnls_register_widgets() {
		register_widget( 'Testimonials' );
	}
}

/**
 * Delete plugin options
 */
if ( ! function_exists( 'tstmnls_plugin_uninstall' ) ) {
	function tstmnls_plugin_uninstall() {
		delete_option( 'tstmnls_options' );
	}
}

add_action( 'admin_menu', 'tstmnls_admin_menu' );
		
add_action( 'init', 'tstmnls_init' );
add_action( 'admin_init', 'tstmnls_admin_init' );
add_action( 'widgets_init', 'tstmnls_register_widgets' );

add_action( 'save_post', 'tstmnls_save_postdata' );

/* Display Featured Post */
add_action( 'tstmnls_show_testimonials', 'tstmnls_show_testimonials' );

add_shortcode( 'bws_testimonials', 'tstmnls_show_testimonials' );
/* Add style for admin page */
add_action( 'admin_enqueue_scripts', 'tstmnls_admin_head' );
add_action( 'wp_enqueue_scripts', 'tstmnls_wp_head' );

/* Additional links on the plugin page */
add_filter( 'plugin_action_links', 'tstmnls_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'tstmnls_register_plugin_links', 10, 2 );

register_uninstall_hook( __FILE__, 'tstmnls_plugin_uninstall' );