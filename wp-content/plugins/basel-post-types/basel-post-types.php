<?php 
/*
Plugin Name: XTEMOS Post Types plugin
Description: Register post types needed for XTEMOS themes
Version: 1.7
Text Domain: basel_post_types
*/

class BASEL_Post_Types {

	public $domain = 'basel_starter';

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'basel' ), '2.1' );
	}

	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'basel' ), '2.1' );
	}

	public function __construct() {
		
		// Hook into the 'init' action
		add_action( 'init', array($this, 'register_blocks'), 1 );
		add_action( 'init', array($this, 'size_guide'), 1 );
		add_action( 'init', array($this, 'slider'), 1 );

		// Duplicate post action for slides
		add_filter( 'post_row_actions', array($this, 'duplicate_slide_action'), 10, 2 );
		add_filter( 'admin_action_basel_duplicate_post_as_draft', array($this, 'duplicate_post_as_draft'), 10, 2 );

		// Manage slides list columns
		add_filter( 'manage_edit-basel_slide_columns', array($this, 'edit_basel_slide_columns') ) ;
		add_action( 'manage_basel_slide_posts_custom_column', array($this, 'manage_basel_slide_columns'), 10, 2 );

		// Add shortcode column to block list
		add_filter( 'manage_edit-cms_block_columns', array($this, 'edit_html_blocks_columns') ) ;
		add_action( 'manage_cms_block_posts_custom_column', array($this, 'manage_html_blocks_columns'), 10, 2 );

		add_filter( 'manage_edit-portfolio_columns', array($this, 'edit_portfolio_columns') ) ;
		add_action( 'manage_portfolio_posts_custom_column', array($this, 'manage_portfolio_columns'), 10, 2 );

		add_action( 'init', array($this, 'register_sidebars'), 1 );
		add_action( 'init', array($this, 'register_portfolio'), 1 );

	}
		
	// **********************************************************************// 
	// ! Register Custom Post Type for Basel slider
	// **********************************************************************// 
	public function slider() {
		
		if ( function_exists( 'basel_get_opt' ) && ! basel_get_opt( 'basel_slider' ) ) return;

		$labels = array(
			'name'                => esc_html__( 'Basel Slider', 'basel' ),
			'singular_name'       => esc_html__( 'Slide', 'basel' ),
			'menu_name'           => esc_html__( 'Slides', 'basel' ),
			'parent_item_colon'   => esc_html__( 'Parent Item:', 'basel' ),
			'all_items'           => esc_html__( 'All Items', 'basel' ),
			'view_item'           => esc_html__( 'View Item', 'basel' ),
			'add_new_item'        => esc_html__( 'Add New Item', 'basel' ),
			'add_new'             => esc_html__( 'Add New', 'basel' ),
			'edit_item'           => esc_html__( 'Edit Item', 'basel' ),
			'update_item'         => esc_html__( 'Update Item', 'basel' ),
			'search_items'        => esc_html__( 'Search Item', 'basel' ),
			'not_found'           => esc_html__( 'Not found', 'basel' ),
			'not_found_in_trash'  => esc_html__( 'Not found in Trash', 'basel' ),
		);

		$args = array(
			'label'               => 'basel_slide',
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 29,
			'menu_icon'           => 'dashicons-images-alt2',
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'page',
		);

		register_post_type( 'basel_slide', $args );

		$labels = array(
			'name'					=> esc_html__( 'Sliders', 'basel' ),
			'singular_name'			=> esc_html__( 'Slider', 'basel' ),
			'search_items'			=> esc_html__( 'Search Sliders', 'basel' ),
			'popular_items'			=> esc_html__( 'Popular Sliders', 'basel' ),
			'all_items'				=> esc_html__( 'All Sliders', 'basel' ),
			'parent_item'			=> esc_html__( 'Parent Slider', 'basel' ),
			'parent_item_colon'		=> esc_html__( 'Parent Slider', 'basel' ),
			'edit_item'				=> esc_html__( 'Edit Slider', 'basel' ),
			'update_item'			=> esc_html__( 'Update Slider', 'basel' ),
			'add_new_item'			=> esc_html__( 'Add New Slider', 'basel' ),
			'new_item_name'			=> esc_html__( 'New Slide', 'basel' ),
			'add_or_remove_items'	=> esc_html__( 'Add or remove Sliders', 'basel' ),
			'choose_from_most_used'	=> esc_html__( 'Choose from most used sliders', 'basel' ),
			'menu_name'				=> esc_html__( 'Slider', 'basel' ),
		);
	
		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_admin_column' => false,
			'hierarchical'      => true,
			'show_tagcloud'     => false,
			'show_ui'           => true,
			'query_var'         => false,
			'rewrite'           => false,
			'query_var'         => false,
			'capabilities'      => array(),
		);
	
		register_taxonomy( 'basel_slider', array( 'basel_slide' ), $args );
	}

	public function duplicate_slide_action( $actions, $post ) {
		if( $post->post_type != 'basel_slide' ) return $actions;

		if (current_user_can('edit_posts')) {
			$actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=basel_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
		}
		return $actions;
	}

	public function duplicate_post_as_draft() {
		global $wpdb;
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'basel_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
			wp_die('No post to duplicate has been supplied!');
		}
	 
		/*
		 * Nonce verification
		 */
		if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) )
			return;
	 
		/*
		 * get the original post id
		 */
		$post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
		/*
		 * and all the original post data then
		 */
		$post = get_post( $post_id );
	 
		/*
		 * if you don't want current user to be the new post author,
		 * then change next couple of lines to this: $new_post_author = $post->post_author;
		 */
		$current_user = wp_get_current_user();
		$new_post_author = $current_user->ID;
	 
		/*
		 * if post data exists, create the post duplicate
		 */
		if (isset( $post ) && $post != null) {
	 
			/*
			 * new post data array
			 */
			$args = array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $new_post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'draft',
				'post_title'     => $post->post_title . ' (duplicate)',
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			);
	 
			/*
			 * insert the post by wp_insert_post() function
			 */
			$new_post_id = wp_insert_post( $args );
	 
			/*
			 * get all current post terms ad set them to the new post draft
			 */
			$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
			foreach ($taxonomies as $taxonomy) {
				$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
				wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
			}
	 
			/*
			 * duplicate all post meta just in two SQL queries
			 */
			$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
			if (count($post_meta_infos)!=0) {
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
				foreach ($post_meta_infos as $meta_info) {
					$meta_key = $meta_info->meta_key;
					if( $meta_key == '_wp_old_slug' ) continue;
					$meta_value = addslashes($meta_info->meta_value);
					$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
				}
				$sql_query.= implode(" UNION ALL ", $sql_query_sel);
				$wpdb->query($sql_query);
			}
	 
	 
			/*
			 * finally, redirect to the edit post screen for the new draft
			 */
			wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
			exit;
		} else {
			wp_die('Post creation failed, could not find original post: ' . $post_id);
		}
	}

	public function edit_basel_slide_columns( $columns ) {

		$columns = array(
			'cb' => '<input type="checkbox" />',
			'thumb' => '',
			'title' => esc_html__( 'Title', 'basel' ),
			'slide-slider' => esc_html__( 'Slider', 'basel' ),	   
			'date' => esc_html__( 'Date', 'basel' ),
		);

		return $columns;
	}


	public function manage_basel_slide_columns($column, $post_id) {
		switch( $column ) {
			case 'thumb' :
				if( has_post_thumbnail( $post_id ) ) {
					the_post_thumbnail( array(60,60) );
				}
			break;
			case 'slide-slider' :
				$terms = get_the_terms( $post_id, 'basel_slider' );
										
				if ( $terms && ! is_wp_error( $terms ) ) : 

					$cats_links = array();

					foreach ( $terms as $term ) {
						$cats_links[] = $term->name;
					}
										
					$cats = join( ", ", $cats_links );
				?>

				<span><?php echo $cats; ?></span>

				<?php endif; 
			break;
		}	
	}

	// **********************************************************************// 
	// ! Register Custom Post Type for Size Guide
	// **********************************************************************// 
	public function size_guide() {
		
		if ( function_exists( 'basel_get_opt' ) && !basel_get_opt( 'size_guides' ) ) return;

		$labels = array(
			'name'                => esc_html__( 'Size Guides', 'basel' ),
			'singular_name'       => esc_html__( 'Size Guide', 'basel' ),
			'menu_name'           => esc_html__( 'Size Guides', 'basel' ),
			'add_new'             => esc_html__( 'Add new', 'basel' ),
			'add_new_item'        => esc_html__( 'Add new size guide', 'basel' ),
			'new_item'            => esc_html__( 'New size guide', 'basel' ),
			'edit_item'           => esc_html__( 'Edit size guide', 'basel' ),
			'view_item'           => esc_html__( 'View size guide', 'basel' ),
			'all_items'           => esc_html__( 'All size guides', 'basel' ),
			'search_items'        => esc_html__( 'Search size guides', 'basel' ),
			'not_found'           => esc_html__( 'No size guides found.', 'basel' ),
			'not_found_in_trash'  => esc_html__( 'No size guides found in trash.', 'basel' )
		);

		$args = array(
			'label'               => esc_html__( 'basel_size_guide', 'basel' ),
			'description'         => esc_html__( 'Size guide to place in your products', 'basel' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 29,
			'menu_icon'           => 'dashicons-editor-kitchensink',
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'rewrite'             => false,
			'capability_type'     => 'page',
		);

		register_post_type( 'basel_size_guide', $args );
	}

	public function register_blocks() {

		$labels = array(
			'name'                => esc_html__( 'HTML Blocks', 'basel' ),
			'singular_name'       => esc_html__( 'HTML Block', 'basel' ),
			'menu_name'           => esc_html__( 'HTML Blocks', 'basel' ),
			'parent_item_colon'   => esc_html__( 'Parent Item:', 'basel' ),
			'all_items'           => esc_html__( 'All Items', 'basel' ),
			'view_item'           => esc_html__( 'View Item', 'basel' ),
			'add_new_item'        => esc_html__( 'Add New Item', 'basel' ),
			'add_new'             => esc_html__( 'Add New', 'basel' ),
			'edit_item'           => esc_html__( 'Edit Item', 'basel' ),
			'update_item'         => esc_html__( 'Update Item', 'basel' ),
			'search_items'        => esc_html__( 'Search Item', 'basel' ),
			'not_found'           => esc_html__( 'Not found', 'basel' ),
			'not_found_in_trash'  => esc_html__( 'Not found in Trash', 'basel' ),
		);

		$args = array(
			'label'               => esc_html__( 'cms_block', 'basel' ),
			'description'         => esc_html__( 'CMS Blocks for custom HTML to place in your pages', 'basel' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 29,
			'menu_icon'           => 'dashicons-schedule',
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'rewrite'             => false,
			'capability_type'     => 'page',
		);

		register_post_type( 'cms_block', $args );

	}


	public function edit_html_blocks_columns( $columns ) {

		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => esc_html__( 'Title', 'basel' ),
			'shortcode' => esc_html__( 'Shortcode', 'basel' ),	   
			'date' => esc_html__( 'Date', 'basel' ),
		);

		return $columns;
	}


	public function manage_html_blocks_columns($column, $post_id) {
		switch( $column ) {
			case 'shortcode' :
				echo '<strong>[html_block id="'.$post_id.'"]</strong>';
			break;
		}	
	}

	// **********************************************************************// 
	// ! Register Custom Post Type for additional sidebars
	// **********************************************************************// 
	public function register_sidebars() {

		$labels = array(
			'name'                => esc_html__( 'Sidebars', 'basel' ),
			'singular_name'       => esc_html__( 'Sidebar', 'basel' ),
			'menu_name'           => esc_html__( 'Sidebars', 'basel' ),
			'parent_item_colon'   => esc_html__( 'Parent Item:', 'basel' ),
			'all_items'           => esc_html__( 'All Items', 'basel' ),
			'view_item'           => esc_html__( 'View Item', 'basel' ),
			'add_new_item'        => esc_html__( 'Add New Item', 'basel' ),
			'add_new'             => esc_html__( 'Add New', 'basel' ),
			'edit_item'           => esc_html__( 'Edit Item', 'basel' ),
			'update_item'         => esc_html__( 'Update Item', 'basel' ),
			'search_items'        => esc_html__( 'Search Item', 'basel' ),
			'not_found'           => esc_html__( 'Not found', 'basel' ),
			'not_found_in_trash'  => esc_html__( 'Not found in Trash', 'basel' ),
		);

		$args = array(
			'label'               => esc_html__( 'basel_sidebar', 'basel' ),
			'description'         => esc_html__( 'You can create additional custom sidebar and use them in Visual Composer', 'basel' ),
			'labels'              => $labels,
			'supports'            => array( 'title' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 67,
			'menu_icon'           => 'dashicons-welcome-widgets-menus',
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'rewrite'             => false,
			'capability_type'     => 'page',
		);

		register_post_type( 'basel_sidebar', $args );

	}



	// **********************************************************************// 
	// ! Register Custom Post Type for portfolio
	// **********************************************************************// 
	public function register_portfolio() {

		if ( function_exists( 'basel_get_opt' ) && basel_get_opt( 'disable_portfolio' ) ) return;

		$labels = array(
			'name'                => esc_html__( 'Portfolio', 'basel' ),
			'singular_name'       => esc_html__( 'Project', 'basel' ),
			'menu_name'           => esc_html__( 'Projects', 'basel' ),
			'parent_item_colon'   => esc_html__( 'Parent Item:', 'basel' ),
			'all_items'           => esc_html__( 'All Items', 'basel' ),
			'view_item'           => esc_html__( 'View Item', 'basel' ),
			'add_new_item'        => esc_html__( 'Add New Item', 'basel' ),
			'add_new'             => esc_html__( 'Add New', 'basel' ),
			'edit_item'           => esc_html__( 'Edit Item', 'basel' ),
			'update_item'         => esc_html__( 'Update Item', 'basel' ),
			'search_items'        => esc_html__( 'Search Item', 'basel' ),
			'not_found'           => esc_html__( 'Not found', 'basel' ),
			'not_found_in_trash'  => esc_html__( 'Not found in Trash', 'basel' ),
		);

		$args = array(
			'label'               => esc_html__( 'portfolio', 'basel' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 28,
			'menu_icon'           => 'dashicons-format-gallery',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'rewrite'             => array('slug' => 'portfolio'),
			'capability_type'     => 'page',
		);

		register_post_type( 'portfolio', $args );

		/**
		 * Create a taxonomy category for portfolio
		 *
		 * @uses  Inserts new taxonomy object into the list
		 * @uses  Adds query vars
		 *
		 * @param string  Name of taxonomy object
		 * @param array|string  Name of the object type for the taxonomy object.
		 * @param array|string  Taxonomy arguments
		 * @return null|WP_Error WP_Error if errors, otherwise null.
		 */
		
		$labels = array(
			'name'					=> esc_html__( 'Project Categories', 'basel' ),
			'singular_name'			=> esc_html__( 'Project Category', 'basel' ),
			'search_items'			=> esc_html__( 'Search Categories', 'basel' ),
			'popular_items'			=> esc_html__( 'Popular Project Categories', 'basel' ),
			'all_items'				=> esc_html__( 'All Project Categories', 'basel' ),
			'parent_item'			=> esc_html__( 'Parent Category', 'basel' ),
			'parent_item_colon'		=> esc_html__( 'Parent Category', 'basel' ),
			'edit_item'				=> esc_html__( 'Edit Category', 'basel' ),
			'update_item'			=> esc_html__( 'Update Category', 'basel' ),
			'add_new_item'			=> esc_html__( 'Add New Category', 'basel' ),
			'new_item_name'			=> esc_html__( 'New Category', 'basel' ),
			'add_or_remove_items'	=> esc_html__( 'Add or remove Categories', 'basel' ),
			'choose_from_most_used'	=> esc_html__( 'Choose from most used text-domain', 'basel' ),
			'menu_name'				=> esc_html__( 'Category', 'basel' ),
		);
	
		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'show_in_nav_menus' => true,
			'show_admin_column' => false,
			'hierarchical'      => true,
			'show_tagcloud'     => true,
			'show_ui'           => true,
			'query_var'         => true,
			'rewrite'           => true,
			'query_var'         => true,
			'capabilities'      => array(),
		);
	
		register_taxonomy( 'project-cat', array( 'portfolio' ), $args );

	}


	public function edit_portfolio_columns( $columns ) {

		$columns = array(
			'cb' => '<input type="checkbox" />',
			'thumb' => '',
			'title' => esc_html__( 'Title', 'basel' ),
			'project-cat' => esc_html__( 'Categories', 'basel' ),	   
			'date' => esc_html__( 'Date', 'basel' ),
		);

		return $columns;
	}


	public function manage_portfolio_columns($column, $post_id) {
		switch( $column ) {
			case 'thumb' :
				if( has_post_thumbnail( $post_id ) ) {
					the_post_thumbnail( array(60,60) );
				}
			break;
			case 'project-cat' :
				$terms = get_the_terms( $post_id, 'project-cat' );
										
				if ( $terms && ! is_wp_error( $terms ) ) : 

					$cats_links = array();

					foreach ( $terms as $term ) {
						$cats_links[] = $term->name;
					}
										
					$cats = join( ", ", $cats_links );
				?>

				<span><?php echo $cats; ?></span>

				<?php endif; 
			break;
		}	
	}

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}


}

function BASEL_Theme_Plugin() {
	return BASEL_Post_Types::instance();
}

$GLOBALS['basel_theme_plugin'] = BASEL_Theme_Plugin();

require_once 'vendor/opauth/twitteroauth/twitteroauth.php';
require_once 'inc/auth.php';
require_once 'inc/functions.php';

?>
