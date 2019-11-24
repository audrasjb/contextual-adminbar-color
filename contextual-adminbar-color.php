<?php
/*
 * Plugin name: Contextual Adminbar Color
 * Description: Use custom admin bar colors to differentiate environments (staging, preprod, production)
 * Plugin URI: https://jeanbaptisteaudras.com/en/contextual-adminbar-color-wordpress
 * Requires at least: 5.3
 * Requires PHP: 5.6
 * Author: whodunitagency, audrasjb
 * Author URI: https://jeanbaptisteaudras.com
 * Version: 0.1
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text-domain: contextual-adminbar-color
 */

function contextual_adminbar_color_admin_enqueue_adminbar_color() {
	$chosen_color = contextual_adminbar_color_get_the_chosen_color();
	if ( $chosen_color && isset( $chosen_color['slug'] ) ) {
		$color_slug = strtolower( esc_attr( $chosen_color['slug'] ) );
		if ( file_exists( plugin_dir_path( __FILE__ ) . '/css/' . $color_slug . '.css' ) ) {
			wp_register_style( 'contextual-adminbar-color-admin-' . $color_slug, plugin_dir_url( __FILE__ ) . '/css/' . $color_slug . '.css' );
			wp_enqueue_style( 'contextual-adminbar-color-admin-' . $color_slug );
		}
	}
}
add_action( 'admin_enqueue_scripts', 'contextual_adminbar_color_admin_enqueue_adminbar_color' );

