<?php

/**
 * BuddyPress Groups Widgets
 *
 * @package BuddyPress
 * @subpackage GroupsWidgets
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* Register widgets for groups component */
function cc_groups_by_cat_register_widgets() {
	add_action('widgets_init', create_function('', 'return register_widget("CC_Groups_by_Category_Widget");') );
}
add_action( 'bp_register_widgets', 'cc_groups_by_cat_register_widgets' );

/*** GROUPS WIDGET *****************/

class CC_Groups_by_Category_Widget extends WP_Widget {
	function __construct() {
		$widget_ops = array(
			'description' => __( 'A list of groups that are tagged with a category name', 'cc-groups-by-category' ),
			'classname' => 'widget_bp_groups_widget buddypress widget',
		);
		parent::__construct( false, _x( '(BuddyPress) Groups by Category', 'widget name', 'cc-groups-by-category' ), $widget_ops );

		// if ( is_active_widget( false, false, $this->id_base ) && !is_admin() && !is_network_admin() ) {
		// 	$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		// 	wp_enqueue_script( 'groups_widget_groups_list-js', buddypress()->plugin_url . "bp-groups/js/widget-groups{$min}.js", array( 'jquery' ), bp_get_version() );
		// }
	}

	/**
	 * PHP4 constructor
	 *
	 * For backward compatibility only
	 */
	function bp_groups_widget() {
		$this->_construct();
	}

	function widget( $args, $instance ) {
		$user_id = apply_filters( 'bp_group_widget_user_id', '0' );

		extract( $args );

		if ( empty( $instance['title'] ) )
			$instance['title'] = __( 'Groups by Channel', 'buddypress' );

		// What term shall we show?
		$term = get_queried_object();
		$term_id = ( $term ) ? $term->term_id : 0;

		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;

		$title = !empty( $instance['link_title'] ) ? '<a href="' . trailingslashit( bp_get_root_domain() . '/' . bp_get_groups_root_slug() ) . '">' . $title . '</a>' : $title;

		echo $before_title . $title . $after_title;

		$featured_group_args = array(
			'user_id'       => $user_id,
			'per_page'      => $instance['max_groups'],
			'max'           => $instance['max_groups'],
			'meta_query'	=> array(
					                array(
					                	'key'     => 'cc_group_is_featured',
						                'compare' => 'EXISTS'
						                ),
									),
		);

		// If this is a category page, show groups tagged with that category.
		if ( $term_id ) {
            $featured_group_args['meta_query'][] = array(
                'key'     => 'cc_group_category',
                'value'   => $term_id,
                'type'    => 'numeric',
                'compare' => '='
            );
        }
        $exclude_groups = array();
		?>

		<?php if ( bp_has_groups( $featured_group_args ) ) : ?>
			<ul id="groups-list" class="item-list">
				<?php while ( bp_groups() ) : bp_the_group(); ?>
					<li <?php bp_group_class(); ?>>
						<div class="item-avatar">
							<a href="<?php bp_group_permalink() ?>" title="<?php bp_group_name() ?>"><?php bp_group_avatar_thumb() ?></a>
						</div>

						<div class="item">
							<div class="item-title"><a href="<?php bp_group_permalink() ?>" title="<?php bp_group_name() ?>"><?php bp_group_name() ?></a></div>
							<div class="item-meta">
								<span class="activity">
								<?php printf( __( 'active %s', 'buddypress' ), bp_get_group_last_active() ); ?>
								</span>
							</div>
						</div>
					</li>

				<?php
				$exclude_groups[] = bp_get_group_id();
				endwhile;
				// Next we show non-featured groups in this term.
				if ( $term_id ) :
					$category_group_args = array(
						'user_id'       => $user_id,
						'per_page'      => $instance['max_groups'] - count( $exclude_groups ),
						'max'           => $instance['max_groups'] - count( $exclude_groups ),
						'exclude'		=> $exclude_groups,
						'meta_query'	=> array(
								                array(
									                'key'     => 'cc_group_category',
									                'value'   => $term_id,
									                'type'    => 'numeric',
									                'compare' => '='
									            ),
											),
					);

					if ( bp_has_groups( $category_group_args ) ) :
						while ( bp_groups() ) : bp_the_group(); ?>
							<li <?php bp_group_class(); ?>>
								<div class="item-avatar">
									<a href="<?php bp_group_permalink() ?>" title="<?php bp_group_name() ?>"><?php bp_group_avatar_thumb() ?></a>
								</div>

								<div class="item">
									<div class="item-title"><a href="<?php bp_group_permalink() ?>" title="<?php bp_group_name() ?>"><?php bp_group_name() ?></a></div>
									<div class="item-meta">
										<span class="activity">
										<?php printf( __( 'active %s', 'buddypress' ), bp_get_group_last_active() ); ?>
										</span>
									</div>
								</div>
							</li>

						<?php endwhile;
					endif; // if ( bp_has_groups( $category_group_args ) )
				endif; // if ( $term_id ) :
				?>
			</ul>
			<?php wp_nonce_field( 'groups_widget_groups_list', '_wpnonce-groups' ); ?>
			<input type="hidden" name="groups_widget_max" id="groups_widget_max" value="<?php echo esc_attr( $instance['max_groups'] ); ?>" />

		<?php else: ?>

			<div class="widget-error">
				<?php _e('No matching groups found.', 'buddypress') ?>
			</div>

		<?php endif; ?>

		<?php echo $after_widget; ?>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']         = strip_tags( $new_instance['title'] );
		$instance['max_groups']    = strip_tags( $new_instance['max_groups'] );
		$instance['link_title']    = (bool)$new_instance['link_title'];

		return $instance;
	}

