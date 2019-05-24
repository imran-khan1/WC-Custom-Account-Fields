<?php
/*
Plugin Name: WooCommerce Custom Account Fields
Plugin URI: http://imran1.com
Description: Add custom WooCommerce user account fields.
Author: imran
Version: 1.0.0
Author URI: https://imran1.com
*/

/**
 * Get additional account fields.
 *
 * @return array
 */
function imran_get_account_fields() {
	return apply_filters( 'imran_account_fields', array(
		'first_name'                 => array(
			'type'                 => 'text',
			'label'                => __( 'First Name', 'imran' ),
			'hide_in_account'      => true,
			'hide_in_admin'        => true,
			'hide_in_checkout'     => true,
			'hide_in_registration' => false,
			'required'             => true,
		),
		'last_name'                  => array(
			'type'                 => 'text',
			'label'                => __( 'Last Name', 'imran' ),
			'hide_in_account'      => true,
			'hide_in_admin'        => true,
			'hide_in_checkout'     => true,
			'hide_in_registration' => false,
			'required'             => true,
		),
		'user_url'                   => array(
			'type'                 => 'text',
			'label'                => __( 'Website', 'imran' ),
			'hide_in_account'      => false,
			'hide_in_admin'        => true,
			'hide_in_checkout'     => true,
			'hide_in_registration' => false,
			'required'             => false,
		),
		'imran-register-text'       => array(
			'type'        => 'text',
			'label'       => __( 'Text Field', 'imran' ),
			'placeholder' => __( 'E.g. Some text...', 'imran' ),
			'required'    => true,
		),
		'imran-register-select'     => array(
			'type'    => 'select',
			'label'   => __( 'Select Field', 'imran' ),
			'options' => array(
				'' => __( 'Select an option...', 'imran' ),
				1  => __( 'Option 1', 'imran' ),
				2  => __( 'Option 2', 'imran' ),
				3  => __( 'Option 3', 'imran' ),
			),
		),
		'imran-register-textarea'   => array(
			'type'     => 'textarea',
			'label'    => __( 'Textarea', 'imran' ),
			'sanitize' => 'sanitize_textarea_field',
		),
		'imran-register-checkbox'   => array(
			'type'  => 'checkbox',
			'label' => __( 'Checkbox', 'imran' ),
		),
		'imran-register-radio'      => array(
			'type'    => 'radio',
			'label'   => __( 'Radio Fields', 'imran' ),
			'options' => array(
				1 => __( 'Option 1', 'imran' ),
				2 => __( 'Option 2', 'imran' ),
				3 => __( 'Option 3', 'imran' ),
			),
		),
		'imran-register-checkboxes' => array(
			'type'     => 'checkboxes',
			'label'    => __( 'Checkboxes', 'imran' ),
			'options'  => array(
				1 => __( 'Option 1', 'imran' ),
				2 => __( 'Option 2', 'imran' ),
				3 => __( 'Option 3', 'imran' ),
			),
			'required' => true,
		),
		'imran-register-number'     => array(
			'type'  => 'number',
			'label' => __( 'Number Field', 'imran' ),
		),
		'imran-register-email'      => array(
			'type'     => 'email',
			'label'    => __( 'Email Field', 'imran' ),
			'sanitize' => 'sanitize_email',
		),
		'imran-register-country'    => array(
			'type'  => 'country',
			'label' => __( 'Country Field', 'imran' ),
		),
	) );
}

/**
 * Add post values to account fields if set.
 *
 * @param array $fields
 *
 * @return array
 */
function imran_add_post_data_to_account_fields( $fields ) {
	if ( empty( $_POST ) ) {
		return $fields;
	}

	foreach ( $fields as $key => $field_args ) {
		if ( empty( $_POST[ $key ] ) ) {
			$fields[ $key ]['value'] = '';
			continue;
		}

		$fields[ $key ]['value'] = $_POST[ $key ];
	}

	return $fields;
}

add_filter( 'imran_account_fields', 'imran_add_post_data_to_account_fields', 10, 1 );

/**
 * Add fields to registration form and account area.
 */
function imran_print_user_frontend_fields() {
	$fields            = imran_get_account_fields();
	$is_user_logged_in = is_user_logged_in();

	foreach ( $fields as $key => $field_args ) {
		$value = null;

		if ( ! imran_is_field_visible( $field_args ) ) {
			continue;
		}

		if ( $is_user_logged_in ) {
			$user_id = imran_get_edit_user_id();
			$value   = imran_get_userdata( $user_id, $key );
		}

		$value = isset( $field_args['value'] ) ? $field_args['value'] : $value;

		woocommerce_form_field( $key, $field_args, $value );
	}
}

