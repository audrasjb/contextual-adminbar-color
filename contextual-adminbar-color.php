<?php
/*
 * Plugin name: Contextual Adminbar Color
 * Description: Use custom admin bar colors and favicons to differentiate your environments (staging/prod)
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
	$authorized = contextual_adminbar_color_is_user_role_authorized();
	$chosen_color = contextual_adminbar_color_get_the_chosen_color();
	if ( $chosen_color && isset( $chosen_color['slug'] ) && true === $authorized ) {
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
		$authorized = contextual_adminbar_color_is_user_role_authorized();
		$chosen_color = contextual_adminbar_color_get_the_chosen_color();
		if ( $chosen_color && isset( $chosen_color['slug'] ) && true === $authorized ) {
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
	$authorized = contextual_adminbar_color_is_user_role_authorized();
	$chosen_color = contextual_adminbar_color_get_the_chosen_color();
	if ( isset( $chosen_color['slug'] ) && true === $authorized ) {
		$color_slug = strtolower( esc_attr( $chosen_color['slug'] ) );
		if ( file_exists( plugin_dir_path( __FILE__ ) . '/css/' . $color_slug . '.css' ) ) {
			$classes .= ' ' . 'contextual-adminbar-color';
		}
	}
	return $classes;
}
add_filter( 'admin_body_class', 'contextual_adminbar_color_add_admin_body_class' );

function contextual_adminbar_color_add_front_body_class( $classes ) {
	$authorized = contextual_adminbar_color_is_user_role_authorized();
	$chosen_color = contextual_adminbar_color_get_the_chosen_color();
	if ( isset( $chosen_color['slug'] ) && true === $authorized ) {
		$color_slug = strtolower( esc_attr( $chosen_color['slug'] ) );
		$classes[] = 'contextual-adminbar-color';
	}
	return $classes;
}
add_filter( 'body_class', 'contextual_adminbar_color_add_front_body_class' );

function contextual_adminbar_color_add_admin_bar_text( $wp_admin_bar ) {
	$authorized = contextual_adminbar_color_is_user_role_authorized();
	$chosen_color = contextual_adminbar_color_get_the_chosen_color();
	if ( $chosen_color && isset( $chosen_color['message'] ) && ! empty( $chosen_color['message'] ) && true === $authorized ) {
		$color_message = esc_html( $chosen_color['message'] );
		$args = array(
			'parent' => 'top-secondary' ,
			'id'	 => 'contextual-adminbar-color-message',
			'title'  => $color_message,
		);
		$wp_admin_bar->add_node( $args );
	}
}
add_action( 'admin_bar_menu', 'contextual_adminbar_color_add_admin_bar_text' );

function contextual_adminbar_color_add_favicon( $url ) {
	if ( is_admin() ) {
		$authorized = contextual_adminbar_color_is_user_role_authorized();
		$chosen_color = contextual_adminbar_color_get_the_chosen_color();
		if ( $chosen_color && isset( $chosen_color['favicon'] ) && 1 === $chosen_color['favicon'] && true === $authorized ) {
	    	$url = plugin_dir_url( __FILE__ ) . 'assets/favicons/favicon-' . $chosen_color['slug'] . '.ico';
		}
	}
	return $url;
}
add_action( 'get_site_icon_url', 'contextual_adminbar_color_add_favicon', 20 );

function contextual_adminbar_color_is_user_role_authorized() {
	$authorized = true;
	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
		$roles = ( array ) $user->roles;
		if ( get_option( 'contextual-adminbar-color' ) ) {
			$current_settings = get_option( 'contextual-adminbar-color' );
			$slug = sanitize_text_field( $current_settings['slug'] );
			$message = sanitize_text_field( $current_settings['message'] );
			if ( ! empty( $current_settings['roles'] ) ) {
				$existing_roles = $current_settings['roles'];
				$authorized = false;
				foreach ( $roles as $role ) {
					if ( in_array( $role, $existing_roles ) ) {
						$authorized = true;
					}
				}
			}
		}
	}
	return $authorized;
}

function contextual_adminbar_color_get_the_chosen_color() {
	$slug = '';
	$message = '';
	$favicon = '';

	if ( get_option( 'contextual-adminbar-color' ) ) {
		$current_settings = get_option( 'contextual-adminbar-color' );
		$slug = sanitize_text_field( $current_settings['slug'] );
		$message = sanitize_text_field( $current_settings['message'] );
		$favicon = intval( $current_settings['favicon'] );
	}

	if ( defined( 'CONTEXTUAL_ADMINBAR_COLOR' ) && ! empty( CONTEXTUAL_ADMINBAR_COLOR ) ) {
		$slug = sanitize_text_field( CONTEXTUAL_ADMINBAR_COLOR );
	}

	if ( defined( 'CONTEXTUAL_ADMINBAR_FAVICON' ) && ! empty( CONTEXTUAL_ADMINBAR_FAVICON ) ) {
		$favicon = CONTEXTUAL_ADMINBAR_FAVICON;
	}
	
	if ( defined( 'CONTEXTUAL_ADMINBAR_MESSAGE' ) && ! empty( CONTEXTUAL_ADMINBAR_MESSAGE ) ) {
		$message = sanitize_text_field( CONTEXTUAL_ADMINBAR_MESSAGE );
	}
	
	$chosen_color = array(
		'slug'	  => $slug,
		'message' => $message,
		'favicon' => $favicon,
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
				'CONTEXTUAL_ADMINBAR_FAVICON' => false,
				'CONTEXTUAL_ADMINBAR_MESSAGE' => false,
			);
			if ( defined( 'CONTEXTUAL_ADMINBAR_COLOR' ) || defined( 'CONTEXTUAL_ADMINBAR_MESSAGE' ) || defined( 'CONTEXTUAL_ADMINBAR_FAVICON' ) ) {
				if ( get_option( 'contextual-adminbar-color' ) ) {
					$settings = get_option( 'contextual-adminbar-color' );
					$slug = sanitize_text_field( $settings['slug'] );
					$message = sanitize_text_field( $settings['message'] );
				}
				if ( defined( 'CONTEXTUAL_ADMINBAR_COLOR' ) ) {
					$disabled_settings['CONTEXTUAL_ADMINBAR_COLOR'] = sprintf(
						/* Tranlators: 1: Name of the constant. 2: Value of the constant. */
						esc_html__( '%1$s (color scheme), with the value %2$s', 'contextual-adminbar-color' ),
						'<code>CONTEXTUAL_ADMINBAR_COLOR</code>',
						'<code>' . sanitize_text_field( CONTEXTUAL_ADMINBAR_COLOR ) . '</code>'
					);
					$settings['slug'] = strtolower( sanitize_text_field( CONTEXTUAL_ADMINBAR_COLOR ) );
				}
				if ( defined( 'CONTEXTUAL_ADMINBAR_FAVICON' ) ) {
					$value = CONTEXTUAL_ADMINBAR_FAVICON;
					if ( 0 === CONTEXTUAL_ADMINBAR_FAVICON || false === CONTEXTUAL_ADMINBAR_FAVICON ) {
						$value = 'false';
					}
					$disabled_settings['CONTEXTUAL_ADMINBAR_FAVICON'] = sprintf(
						/* Tranlators: 1: Name of the constant. 2: Value of the constant. */
						esc_html__( '%1$s (favicon), with the value %2$s', 'contextual-adminbar-color' ),
						'<code>CONTEXTUAL_ADMINBAR_FAVICON</code>',
						'<code>' . CONTEXTUAL_ADMINBAR_FAVICON . '</code>'
					);
					$settings['favicon'] = 0;
				}
				if ( defined( 'CONTEXTUAL_ADMINBAR_MESSAGE' ) ) {
					$disabled_settings['CONTEXTUAL_ADMINBAR_MESSAGE'] = sprintf(
						/* Tranlators: 1: Name of the constant. 2: Value of the constant. */
						esc_html__( '%1$s (custom message), with the value %2$s', 'contextual-adminbar-color' ),
						'<code>CONTEXTUAL_ADMINBAR_MESSAGE</code>',
						'<code>' . sanitize_text_field( CONTEXTUAL_ADMINBAR_MESSAGE ) . '</code>'
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
							esc_html__( 'You have already defined some settings in your %s file.', 'contextual-adminbar-color' ),
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

					$new_slug = '';
					if ( isset( $_POST['contextual_adminbar_color_setting_slug'] ) ) {
						$new_slug = sanitize_text_field( $_POST['contextual_adminbar_color_setting_slug'] );
					}
					
					$new_message = '';
					if ( isset( $_POST['contextual_adminbar_color_setting_message'] ) ) {
						$new_message = sanitize_text_field( $_POST['contextual_adminbar_color_setting_message'] );
					}
					
					$new_favicon = '';
					if ( isset( $_POST['contextual_adminbar_color_setting_favicon'] ) ) {
						$new_favicon = intval( $_POST['contextual_adminbar_color_setting_favicon'] );
					}

					$new_display_for_roles = $_POST['contextual_adminbar_color_setting_role'];
					$display_for_roles = array();
					foreach ( $new_display_for_roles as $role ) {
						$display_for_roles[] = sanitize_text_field( $role );
					}

					$new_settings = array(
						'slug' => $new_slug,
						'message' => $new_message,
						'favicon' => $new_favicon,
						'roles' => $display_for_roles,
					);
					update_option( 'contextual-adminbar-color', $new_settings );
					?>
					<div class="notice notice-success settings-error is-dismissible"> 
						<p>
							<?php
							echo sprintf(
								/* translators: 1: Link opening tag. 2: Link closing tag. */
								__( 'Settings saved. Please %1$srefresh this page to see your changes%2$s.', 'contextual-adminbar-color' ),
								'<a href="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">',
								'</a>'
							);
							?>
						</p>
					</div>
					<?php
				}
			}
			$slug = '';
			$message = '';
			$favicon = '';
			$existing_roles = array();
			if ( get_option( 'contextual-adminbar-color' ) ) {
				$current_settings = get_option( 'contextual-adminbar-color' );
				$slug = sanitize_text_field( $current_settings['slug'] );
				$message = sanitize_text_field( $current_settings['message'] );
				$favicon = intval( $current_settings['favicon'] );
				if ( ! empty( $current_settings['roles'] ) ) {
					$existing_roles = $current_settings['roles'];
				}
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
							<fieldset>
								<legend class="screen-reader-text"><span>Select a color scheme below.</span></legend>
								
								<style>
								.contextual_adminbar_color_table_schemes td {
									padding: 0 0.5em 0 0;
								}
								.contextual_adminbar_color_table_schemes img {
									border: 1px solid #000;
									border-radius: 5px;
								}
								</style>
								
								<div>
									<label for="contextual_adminbar_color_setting_slug_blue">
										<table class="contextual_adminbar_color_table_schemes">
											<tr>
												<td>
													<input name="contextual_adminbar_color_setting_slug" id="contextual_adminbar_color_setting_slug_blue" type="radio" value="blue" <?php checked( $slug, 'blue' ); ?> />
												</td>
												<td>
													<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/schemes/scheme-blue.png" alt="" />
												</td>
												<td>
													<span><?php esc_html_e( 'Blue', 'contextual-adminbar-color' ); ?></span>
												</td>
											</tr>
										</table>
									</label>
								</div>

								<div>
									<label for="contextual_adminbar_color_setting_slug_red">
										<table class="contextual_adminbar_color_table_schemes">
											<tr>
												<td>
													<input name="contextual_adminbar_color_setting_slug" id="contextual_adminbar_color_setting_slug_red" type="radio" value="red" <?php checked( $slug, 'red' ); ?> />
												</td>
												<td>
													<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/schemes/scheme-red.png" alt="" />
												</td>
												<td>
													<span><?php esc_html_e( 'Red', 'contextual-adminbar-color' ); ?></span>
												</td>
											</tr>
										</table>
									</label>
								</div>

								<div>
									<label for="contextual_adminbar_color_setting_slug_green">
										<table class="contextual_adminbar_color_table_schemes">
											<tr>
												<td>
													<input name="contextual_adminbar_color_setting_slug" id="contextual_adminbar_color_setting_slug_green" type="radio" value="green" <?php checked( $slug, 'green' ); ?> />
												</td>
												<td>
													<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/schemes/scheme-green.png" alt="" />
												</td>
												<td>
													<span><?php esc_html_e( 'Green', 'contextual-adminbar-color' ); ?></span>
												</td>
											</tr>
										</table>
									</label>
								</div>

								<div>
									<label for="contextual_adminbar_color_setting_slug_purple">
										<table class="contextual_adminbar_color_table_schemes">
											<tr>
												<td>
													<input name="contextual_adminbar_color_setting_slug" id="contextual_adminbar_color_setting_slug_purple" type="radio" value="purple" <?php checked( $slug, 'purple' ); ?> />
												</td>
												<td>
													<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/schemes/scheme-purple.png" alt="" />
												</td>
												<td>
													<span><?php esc_html_e( 'Purple', 'contextual-adminbar-color' ); ?></span>
												</td>
											</tr>
										</table>
									</label>
								</div>

								<div>
									<label for="contextual_adminbar_color_setting_slug_orange">
										<table class="contextual_adminbar_color_table_schemes">
											<tr>
												<td>
													<input name="contextual_adminbar_color_setting_slug" id="contextual_adminbar_color_setting_slug_orange" type="radio" value="orange" <?php checked( $slug, 'orange' ); ?> />
												</td>
												<td>
													<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/schemes/scheme-orange.png" alt="" />
												</td>
												<td>
													<span><?php esc_html_e( 'Orange', 'contextual-adminbar-color' ); ?></span>
												</td>
											</tr>
										</table>
									</label>
								</div>

								<div>
									<label for="contextual_adminbar_color_setting_slug_darkgray">
										<table class="contextual_adminbar_color_table_schemes">
											<tr>
												<td>
													<input name="contextual_adminbar_color_setting_slug" id="contextual_adminbar_color_setting_slug_darkgray" type="radio" value="darkgray" <?php checked( $slug, 'darkgray' ); ?> />
												</td>
												<td>
													<img src="<?php echo plugin_dir_url( __FILE__ ); ?>/assets/schemes/scheme-darkgray.png" alt="" />
												</td>
												<td>
													<span><?php esc_html_e( 'Dark gray', 'contextual-adminbar-color' ); ?></span>
												</td>
											</tr>
										</table>
									</label>
								</div>

								<p class="description" id="description_contextual_adminbar_color_setting_slug">
									<?php esc_html_e( 'Default: WordPress native admin color scheme.', 'contextual-adminbar-color' ); ?>
								</p>
							</fieldset>
						</td>
					</tr>
					<?php $settings_counter++; ?>
				<?php endif; ?>

				<?php if ( false === $disabled_settings['CONTEXTUAL_ADMINBAR_FAVICON'] ) : ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Favicon', 'contextual-adminbar-color' ); ?></th>
						<td>
							<fieldset>
								<legend class="screen-reader-text">
									<?php esc_html_e( 'Check this option to activate a custom favicon.', 'contextual-adminbar-color' ); ?>
								</legend>
								<label for="contextual_adminbar_color_setting_favicon">
									<input name="contextual_adminbar_color_setting_favicon" type="checkbox" id="contextual_adminbar_color_setting_favicon" value="1" <?php checked( $favicon, '1' ); ?>>
									<?php esc_html_e( 'Activate color scheme favicon override', 'contextual-adminbar-color' ); ?>
								</label>
								<p class="description">
									<?php esc_html_e( 'For WordPress Admin and authorized users only.', 'contextual-adminbar-color' ); ?>
								</p>
							</fieldset>
						</td>
					</tr>
				<?php endif; ?>

					<tr>
						<th scope="row"><?php esc_html_e( 'User management', 'contextual-adminbar-color' ); ?></th>
						<td>
							<fieldset>
								<p class="description">
									<legend>
										<span><?php esc_html_e( 'Select roles that will be able to see the custom admin bar settings:', 'contextual-adminbar-color' ); ?></span>
									</legend>
								</p>
								<?php
								$roles = array();
								$editable_roles = get_editable_roles();
								foreach ( $editable_roles as $role => $details ) {
									$checked = '';
									if ( in_array( esc_attr( $role ), $existing_roles) ) {
										$checked = ' checked';
									}
									if ( ! get_option( 'contextual-adminbar-color' ) ) {
										$checked = ' checked';
									}
									?>
									<p>
										<label for="contextual_adminbar_color_setting_role_<?php echo esc_attr( $role ); ?>">
											<input name="contextual_adminbar_color_setting_role[]" type="checkbox" id="contextual_adminbar_color_setting_role_<?php echo esc_attr( $role ); ?>" value="<?php echo esc_attr( $role ); ?>" <?php echo $checked; ?>>
											<?php echo translate_user_role( $details['name'] ); ?>
										</label>
									</p>
									<?php
								}
								?>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>
			<?php if ( $settings_counter > 0 ) : ?>
				<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Save changes', 'contextual-adminbar-color' ); ?>" />
			<?php endif; ?>
		</form>
	</div>
	<?php
}