	function form( $instance ) {
		$defaults = array(
			'title'         => __( 'Groups by Channel', 'buddypress' ),
			'max_groups'    => 5,
			'group_default' => 'active',
			'link_title'    => false
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		$title 	       = strip_tags( $instance['title'] );
		$max_groups    = strip_tags( $instance['max_groups'] );
		$link_title    = (bool)$instance['link_title'];
		?>

		<p><label for="bp-groups-widget-title"><?php _e('Title:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" style="width: 100%" /></label></p>

		<p><label for="<?php echo $this->get_field_name('link_title') ?>"><input type="checkbox" name="<?php echo $this->get_field_name('link_title') ?>" value="1" <?php checked( $link_title ) ?> /> <?php _e( 'Link widget title to Groups directory', 'buddypress' ) ?></label></p>

		<p><label for="bp-groups-widget-groups-max"><?php _e('Max groups to show:', 'buddypress'); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'max_groups' ); ?>" name="<?php echo $this->get_field_name( 'max_groups' ); ?>" type="text" value="<?php echo esc_attr( $max_groups ); ?>" style="width: 30%" /></label></p>
	<?php
	}
}

function cc_groups_ajax_widget_groups_list() {

	check_ajax_referer('groups_widget_groups_list');

	switch ( $_POST['filter'] ) {
		case 'newest-groups':
			$type = 'newest';
		break;
		case 'recently-active-groups':
			$type = 'active';
		break;
		case 'popular-groups':
			$type = 'popular';
		break;
	}

	$per_page = isset( $_POST['max_groups'] ) ? intval( $_POST['max_groups'] ) : 5;

	$groups_args = array(
		'user_id'  => 0,
		'type'     => $type,
		'per_page' => $per_page,
		'max'      => $per_page,
	);

	if ( bp_has_groups( $groups_args ) ) : ?>
		<?php echo "0[[SPLIT]]"; ?>
		<?php while ( bp_groups() ) : bp_the_group(); ?>
			<li <?php bp_group_class(); ?>>
				<div class="item-avatar">
					<a href="<?php bp_group_permalink() ?>"><?php bp_group_avatar_thumb() ?></a>
				</div>

				<div class="item">
					<div class="item-title"><a href="<?php bp_group_permalink() ?>" title="<?php bp_group_name() ?>"><?php bp_group_name() ?></a></div>
					<div class="item-meta">
						<span class="activity">
							<?php
							if ( 'newest-groups' == $_POST['filter'] ) {
								printf( __( 'created %s', 'buddypress' ), bp_get_group_date_created() );
							} else if ( 'recently-active-groups' == $_POST['filter'] ) {
								printf( __( 'active %s', 'buddypress' ), bp_get_group_last_active() );
							} else if ( 'popular-groups' == $_POST['filter'] ) {
								bp_group_member_count();
							}
							?>
						</span>
					</div>
				</div>
			</li>
		<?php endwhile; ?>

		<?php wp_nonce_field( 'groups_widget_groups_list', '_wpnonce-groups' ); ?>
		<input type="hidden" name="groups_widget_max" id="groups_widget_max" value="<?php echo esc_attr( $_POST['max_groups'] ); ?>" />

	<?php else: ?>

		<?php echo "-1[[SPLIT]]<li>" . __("No groups matched the current filter.", 'buddypress'); ?>

	<?php endif;

}
// add_action( 'wp_ajax_widget_groups_list',        'groups_ajax_widget_groups_list' );
// add_action( 'wp_ajax_nopriv_widget_groups_list', 'groups_ajax_widget_groups_list' );