add_action( 'woocommerce_register_form', 'imran_print_user_frontend_fields', 10 ); // register form
add_action( 'woocommerce_edit_account_form', 'imran_print_user_frontend_fields', 10 ); // my account

/**
 * Get user data.
 *
 * @param $user_id
 * @param $key
 *
 * @return mixed|string
 */
function imran_get_userdata( $user_id, $key ) {
	if ( ! imran_is_userdata( $key ) ) {
		return get_user_meta( $user_id, $key, true );
	}

	$userdata = get_userdata( $user_id );

	if ( ! $userdata || ! isset( $userdata->{$key} ) ) {
		return '';
	}

	return $userdata->{$key};
}

/**
 * Modify checkboxes/radio fields.
 *
 * @param $field
 * @param $key
 * @param $args
 * @param $value
 *
 * @return string
 */
function imran_form_field_modify( $field, $key, $args, $value ) {
	ob_start();
	imran_print_list_field( $key, $args, $value );
	$field = ob_get_clean();

	if ( $args['return'] ) {
		return $field;
	} else {
		echo $field;
	}
}

add_filter( 'woocommerce_form_field_checkboxes', 'imran_form_field_modify', 10, 4 );
add_filter( 'woocommerce_form_field_radio', 'imran_form_field_modify', 10, 4 );

/**
 * Get currently editing user ID (frontend account/edit profile/edit other user).
 *
 * @return int
 */
function imran_get_edit_user_id() {
	return isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : get_current_user_id();
}

/**
 * Print a list field (checkboxes|radio).
 *
 * @param string $key
 * @param array  $field_args
 * @param mixed  $value
 */
function imran_print_list_field( $key, $field_args, $value = null ) {
	$value = empty( $value ) && $field_args['type'] === 'checkboxes' ? array() : $value;
	?>
	<div class="form-row">
		<?php if ( ! empty( $field_args['label'] ) ) { ?>
			<label>
				<?php echo $field_args['label']; ?>
				<?php if ( ! empty( $field_args['required'] ) ) { ?>
					<abbr class="required" title="<?php echo esc_attr__( 'required', 'woocommerce' ); ?>">*</abbr>
				<?php } ?>
			</label>
		<?php } ?>
		<ul>
			<?php foreach ( $field_args['options'] as $option_value => $option_label ) {
				$id         = sprintf( '%s_%s', $key, sanitize_title_with_dashes( $option_label ) );
				$option_key = $field_args['type'] === 'checkboxes' ? sprintf( '%s[%s]', $key, $option_value ) : $key;
				$type       = $field_args['type'] === 'checkboxes' ? 'checkbox' : $field_args['type'];
				$checked    = $field_args['type'] === 'checkboxes' ? in_array( $option_value, $value ) : $option_value == $value;
				?>
				<li>
					<label for="<?php echo esc_attr( $id ); ?>">
						<input type="<?php echo esc_attr( $type ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $option_key ); ?>" value="<?php echo esc_attr( $option_value ); ?>" <?php checked( $checked ); ?>>
						<?php echo $option_label; ?>
					</label>
				</li>
			<?php } ?>
		</ul>
	</div>
	<?php
}

/**
 * Save registration fields.
 *
 * @param int $customer_id
 */
function imran_save_account_fields( $customer_id ) {
	$fields         = imran_get_account_fields();
	$sanitized_data = array();

	foreach ( $fields as $key => $field_args ) {
		if ( ! imran_is_field_visible( $field_args ) ) {
			continue;
		}

		$sanitize = isset( $field_args['sanitize'] ) ? $field_args['sanitize'] : 'wc_clean';
		$value    = isset( $_POST[ $key ] ) ? call_user_func( $sanitize, $_POST[ $key ] ) : '';

		if ( imran_is_userdata( $key ) ) {
			$sanitized_data[ $key ] = $value;
			continue;
		}

		update_user_meta( $customer_id, $key, $value );
	}

	if ( ! empty( $sanitized_data ) ) {
		$sanitized_data['ID'] = $customer_id;
		wp_update_user( $sanitized_data );
	}
}

add_action( 'woocommerce_created_customer', 'imran_save_account_fields' ); // register/checkout
add_action( 'personal_options_update', 'imran_save_account_fields' ); // edit own account admin
add_action( 'edit_user_profile_update', 'imran_save_account_fields' ); // edit other account
add_action( 'woocommerce_save_account_details', 'imran_save_account_fields' ); // edit WC account

