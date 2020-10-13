<?php
if (!class_exists('Timber')) {

	add_action(
		'admin_notices',
		function () {
			echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url(admin_url('plugins.php#timber')) . '">' . esc_url(admin_url('plugins.php')) . '</a></p></div>';
		}
	);

	add_filter(
		'template_include',
		function ($template) {
			return dirname(get_stylesheet_directory()) . '/static/no-timber.html';
		}
	);
	return;
}

Timber::$dirname = array('../views');
Timber::$autoescape = false;

function walkative_remove_unused_scripts()
{
	wp_deregister_style('wp-block-library');
	wp_deregister_script('jquery');
	wp_deregister_script('devicepx');
	wp_deregister_style('dashicons');
}

function walkative_add_walks_to_query( $query ) {
    if ( $query->is_tag() && $query->is_main_query() ) {
        $query->set( 'post_type', array( 'post', 'walk' ) );
    }
    if ( $query->is_category() && $query->is_main_query() ) {
        $query->set( 'post_type', array( 'post', 'walk' ) );
    }
}

function walkative_allowed_block_types($allowed_block_types)
{
	return array(
		'core/paragraph',
		'core/heading',
		'core/quote',
		'core/list',
		'core/image',
		'core/gallery',
		'core/embed',
		'core/video',
		'core/html',
		'core/youtube',
		'core/facebook',
		'core/instagram',
		'core/vimeo',
		'core/verse',
	);
}
class WalkativeSite extends Timber\Site
{
	public function __construct()
	{
		add_action('after_setup_theme', array($this, 'theme_supports'));
		add_filter('timber/context', array($this, 'add_to_context'));
		add_filter('timber/twig', array($this, 'add_to_twig'));
		add_action('init', array($this, 'register_post_types'));
		add_action('init', array($this, 'register_taxonomies'));
		add_filter('allowed_block_types', 'walkative_allowed_block_types');
		add_action('wp_print_styles', 'walkative_remove_unused_scripts');
		add_action( 'pre_get_posts', "walkative_add_walks_to_query" );

		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');
		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('admin_print_styles', 'print_emoji_styles');
		parent::__construct();
	}
	public function register_post_types()
	{
		$labels = [
			"name" => __("Walks and Events", "custom-post-type-ui"),
			"singular_name" => __("Event", "custom-post-type-ui"),
		];

		$args = [
			"label" => __("Walks", "custom-post-type-ui"),
			"labels" => $labels,
			"description" => "",
			"public" => true,
			"publicly_queryable" => true,
			"show_ui" => true,
			"show_in_rest" => true,
			"rest_base" => "",
			"rest_controller_class" => "WP_REST_Posts_Controller",
			"has_archive" => true,
			"show_in_menu" => true,
			"show_in_nav_menus" => true,
			"delete_with_user" => false,
			"exclude_from_search" => false,
			"capability_type" => "post",
			"map_meta_cap" => true,
			"hierarchical" => false,
			"rewrite" => ["slug" => "walk", "with_front" => true],
			"query_var" => true,
			"supports" => ["title", "editor", "author", "thumbnail"],
			"taxonomies" => ["post_tag"],
		];
		register_post_type("walk", $args);
	}
	/** This is where you can register custom taxonomies. */
	public function register_taxonomies()
	{
	}

	/** This is where you add some context
	 *
	 * @param string $context context['this'] Being the Twig's {{ this }}.
	 */
	public function add_to_context($context)
	{
		$context['menu']  = new Timber\Menu();
		$context['site']  = $this;
		$context['isHome']  = is_home();
		$context['search_form'] = get_search_form(false);

		$posts_args = array(
			"post_type" => "post",
		);

		$context['home_posts'] = Timber::get_posts($posts_args);
		$walk_args = array(
			"post_type" => "walk",
			"orderby" => "meta_value_date",
			"meta_key" => "start_date", // walk start
			"order" => "ASC"
		);
		$context['walks'] = Timber::get_posts($walk_args);
		return $context;
	}

	public function theme_supports()
	{
		add_theme_support('automatic-feed-links');
		add_theme_support('title-tag');
		add_theme_support('post-thumbnails');
		add_theme_support('menus');
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);
	}
	/** This is where you can add your own functions to twig.
	 *
	 * @param string $twig get extension.
	 */
	public function add_to_twig($twig)
	{
		$twig->addExtension(new Twig\Extension\StringLoaderExtension());
		return $twig;
	}
}

new WalkativeSite();
