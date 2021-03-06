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

Timber::$dirname = array('./views');
Timber::$autoescape = false;

function walkative_remove_unused_scripts()
{
	wp_deregister_style('wp-block-library');
	wp_deregister_script('jquery');
	wp_deregister_script('devicepx');
	wp_deregister_style('dashicons');
}

function walkative_add_walks_to_query($query)
{
	if ($query->is_post_type_archive() && $query->is_main_query()) {
		$query->set('orderby', "meta_value");
		$query->set('meta_key', "start_date");
		$query->set('order', "DESC");
	}
	if ($query->is_tag() && $query->is_main_query()) {
		$query->set('post_type', array('post', 'event'));
	}
	if ($query->is_category() && $query->is_main_query()) {
		$query->set('post_type', array('post', 'event'));
	}
}

function walkative_add_custom_post_counts()
{
	$post_types = array('event', "contributor");
	foreach ($post_types as $pt) :
		$pt_info = get_post_type_object($pt);
		$num_posts = wp_count_posts($pt);
		$num = number_format_i18n($num_posts->publish);
		$text = _n($pt_info->labels->singular_name, $pt_info->labels->name, intval($num_posts->publish)); // singular/plural text label for CPT
		echo '<li class="page-count ' . $pt_info->name . '-count"><a href="edit.php?post_type=' . $pt . '">' . $num . ' ' . $text . '</a></li>';
	endforeach;
}

function walkative_event_column($column, $post_id)
{
	if ('subtitle' === $column) {
		echo get_post_meta($post_id, "subtitle", true);
	}
}

function walkative_filter_posts_columns($columns)
{
	$columns = array(
		'cb' => $columns['cb'],
		'title' => __('Title'),
		'subtitle' => __('Subtitle'),
		'date' => __('Date'),
		'tags' => __('Tags'),
	);
	return $columns;
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
		'core/freeform',
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
		add_action('pre_get_posts', "walkative_add_walks_to_query");
		add_action('dashboard_glance_items', 'walkative_add_custom_post_counts');

		add_filter('manage_event_posts_columns', 'walkative_filter_posts_columns');
		add_action('manage_event_posts_custom_column', 'walkative_event_column', 10, 2);

		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('wp_print_styles', 'print_emoji_styles');
		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('admin_print_styles', 'print_emoji_styles');
		parent::__construct();
	}
	public function register_post_types()
	{
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
			"post_type" => "event",
			"orderby" => "meta_value",
			"meta_key" => "start_date", // walk start
			"order" => "DESC"
		);
		$context['walks'] = Timber::get_posts($walk_args);

		$current_cs = array(
			"post_type" => "contributor",
			"meta_query" => array(
				array(
					"key" => "current",
					"value" => 1,
					"compare" => "="
				)
			)
		);
		$past_cs = array(
			"post_type" => "contributor",
			"meta_query" => array(
				array(
					"key" => "current",
					"value" => 0,
					"compare" => "="
				)
			)
		);
		$context['current_contributors'] = Timber::get_posts($current_cs);
		$context['past_contributors'] = Timber::get_posts($past_cs);
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