/**
 * Is this field core user data.
 *
 * @param $key
 *
 * @return bool
 */
function imran_is_userdata( $key ) {
	$userdata = array(
		'user_pass',
		'user_login',
		'user_nicename',
		'user_url',
		'user_email',
		'display_name',
		'nickname',
		'first_name',
		'last_name',
		'description',
		'rich_editing',
		'user_registered',
		'role',
		'jabber',
		'aim',
		'yim',
		'show_admin_bar_front',
	);

	return in_array( $key, $userdata );
}

/**
 * Is field visible.
 *
 * @param $field_args
 *
 * @return bool
 */
function imran_is_field_visible( $field_args ) {
	$visible = true;
	$action  = filter_input( INPUT_POST, 'action' );

	if ( is_admin() && ! empty( $field_args['hide_in_admin'] ) ) {
		$visible = false;
	} elseif ( ( is_account_page() || $action === 'save_account_details' ) && is_user_logged_in() && ! empty( $field_args['hide_in_account'] ) ) {
		$visible = false;
	} elseif ( ( is_account_page() || $action === 'save_account_details' ) && ! is_user_logged_in() && ! empty( $field_args['hide_in_registration'] ) ) {
		$visible = false;
	} elseif ( is_checkout() && ! empty( $field_args['hide_in_checkout'] ) ) {
		$visible = false;
	}

	return $visible;
}

/**
 * Add fields to admin area.
 */
function imran_print_user_admin_fields() {
	$fields = imran_get_account_fields();
	?>
	<h2><?php _e( 'Additional Information', 'imran' ); ?></h2>
	<table class="form-table" id="imran-additional-information">
		<tbody>
		<?php foreach ( $fields as $key => $field_args ) { ?>
			<?php
			if ( ! imran_is_field_visible( $field_args ) ) {
				continue;
			}

			$user_id = imran_get_edit_user_id();
			$value   = imran_get_userdata( $user_id, $key );
			?>
			<tr>
				<th>
					<label for="<?php echo $key; ?>"><?php echo $field_args['label']; ?></label>
				</th>
				<td>
					<?php $field_args['label'] = false; ?>
					<?php woocommerce_form_field( $key, $field_args, $value ); ?>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
}

add_action( 'show_user_profile', 'imran_print_user_admin_fields', 30 ); // admin: edit profile
add_action( 'edit_user_profile', 'imran_print_user_admin_fields', 30 ); // admin: edit other users

/**
 * Validate fields on frontend.
 *
 * @param WP_Error $errors
 *
 * @return WP_Error
 */
function imran_validate_user_frontend_fields( $errors ) {
	$fields = imran_get_account_fields();

	foreach ( $fields as $key => $field_args ) {
		if ( empty( $field_args['required'] ) ) {
			continue;
		}

		if ( ! isset( $_POST['register'] ) && ! empty( $field_args['hide_in_account'] ) ) {
			continue;
		}

		if ( isset( $_POST['register'] ) && ! empty( $field_args['hide_in_registration'] ) ) {
			continue;
		}

		if ( empty( $_POST[ $key ] ) ) {
			$message = sprintf( __( '%s is a required field.', 'imran' ), '<strong>' . $field_args['label'] . '</strong>' );
			$errors->add( $key, $message );
		}
	}

	return $errors;
}

add_filter( 'woocommerce_registration_errors', 'imran_validate_user_frontend_fields', 10 );
add_filter( 'woocommerce_save_account_details_errors', 'imran_validate_user_frontend_fields', 10 );

/**
 * Show fields at checkout.
 */
function imran_checkout_fields( $checkout_fields ) {
	$fields = imran_get_account_fields();

	foreach ( $fields as $key => $field_args ) {
		if ( ! imran_is_field_visible( $field_args ) ) {
			continue;
		}

		// Make sure our fields have a default priority so
		// no error is thrown when sorting them.
		$field_args['priority'] = isset( $field_args['priority'] ) ? $field_args['priority'] : 0;

		$checkout_fields['account'][ $key ] = $field_args;
	}

	// Default password field has no priority which throws an
	// error when it tries to order the fields by priority.
	if ( ! empty( $checkout_fields['account']['account_password'] ) && ! isset( $checkout_fields['account']['account_password']['priority'] ) ) {
		$checkout_fields['account']['account_password']['priority'] = 0;
	}

	return $checkout_fields;
}

add_filter( 'woocommerce_checkout_fields', 'imran_checkout_fields', 10, 1 );