function contextual_adminbar_color_front_enqueue_adminbar_color() {
	if ( is_admin_bar_showing() ) {
		$chosen_color = contextual_adminbar_color_get_the_chosen_color();
		if ( $chosen_color && isset( $chosen_color['slug'] ) ) {
			$color_slug = strtolower( esc_attr( $chosen_color['slug'] ) );
			if ( file_exists( plugin_dir_path( __FILE__ ) . '/css/' . $color_slug . '.css' ) ) {
				wp_register_style( 'contextual-adminbar-color-front-' . $color_slug, plugin_dir_url( __FILE__ ) . 'css/' . $color_slug . '.css' );
				wp_enqueue_style( 'contextual-adminbar-color-front-' . $color_slug );
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'contextual_adminbar_color_front_enqueue_adminbar_color' );

function contextual_adminbar_color_add_admin_body_class( $classes ) {
	$chosen_color = contextual_adminbar_color_get_the_chosen_color();
	if ( isset( $chosen_color['slug'] ) ) {
		$color_slug = strtolower( esc_attr( $chosen_color['slug'] ) );
		if ( file_exists( plugin_dir_path( __FILE__ ) . '/css/' . $color_slug . '.css' ) ) {
			$classes .= ' ' . 'contextual-adminbar-color';
		}
	}
	return $classes;
}
add_filter( 'admin_body_class', 'contextual_adminbar_color_add_admin_body_class' );

function contextual_adminbar_color_add_front_body_class( $classes ) {
	$chosen_color = contextual_adminbar_color_get_the_chosen_color();
	if ( isset( $chosen_color['slug'] ) ) {
		$color_slug = strtolower( esc_attr( $chosen_color['slug'] ) );
		$classes[] = 'contextual-adminbar-color';
	}
	return $classes;
}
add_filter( 'body_class', 'contextual_adminbar_color_add_front_body_class' );

function contextual_adminbar_color_add_admin_bar_text( $wp_admin_bar ) {
	$chosen_color = contextual_adminbar_color_get_the_chosen_color();
	if ( $chosen_color && isset( $chosen_color['message'] ) && ! empty( $chosen_color['message'] ) ) {
		$color_message = esc_html( $chosen_color['message'] );
		$args = array(
			'parent' => 'top-secondary' ,
			'id'     => 'contextual-adminbar-color-message',
			'title'  => $color_message,
		);
		$wp_admin_bar->add_node( $args );
	}
}
add_action( 'admin_bar_menu', 'contextual_adminbar_color_add_admin_bar_text' );

function contextual_adminbar_color_get_the_chosen_color() {
	$slug = '';
	$message = '';

	if ( get_option( 'contextual-adminbar-color' ) ) {
		$current_settings = get_option( 'contextual-adminbar-color' );
		$slug = sanitize_text_field( $current_settings['slug'] );
		$message = sanitize_text_field( $current_settings['message'] );
	}

	if ( defined( 'CONTEXTUAL_ADMINBAR_COLOR' ) && ! empty( 'CONTEXTUAL_ADMINBAR_COLOR' ) ) {
		$slug = sanitize_text_field( CONTEXTUAL_ADMINBAR_COLOR );
	}
	
	if ( defined( 'CONTEXTUAL_ADMINBAR_MESSAGE' ) && ! empty( 'CONTEXTUAL_ADMINBAR_MESSAGE' ) ) {
		$message = sanitize_text_field( CONTEXTUAL_ADMINBAR_MESSAGE );
	}
	
	$chosen_color = array(
		'slug'     => $slug,
		'message' => $message,
	);
	
	return $chosen_color;
}

function contextual_adminbar_color_submenu_page() { 
	if ( defined( 'CONTEXTUAL_ADMINBAR_SETTINGS' ) && false === CONTEXTUAL_ADMINBAR_SETTINGS ) {
		// Do nothing
	} else {
		add_submenu_page( 'tools.php', esc_html__( 'Adminbar settings', 'contextual-adminbar-color' ), esc_html__( 'Adminbar settings', 'contextual-adminbar-color' ), 'manage_options', 'contextual-adminbar-color', 'contextual_adminbar_color_submenu_page_callback' );
	}
}
add_action( 'admin_menu', 'contextual_adminbar_color_submenu_page' );

function contextual_adminbar_color_submenu_page_callback() {
	?>
	<div class="wrap contextual_adminbar_color_submenu_page">
		<form action="" method="post">
			<?php
			$disabled_settings = array(
				'CONTEXTUAL_ADMINBAR_COLOR'   => false,
				'CONTEXTUAL_ADMINBAR_MESSAGE' => false,
			);
			if ( defined( 'CONTEXTUAL_ADMINBAR_COLOR' ) || defined( 'CONTEXTUAL_ADMINBAR_MESSAGE' ) ) {
				if ( get_option( 'contextual-adminbar-color' ) ) {
					$settings = get_option( 'contextual-adminbar-color' );
					$slug = sanitize_text_field( $settings['slug'] );
					$message = sanitize_text_field( $settings['message'] );
				}
				if ( defined( 'CONTEXTUAL_ADMINBAR_COLOR' ) && ! empty( 'CONTEXTUAL_ADMINBAR_COLOR' ) ) {
					$disabled_settings['CONTEXTUAL_ADMINBAR_COLOR'] = sprintf(
						/* Tranlators: 1: Name of the constant. 2: Value of the constant. */
						esc_html__( '%1$s (color scheme), with the value "%2$s"', 'contextual-adminbar-color' ),
						'<code>CONTEXTUAL_ADMINBAR_COLOR</code>',
						sanitize_text_field( CONTEXTUAL_ADMINBAR_COLOR )
					);
					$settings['slug'] = strtolower( sanitize_text_field( CONTEXTUAL_ADMINBAR_COLOR ) );
				}
				if ( defined( 'CONTEXTUAL_ADMINBAR_MESSAGE' ) && ! empty( 'CONTEXTUAL_ADMINBAR_MESSAGE' ) ) {
					$disabled_settings['CONTEXTUAL_ADMINBAR_MESSAGE'] = sprintf(
						/* Tranlators: 1: Name of the constant. 2: Value of the constant. */
						esc_html__( '%1$s (custom message), with the value "%2$s"', 'contextual-adminbar-color' ),
						'<code>CONTEXTUAL_ADMINBAR_MESSAGE</code>',
						sanitize_text_field( CONTEXTUAL_ADMINBAR_MESSAGE )
					);
					$settings['message'] = sanitize_text_field( CONTEXTUAL_ADMINBAR_MESSAGE );
				}
				update_option( 'contextual-adminbar-color', $settings );
				?>
				<div class="notice notice-info"> 
					<p>
						<?php
						echo sprintf(
							/* Tranlators: Name of the wp-config.php file. */
							esc_html__( 'You have already defined the settings in your %s file.', 'contextual-adminbar-color' ),
							'<code>wp-config.php</code>'
						);
						?>
					</p>
					<p>
						<?php esc_html_e( 'The following settings are already defined and not available on this screen:', 'contextual-adminbar-color' ); ?>
					</p>
					<ul>
					<?php foreach ( $disabled_settings as $disabled_setting ) : ?>
						<li><?php echo $disabled_setting; ?></li>
					<?php endforeach; ?>
					</ul>
				</div>

				<?php
			}
			if ( isset( $_POST ) && ! empty( $_POST ) ) {
				if ( wp_verify_nonce( $_POST['nonce'], 'contextual_adminbar_color_nonce' ) ) {
					$new_slug = sanitize_text_field( $_POST['contextual_adminbar_color_setting_slug'] );
					$new_message = sanitize_text_field( $_POST['contextual_adminbar_color_setting_message'] );
					$new_settings = array(
						'slug' => $new_slug,
						'message' => $new_message,
					);
					update_option( 'contextual-adminbar-color', $new_settings );
					?>
					<div class="notice notice-success settings-error is-dismissible"> 
						<p>
							<?php esc_html_e( 'Settings saved. Please refresh this page to see your changes.', 'contextual-adminbar-color' ); ?>
						</p>
					</div>
					<?php
				}
			}
			$slug = '';
			$message = '';
			if ( get_option( 'contextual-adminbar-color' ) ) {
				$current_settings = get_option( 'contextual-adminbar-color' );
				$slug = sanitize_text_field( $current_settings['slug'] );
				$message = sanitize_text_field( $current_settings['message'] );
			}
			?>

			<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'contextual_adminbar_color_nonce' ) ?>">

			<h1><?php esc_html_e( 'Contextual adminbar settings', 'contextual-adminbar-color' ); ?></h1>

			<table class="form-table" role="presentation">
				<tbody>
				<?php $settings_counter = 0; ?>
				<?php if ( false === $disabled_settings['CONTEXTUAL_ADMINBAR_MESSAGE'] ) : ?>
					<tr>
						<th scope="row">
							<label for="contextual_adminbar_color_setting_message">
								<?php esc_html_e( 'Custom message', 'contextual-adminbar-color' ); ?>
							</label>
						</th>
						<td>
							<input name="contextual_adminbar_color_setting_message" type="text" id="contextual_adminbar_color_setting_message" aria-describedby="description_contextual_adminbar_color_setting_message" value="<?php echo $message; ?>" class="regular-text">
							<p class="description" id="description_contextual_adminbar_color_setting_message">
								<?php esc_html_e( 'This message will be displayed to every user who can see the adminbar.', 'contextual-adminbar-color' ); ?>
							</p>
						</td>
					</tr>
					<?php $settings_counter++; ?>
				<?php endif; ?>

				<?php if ( false === $disabled_settings['CONTEXTUAL_ADMINBAR_COLOR'] ) : ?>
					<tr>
						<th scope="row">
							<label for="contextual_adminbar_color_setting_slug">
								<?php esc_html_e( 'Color scheme', 'contextual-adminbar-color' ); ?>
							</label>
						</th>
						<td>
							<select name="contextual_adminbar_color_setting_slug" id="contextual_adminbar_color_setting_slug" aria-describedby="description_contextual_adminbar_color_setting_slug">
								<option value=""><?php esc_html_e( '— Select a color scheme —', 'contextual-adminbar-color' ); ?></option>
								<option value="blue" <?php selected( $slug, 'blue' ); ?>><?php esc_html_e( 'Blue', 'contextual-adminbar-color' ); ?></option>
								<option value="red" <?php selected( $slug, 'red' ); ?>><?php esc_html_e( 'Red', 'contextual-adminbar-color' ); ?></option>
								<option value="green" <?php selected( $slug, 'green' ); ?>><?php esc_html_e( 'Green', 'contextual-adminbar-color' ); ?></option>
								<option value="orange" <?php selected( $slug, 'orange' ); ?>><?php esc_html_e( 'Orange', 'contextual-adminbar-color' ); ?></option>
								<option value="purple" <?php selected( $slug, 'purple' ); ?>><?php esc_html_e( 'Purple', 'contextual-adminbar-color' ); ?></option>
								<option value="darkgray" <?php selected( $slug, 'darkgray' ); ?>><?php esc_html_e( 'Dark gray', 'contextual-adminbar-color' ); ?></option>
							</select>
							<p class="description" id="description_contextual_adminbar_color_setting_slug">
								<?php esc_html_e( 'Default: WordPress Admin native color scheme.', 'contextual-adminbar-color' ); ?>
							</p>
						</td>
					</tr>
					<?php $settings_counter++; ?>
				<?php endif; ?>

					<!--
					<tr>
						<th scope="row"><?php esc_html_e( '', 'contextual-adminbar-color' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text"><span>Search Engine Visibility</span></legend>
								<label for="blog_public">
									<input name="blog_public" type="checkbox" id="blog_public" value="0">
									Discourage search engines from indexing this site
								</label>
								<p class="description">It is up to search engines to honor this request.</p>
							</fieldset>
						</td>
					</tr>
					-->
				</tbody>
			</table>
			<?php if ( $settings_counter > 0 ) : ?>
				<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save changes', 'contextual-adminbar-color' ); ?>" />
			<?php endif; ?>
		</form>
	</div>
	<?php
}