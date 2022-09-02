<?php
/**
 * Creates a shortcode for the plugin.
 *
 * @package student
 */

namespace Devang\Shortcode;

/**
 * Create a registration form for user.
 *
 * @return void
 */
function registration_form() {
	?>
	<form action="<?php filter_input( INPUT_SERVER, 'REQUEST_URI' ); ?>" method="post">
		<div>
			<label for="username">Username <strong>*</strong></label>
			<input type="text" name="username" value="<?php filter_input( INPUT_POST, 'username' ); ?>">
		</div>

		<div>
			<label for="password">Password <strong>*</strong></label>
			<input type="password" name="password" value="<?php filter_input( INPUT_POST, 'password' ); ?>">
		</div>

		<div>
			<label for="email">Email <strong>*</strong></label>
			<input type="email" name="email" value="<?php filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL ); ?>">
		</div>

		<div>
			<label for="url">Blog url</label>
			<input type="url" name="url" value="<?php filter_input( INPUT_POST, 'url', FILTER_VALIDATE_URL ); ?>">
		</div>

		<div>
			<label for="firstname">First Name <strong>*</strong></label>
			<input type="text" name="fname" value="<?php filter_input( INPUT_POST, 'fname' ); ?>">
		</div>

		<div>
			<label for="lastname">Last Name <strong>*</strong></label>
			<input type="text" name="lname" value="<?php filter_input( INPUT_POST, 'lname' ); ?>">
		</div>

		<input type="submit" name="submit" value="Register Student"/><?php wp_nonce_field( 'register', '_wpnonce_register-student' ); ?>
	</form>
	<?php
}
/**
 * Validates the user input data.
 *
 * @return WP_Error $reg_errors
 */
function registration_validation() {
	$reg_errors = new \WP_Error();
	if ( empty( filter_input( INPUT_POST, 'username' ) ) || empty( filter_input( INPUT_POST, 'password' ) ) || empty( filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL ) ) || empty( filter_input( INPUT_POST, 'fname' ) ) || empty( filter_input( INPUT_POST, 'lname' ) ) ) {
		$reg_errors->add( 'field', __( 'Required form field is missing', 'student' ) );
	} elseif ( 4 > strlen( filter_input( INPUT_POST, 'username' ) ) ) {
		$reg_errors->add( 'username_length', __( 'Username too short. At least 4 characters is required', 'student' ) );
	} elseif ( username_exists( filter_input( INPUT_POST, 'username' ) ) ) {
		$reg_errors->add( 'user_name', __( 'Sorry, that username already exists!', 'student' ) );
	} elseif ( ! validate_username( filter_input( INPUT_POST, 'username' ) ) ) {
		$reg_errors->add( 'username_invalid', __( 'Sorry, the username you entered is not valid', 'student' ) );
	} elseif ( 5 > strlen( filter_input( INPUT_POST, 'password' ) ) ) {
		$reg_errors->add( 'password', __( 'Password length must be greater than 5', 'student' ) );
	} elseif ( ! is_email( filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL ) ) ) {
		$reg_errors->add( 'email_invalid', __( 'Email is not valid', 'student' ) );
	} elseif ( email_exists( filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL ) ) ) {
		$reg_errors->add( 'email', __( 'Email Already in use', 'student' ) );
	}
	if ( ! empty( filter_input( INPUT_POST, 'url', FILTER_VALIDATE_URL ) ) ) {
		if ( wp_http_validate_url( filter_input( INPUT_POST, 'url', FILTER_VALIDATE_URL ) ) ) {
			return;
		} else {
			$reg_errors->add( 'url', 'It is not a valid URL' );
		}
	} elseif ( ! validate_username( filter_input( INPUT_POST, 'fname' ) ) ) {
		$reg_errors->add( 'username_invalid', __( 'Sorry, Invalid First Name', 'student' ) );
	} elseif ( ! validate_username( filter_input( INPUT_POST, 'lname' ) ) ) {
		$reg_errors->add( 'username_invalid', __( 'Sorry, Invalid Last Name', 'student' ) );
	}
	if ( is_wp_error( $reg_errors ) ) {

		foreach ( $reg_errors->get_error_messages() as $error ) {
			?>
			<div>
				<strong>ERROR</strong>: <?php esc_html( $error ); ?><br/>
			</div>
			<?php
		}
	}
	return $reg_errors;
}
/**
 * Inserts a user if no error found in registration_validation function found.
 * See registration_validation() first.
 *
 * @return void
 */
function complete_registration() {
	$reg_errors = registration_validation();

	if ( 1 > count( $reg_errors->get_error_messages() ) ) {
		$userdata = array(
			// sanitize user form input.
			'user_login' => sanitize_user( filter_input( INPUT_POST, 'username' ) ),
			'user_email' => sanitize_email( filter_input( INPUT_POST, 'email' ) ),
			'user_pass'  => esc_attr( filter_input( INPUT_POST, 'password' ) ),
			'user_url'   => esc_url( filter_input( INPUT_POST, 'url' ) ),
			'first_name' => sanitize_text_field( filter_input( INPUT_POST, 'fname' ) ),
			'last_name'  => sanitize_text_field( filter_input( INPUT_POST, 'lname' ) ),
			'role'       => 'student',
		);
		// Verify nonce.
		if ( wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce_register-student' ), 'register' ) ) {
			// Insert new student in database.
			$user_id = wp_insert_user( $userdata );
			if ( ! is_wp_error( $user_id ) ) {
				// Update student status to meta.
				update_user_meta( $user_id, 'user_status', 'pending' );
			} else {
				return;
			}
			?>
			<div>
				<?php esc_html_e( 'Registration complete. Waiting for Approval.', 'student' ); ?>
			</div>
			<?php
		} else {
			?>
			<div>
				<?php esc_html_e( 'You do not have permission to create a user.', 'student' ); ?>
			</div>
			<?php
		}
	}
}
/**
 * Custom function to add shortcode for student registration.
 * Renders registration form.
 * If no errors then registers a student with status pending.
 *
 * @return void
 */
function student_shortcode_callable() {
	if ( filter_input( INPUT_POST, 'submit' ) ) {
		// Register a student after validation is done.
		complete_registration();
	}
	// Renders registration form HTML.
	registration_form();
}
// Add shortcode hook.
add_shortcode( 'student_register_form', __NAMESPACE__ . '\student_shortcode_callable' );
