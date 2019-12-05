<?php
/*
 * Plugin name: Contextual Adminbar Color
 * Description: Use custom admin bar colors and favicons to differentiate your environments (staging/prod)
 * Plugin URI: https://jeanbaptisteaudras.com/en/contextual-adminbar-color-wordpress
 * Requires at least: 5.3
 * Requires PHP: 5.6
 * Author: whodunitagency, audrasjb
 * Author URI: https://jeanbaptisteaudras.com
 * Version: 0.3
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text-domain: contextual-adminbar-color
 * Contributors: juliobox
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Something went wrong.' );
}

define( 'CAC_PLUGIN_FILE',    __FILE__ );
define( 'CAC_PLUGIN_SLUG',    'contextual-adminbar-color' );
define( 'CAC_PLUGIN_SETTING', 'contextual-adminbar-color_setting' );
define( 'CAC_PLUGIN_PATH',    plugin_dir_path( CAC_PLUGIN_FILE ) );
define( 'CAC_PLUGIN_DURL',    plugin_dir_url( CAC_PLUGIN_FILE ) );


function contextual_adminbar_color_enqueue_adminbar_color( $hook ) {
	if ( ! contextual_adminbar_color_is_user_role_authorized() || ! is_admin_bar_showing() ) {
		return;
	}
	$chosen_color = contextual_adminbar_color_get_the_chosen_color();
	$color_slug   = isset( $chosen_color['slug'] ) ? sanitize_file_name( $chosen_color['slug'] ) : '';
	if ( file_exists( CAC_PLUGIN_PATH . '/css/' . $color_slug . '.css' ) ) {
		wp_register_style( CAC_PLUGIN_SLUG . $color_slug, CAC_PLUGIN_DURL . '/css/' . $color_slug . '.css' );
		wp_enqueue_style( CAC_PLUGIN_SLUG . $color_slug );
		wp_register_style( CAC_PLUGIN_SLUG . 'base', CAC_PLUGIN_DURL . '/css/custom.css' );
		wp_enqueue_style( CAC_PLUGIN_SLUG . 'base' );
	}
}
add_action( 'admin_enqueue_scripts', 'contextual_adminbar_color_enqueue_adminbar_color' );
add_action( 'wp_enqueue_scripts', 'contextual_adminbar_color_enqueue_adminbar_color' );

function contextual_adminbar_color_add_body_class( $classes ) {
	if ( ! contextual_adminbar_color_is_user_role_authorized() || ! is_admin_bar_showing() ) {
		return $classes;
	}
	$chosen_color = contextual_adminbar_color_get_the_chosen_color();
	$color_slug   = isset( $chosen_color['slug'] ) ? sanitize_file_name( $chosen_color['slug'] ) : '';
	if ( file_exists( CAC_PLUGIN_PATH . '/css/' . $color_slug . '.css' ) ) {
		if ( 'body_class' === current_filter() ) {
		$classes[] = CAC_PLUGIN_SLUG;
		} else {
			$classes .= ' ' . CAC_PLUGIN_SLUG;
		}
	}
	return $classes;
}
add_filter( 'admin_body_class', 'contextual_adminbar_color_add_body_class' );
add_filter( 'body_class', 'contextual_adminbar_color_add_body_class' );


function contextual_adminbar_color_add_admin_bar_text( $wp_admin_bar ) {
	if ( ! contextual_adminbar_color_is_user_role_authorized() || ! is_admin_bar_showing() ) {
		return $classes;
	}
	$chosen_color  = contextual_adminbar_color_get_the_chosen_color();
	$color_message = wp_strip_all_tags( $chosen_color['message'] );
	if ( ! empty( $chosen_color['message'] ) ) {
		$args = [
			'parent' => 'top-secondary' ,
			'id'	 => CAC_PLUGIN_SLUG . '-message',
			'title'  => $color_message,
		];
		$wp_admin_bar->add_node( $args );
	}
}
add_action( 'admin_bar_menu', 'contextual_adminbar_color_add_admin_bar_text' );

function contextual_adminbar_color_add_favicon( $url ) {
	if ( ! is_admin() || ! contextual_adminbar_color_is_user_role_authorized() ) {
		return $url;
	}
	$chosen_color         = contextual_adminbar_color_get_the_chosen_color();
	$chosen_color['slug'] = sanitize_file_name( $chosen_color['slug'] );
	if ( $chosen_color['favicon'] ) {
    	$url = CAC_PLUGIN_DURL . 'images/favicons/favicon-' . $chosen_color['slug'] . '.ico';
	}
	return $url;
}
add_action( 'get_site_icon_url', 'contextual_adminbar_color_add_favicon', 20 );

function contextual_adminbar_color_is_user_role_authorized() {
	if ( ! is_user_logged_in() ) {
		return true;
	}
	$user     = wp_get_current_user();
	$settings = get_option( CAC_PLUGIN_SETTING );
	return ! empty( array_intersect( $settings['roles'], $user->roles ) );
}

function contextual_adminbar_color_get_the_chosen_color() {
	$settings = get_option( CAC_PLUGIN_SETTING );
	$slug     = sanitize_key( $settings['slug'] );
	$message  = wp_strip_all_tags( $settings['message'] );
	$favicon  = (bool) $settings['favicon'];

	if ( defined( 'CONTEXTUAL_ADMINBAR_COLOR' ) && ! empty( CONTEXTUAL_ADMINBAR_COLOR ) ) {
		$slug = sanitize_key( CONTEXTUAL_ADMINBAR_COLOR );
	}

	if ( defined( 'CONTEXTUAL_ADMINBAR_FAVICON' ) && ! empty( CONTEXTUAL_ADMINBAR_FAVICON ) ) {
		$favicon = (bool) CONTEXTUAL_ADMINBAR_FAVICON;
	}

	if ( defined( 'CONTEXTUAL_ADMINBAR_MESSAGE' ) && ! empty( CONTEXTUAL_ADMINBAR_MESSAGE ) ) {
		$message = wp_strip_all_tags( CONTEXTUAL_ADMINBAR_MESSAGE );
	}

	$chosen_color = [
		'slug'	  => $slug,
		'message' => $message,
		'favicon' => $favicon,
	];

	return $chosen_color;
}

function contextual_adminbar_color_get_colors() {
	global $_wp_admin_css_colors;

	$current_color = get_user_option( 'admin_color', wp_get_current_user()->ID );
	if ( empty( $current_color ) || ! isset( $_wp_admin_css_colors[ $current_color ] ) ) {
		$current_color = 'fresh';
	}
	$current_color = $_wp_admin_css_colors[ $current_color ];
	$colors = [	'acs'      => [ 'title' => sprintf( '%s <em>(%s)</em>', __( 'Admin Color Scheme', 'contextual-adminbar-color' ), $current_color->name ), 'text' => $current_color->colors[3], 'text-secondary' => $current_color->colors[2], 'background' => $current_color->colors[0], 'background-secondary' => $current_color->colors[1] ],
				'blue'     => [ 'title' => _x( 'Blue', 'just the color', 'contextual-adminbar-color' ),      'text' => '#FFF', 'text-secondary' => '#E2ECf1', 'background' => '#347EA4', 'background-secondary' => '#4796B3' ],
				'red'      => [ 'title' => _x( 'Red', 'just the color', 'contextual-adminbar-color' ),       'text' => '#FFF', 'text-secondary' => '#F7E3D3', 'background' => '#CF4845', 'background-secondary' => '#BE3631' ],
				'green'    => [ 'title' => _x( 'Green', 'just the color', 'contextual-adminbar-color' ),     'text' => '#FFF', 'text-secondary' => '#D2DCCE', 'background' => '#6B8E23', 'background-secondary' => '#4F5F28' ],
				'purple'   => [ 'title' => _x( 'Purple', 'just the color', 'contextual-adminbar-color' ),    'text' => '#FFF', 'text-secondary' => '#C1AAFC', 'background' => '#483D8B', 'background-secondary' => '#1C0E54' ],
				'orange'   => [ 'title' => _x( 'Orange', 'just the color', 'contextual-adminbar-color' ),    'text' => '#FFF', 'text-secondary' => '#F1EAE2', 'background' => '#DF8836', 'background-secondary' => '#E47817' ],
				'darkgray' => [ 'title' => _x( 'Dark Gray', 'just the color', 'contextual-adminbar-color' ), 'text' => '#FFF', 'text-secondary' => '#DCDCDC', 'background' => '#797676', 'background-secondary' => '#6C5353' ],
			];
	return $colors;
}

function contextual_adminbar_color_submenu_page() {
	if ( defined( 'CONTEXTUAL_ADMINBAR_SETTINGS' ) && false === CONTEXTUAL_ADMINBAR_SETTINGS ) {
		return;
	}
	add_management_page( esc_html__( 'Contextual Adminbar Settings', 'contextual-adminbar-color' ), esc_html__( 'Contextual Adminbar Settings', 'contextual-adminbar-color' ), 'manage_options', CAC_PLUGIN_SLUG, 'contextual_adminbar_color_submenu_page_callback' );
}
add_action( 'admin_menu', 'contextual_adminbar_color_submenu_page' );

function contextual_adminbar_color_link( $actions ) {
	$actions[ CAC_PLUGIN_SLUG ] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'tools.php?page=' . CAC_PLUGIN_SLUG ) ), __( 'Settings' ) );
	return $actions;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'contextual_adminbar_color_link' );

function contextual_adminbar_color_default_settings() {
	return [ 'message' => '', 'slug' => 'acs', 'roles' => [ 'administrator' ], 'favicon' => false ];
}
function contextual_adminbar_color_register_settings() {
	register_setting( 		CAC_PLUGIN_SLUG,
							CAC_PLUGIN_SETTING,
							[   'sanitize_callback' => 'contextual_adminbar_color_setting_fields_sanitize',
								'show_in_rest'      => false,
								'default'           => contextual_adminbar_color_default_settings()
							]
						);
	add_settings_section(   CAC_PLUGIN_SLUG . '-section',
							'',
							'__return_empty_string',
							CAC_PLUGIN_SLUG
						);
	if ( ! defined( 'CONTEXTUAL_ADMINBAR_MESSAGE' ) || ! CONTEXTUAL_ADMINBAR_MESSAGE ) {
		add_settings_field( 	CAC_PLUGIN_SLUG . '-message',
								__( 'Custom Message', 'contextual-adminbar-color' ),
								'contextual_adminbar_color_field',
								CAC_PLUGIN_SLUG,
								CAC_PLUGIN_SLUG . '-section',
								[   'type'        => 'text',
									'name'        => 'message',
									'class'       => 'regular-text',
									'description' => __( 'This message will be displayed to every user who can see the adminbar.', 'contextual-adminbar-color' ),
								]
							);
	}
	if ( ! defined( 'CONTEXTUAL_ADMINBAR_COLOR' ) || ! CONTEXTUAL_ADMINBAR_COLOR ) {
		add_settings_field( 	CAC_PLUGIN_SLUG . '-color',
								__( 'Custom Scheme', 'contextual-adminbar-color' ),
								'contextual_adminbar_color_field',
								CAC_PLUGIN_SLUG,
								CAC_PLUGIN_SLUG . '-section',
								[   'type'        => 'color',
									'name'        => 'slug',
									'description' => __( 'Select the new scheme color dedicated to the adminbar.', 'contextual-adminbar-color' ),
								]
							);
	}
	if ( ! defined( 'CONTEXTUAL_ADMINBAR_FAVICON' ) || ! CONTEXTUAL_ADMINBAR_FAVICON ) {
		add_settings_field( 	CAC_PLUGIN_SLUG . '-favicon',
								__( 'Favicon', 'contextual-adminbar-color' ),
								'contextual_adminbar_color_field',
								CAC_PLUGIN_SLUG,
								CAC_PLUGIN_SLUG . '-section',
								[   'type'        => 'checkbox',
									'name'        => 'favicon',
									'legend'      => __( 'Check this option to activate a custom favicon.', 'contextual-adminbar-color' ),
									'label'       => __( 'Use color scheme favicon override.', 'contextual-adminbar-color' ),
									'description' => __( 'For authorized roles only.', 'contextual-adminbar-color' ),
								]
							);
	}
	add_settings_field( 	CAC_PLUGIN_SLUG . '-role',
							__( 'Roles Management', 'contextual-adminbar-color' ),
							'contextual_adminbar_color_field',
							CAC_PLUGIN_SLUG,
							CAC_PLUGIN_SLUG . '-section',
							[   'type'        => 'roles',
								'name'        => 'roles',
								'description' => __( 'Select roles that will be able to see the custom adminbar settings.', 'contextual-adminbar-color' ),
							]
						);
}
add_action( 'admin_init', 'contextual_adminbar_color_register_settings' );

function cac_field_attr( $attr, $name, $is_array = false ) {
	$array = $is_array ? '[]' : '';
	switch( $attr ) {
		case 'id':
		case 'for':
		case 'name':
			echo CAC_PLUGIN_SETTING . '[' . $name . ']' . $array;
			break;
		case 'aria-describedby':
		case 'description':
			echo 'description_' . CAC_PLUGIN_SETTING . '[' . $name . ']' . $array;
		break;
	}
}

function contextual_adminbar_color_field( $args ) {
	$args     = wp_parse_args( $args, [ 'type' => '', 'name' => '', 'label' => '', 'legend' => '', 'class' => '', 'description' => '' ] );
	$settings = get_option( CAC_PLUGIN_SETTING, contextual_adminbar_color_default_settings() );
	switch( $args['type'] ) {

		case 'text':
		?>
		<p>
			<?php if ( ! empty( $args['legend'] ) ) { ?>
			<legend class="screen-reader-text"><span><?php echo $args['legend']; ?></span></legend>
			<?php } ?>
			<input
				type="text"
				name="<?php cac_field_attr( 'name', $args['name'] ); ?>"
				id="<?php cac_field_attr( 'id', $args['name'] ); ?>"
				aria-describedby="<?php cac_field_attr( 'aria-describedby', $args['name'] ); ?>"
				value="<?php echo esc_attr( $settings[ $args['name'] ] ); ?>"
				class="<?php echo $args['class']; ?>"
			/>
			<?php if ( ! empty( $args['description'] ) ) { ?>
			<p class="description" id="<?php cac_field_attr( 'description', $args['name'] ); ?>]">
				<?php echo $args['description']; ?>
			</p>
			<?php } ?>
		</p>
		<?php
		break;

		case 'roles':
		?>
		<fieldset>
			<p class="description">
				<legend>
					<span><?php echo $args['description']; ?></span>
				</legend>
			</p>
			<?php
			$roles = [];
			$editable_roles = get_editable_roles();
			foreach ( $editable_roles as $role => $details ) {
			?>
			<p>
				<label for="<?php cac_field_attr( 'for', $args['name'] . '_' . $role ); ?>">
					<input
						type="checkbox"
						name="<?php cac_field_attr( 'name', $args['name'], true ); ?>"
						id="<?php cac_field_attr( 'id', $args['name'] . '_' . $role ); ?>"
						value="<?php echo esc_attr( $role ); ?>"
						<?php checked( in_array( $role, $settings['roles'] ) ); ?>
					/>
					<?php echo translate_user_role( $details['name'] ); ?>
				</label>
			</p>
			<?php
			}
			?>
		</fieldset>

		<?php
		break;

		case 'checkbox':
		?>
		<p>
			<?php if ( ! empty( $args['legend'] ) ) { ?>
			<legend class="screen-reader-text"><span><?php echo $args['legend']; ?></span></legend>
			<?php } ?>
			<label for="<?php cac_field_attr( 'for', $args['name'] ); ?>">
				<input
					type="checkbox"
					name="<?php cac_field_attr( 'name', $args['name'] ); ?>"
					id="<?php cac_field_attr( 'id', $args['name'] ); ?>"
					aria-describedby="<?php cac_field_attr( 'aria-describedby', $args['name'] ); ?>"
					value="1"
					class="<?php echo $args['class']; ?>"
					<?php checked( $settings[ $args['name'] ] ); ?>
				/>
				<?php echo $args['label']; ?>
			</label>
			<?php if ( ! empty( $args['description'] ) ) { ?>
			<p class="description" id="<?php cac_field_attr( 'description', $args['name'] ); ?>">
				<?php echo $args['description']; ?>
			</p>
			<?php } ?>
		</p>
		<?php
		break;

		case 'color':
			$colors = contextual_adminbar_color_get_colors();
			?>
			<fieldset>
				<?php if ( ! empty( $args['legend'] ) ) { ?>
				<legend class="screen-reader-text"><span><?php echo $args['legend']; ?></span></legend>
				<?php } ?>

				<style>
				.contextual_adminbar_color_table_schemes td {
					padding: 0 0.5em 0 0;
				}
				.color-scheme-container {
					display: flex;
					margin: 0;
					padding: 0;
					width: 99px;
					height: 50px;
					border: 1px solid #7e8993;
					border-radius: 4px;
				}
				.color-scheme-item {
					margin: 0;
					padding: 0;
					width: 33px;
					height: 50px;
				}
				.button.button-secondary.contextual_adminbar_color__custom_button {
					vertical-align: middle;
					margin-left: 1em;
				}
				.contextual_adminbar_color_setting_custom_container {

				}
				.contextual_adminbar_color_setting_custom_container label {
					display: block;
				}
				</style>
			<?php
			foreach ( $colors as $key => $values ) {
			?>
			<div>
				<label for="<?php cac_field_attr( 'for', $args['name'] . '_' . $key ); ?>">
					<table class="contextual_adminbar_color_table_schemes">
						<tr>
							<td>
								<input
									type="radio"
									name="<?php cac_field_attr( 'name', $args['name'] ); ?>"
									id="<?php cac_field_attr( 'for', $args['name'] . '_' . $key ); ?>"
									value="<?php echo $key; ?>"
									<?php checked( $settings['slug'], $key ); ?>
								/>
							</td>
							<td>
								<div class="color-scheme-container">
									<div class="color-scheme-item" style="background: <?php echo $values['background']; ?>;"></div>
									<div class="color-scheme-item" style="background: <?php echo $values['background-secondary']; ?>;"></div>
									<div class="color-scheme-item" style="background: <?php echo $values['text-secondary']; ?>;"></div>
									<div class="color-scheme-item" style="background: <?php echo $values['text']; ?>;"></div>
								</div>
							</td>
							<td>
								<span><?php echo $values['title']; ?></span>
							</td>
						</tr>
					</table>
				</label>
			</div>
			<?php
			}
			?>
			</fieldset>
			<?php
		break;
		default: echo 'ERREUR : Ce type de champs n’existe pas…';
	}
}

function contextual_adminbar_color_setting_fields_sanitize( $settings ) {
	$editable_roles      = array_keys( get_editable_roles() );
	$colors              = contextual_adminbar_color_get_colors();
	$settings            = wp_parse_args( $settings, contextual_adminbar_color_default_settings() );
	$settings['message'] = wp_strip_all_tags( $settings['message'] );
	$settings['favicon'] = (int) $settings['favicon'];
	$settings['slug']    = isset( $colors[ $settings['slug'] ] ) ? $settings['slug'] : 'acs';
	$settings['roles']   = array_intersect( $settings['roles'], $editable_roles );
	return $settings;
}

function contextual_adminbar_color_submenu_page_callback() {
	?>
	<div class="wrap contextual_adminbar_color_submenu_page">
		<h1><?php echo get_admin_page_title(); ?></h1>
		<form action="options.php" method="post">
		<?php
		$do_submit = cac_do_notices();
		settings_fields( CAC_PLUGIN_SLUG );
		do_settings_sections( CAC_PLUGIN_SLUG );
		submit_button();
		?>
		</form>
	</div>
	<?php
}

function cac_do_notices() {
	$disabled_settings = [];
	if ( defined( 'CONTEXTUAL_ADMINBAR_COLOR' ) || defined( 'CONTEXTUAL_ADMINBAR_MESSAGE' ) || defined( 'CONTEXTUAL_ADMINBAR_FAVICON' ) ) {
		if ( defined( 'CONTEXTUAL_ADMINBAR_COLOR' ) ) {
			$disabled_settings['CONTEXTUAL_ADMINBAR_COLOR'] = sprintf(
				/* Tranlators: 1: Name of the constant. 2: Value of the constant. */
				esc_html__( '%1$s (color scheme), with the value %2$s', 'contextual-adminbar-color' ),
				'<code>CONTEXTUAL_ADMINBAR_COLOR</code>',
				'<code>' . sanitize_key( CONTEXTUAL_ADMINBAR_COLOR ) . '</code>'
			);
		}
		if ( defined( 'CONTEXTUAL_ADMINBAR_FAVICON' ) ) {
			$value = (bool) CONTEXTUAL_ADMINBAR_FAVICON ? 'true' : 'false';
			$disabled_settings['CONTEXTUAL_ADMINBAR_FAVICON'] = sprintf(
				/* Tranlators: 1: Name of the constant. 2: Value of the constant. */
				esc_html__( '%1$s (favicon), with the value %2$s', 'contextual-adminbar-color' ),
				'<code>CONTEXTUAL_ADMINBAR_FAVICON</code>',
				'<code>' . CONTEXTUAL_ADMINBAR_FAVICON . '</code>'
			);
		}
		if ( defined( 'CONTEXTUAL_ADMINBAR_MESSAGE' ) ) {
			$disabled_settings['CONTEXTUAL_ADMINBAR_MESSAGE'] = sprintf(
				/* Tranlators: 1: Name of the constant. 2: Value of the constant. */
				esc_html__( '%1$s (custom message), with the value %2$s', 'contextual-adminbar-color' ),
				'<code>CONTEXTUAL_ADMINBAR_MESSAGE</code>',
				'<code>' . wp_strip_all_tags( CONTEXTUAL_ADMINBAR_MESSAGE ) . '</code>'
			);
		}
		?>
		<div class="notice notice-info">
			<p>
				<?php
				printf(
					/* Tranlators: Name of the wp-config.php file. */
					esc_html__( 'You have already defined some settings in your %s file.', 'contextual-adminbar-color' ),
					'<code>wp-config.php</code>'
				);
				?>
			</p>
			<p>
				<?php esc_html_e( 'The following settings are already defined and not available on this screen:', 'contextual-adminbar-color' ); ?>
			</p>
			<ul>
			<?php foreach ( $disabled_settings as $disabled_setting ) {
				echo "<li>$disabled_setting</li>";
			}
			?>
			</ul>
		</div>
		<?php
	}
	return count( $disabled_settings );
}
