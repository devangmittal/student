<?php
/**
 * Creates a shortcode for the plugin.
 *
 * @package student
 */

namespace Devang\Shortcode;

/**
 * Create a registtration form for user.
 *
 * @return void
 */
function registration_form() {
	echo '
    <form action="' . filter_input( INPUT_SERVER, 'REQUEST_URI' ) . '" method="post">
    <div>
    <label for="username">Username <strong>*</strong></label>
    <input type="text" name="username" value="' . filter_input( INPUT_POST, 'username' ) . '">
    </div>
     
    <div>
    <label for="password">Password <strong>*</strong></label>
    <input type="password" name="password" value="' . filter_input( INPUT_POST, 'password' ) . '">
    </div>
     
    <div>
    <label for="email">Email <strong>*</strong></label>
    <input type="email" name="email" value="' . filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL ) . '">
    </div>
     
    <div>
    <label for="url">Blog url</label>
    <input type="url" name="url" value="' . filter_input( INPUT_POST, 'url', FILTER_VALIDATE_URL ) . '">
    </div>
     
    <div>
    <label for="firstname">First Name</label>
    <input type="text" name="fname" value="' . filter_input( INPUT_POST, 'fname' ) . '">
    </div>
     
    <div>
    <label for="website">Last Name</label>
    <input type="text" name="lname" value="' . filter_input( INPUT_POST, 'lname' ) . '">
    </div>
     
    <input type="submit" name="submit" value="Register Student"/>' . wp_nonce_field( 'register', '_wpnonce_register-student' ) . '
    </form>
    ';
}
/**
 * Validates the data.
 *
 * @param string $username Username to be validated.
 * @param string $password Password  to be validated.
 * @param string $email Email id  to be validated.
 * @param string $url Blog url  to be validated.
 * @param string $first_name First name  to be validated.
 * @param string $last_name Last name  to be validated.
 * @return void
 */
function registration_validation( $username, $password, $email, $url, $first_name, $last_name ) {
	global $reg_errors;
	$reg_errors = new \WP_Error();
	if ( empty( $username ) || empty( $password ) || empty( $email ) ) {
		$reg_errors->add( 'field', __( 'Required form field is missing', 'sturent' ) );
	} elseif ( 4 > strlen( $username ) ) {
		$reg_errors->add( 'username_length', __( 'Username too short. At least 4 characters is required', 'sturent' ) );
	} elseif ( username_exists( $username ) ) {
		$reg_errors->add( 'user_name', __( 'Sorry, that username already exists!', 'sturent' ) );
	} elseif ( ! validate_username( $username ) ) {
		$reg_errors->add( 'username_invalid', __( 'Sorry, the username you entered is not valid', 'sturent' ) );
	} elseif ( 5 > strlen( $password ) ) {
		$reg_errors->add( 'password', __( 'Password length must be greater than 5', 'sturent' ) );
	} elseif ( ! is_email( $email ) ) {
		$reg_errors->add( 'email_invalid', __( 'Email is not valid', 'sturent' ) );
	} elseif ( email_exists( $email ) ) {
		$reg_errors->add( 'email', __( 'Email Already in use', 'sturent' ) );
	}
	if ( ! empty( $url ) ) {
		if ( wp_http_validate_url( $url ) ) {
			return;
		} else {
			$reg_errors->add( 'url', 'It is not a valid URL' );
		}
	} elseif ( ! validate_username( $first_name ) ) {
		$reg_errors->add( 'username_invalid', __( 'Sorry, Invalid First Name', 'sturent' ) );
	} elseif ( ! validate_username( $last_name ) ) {
		$reg_errors->add( 'username_invalid', __( 'Sorry, Invalid Last Name', 'sturent' ) );
	}
	if ( is_wp_error( $reg_errors ) ) {

		foreach ( $reg_errors->get_error_messages() as $error ) {
			echo '<div>';
			echo '<strong>ERROR</strong>: ';
			echo esc_html( $error ) . '<br/>';
			echo '</div>';
		}
	}
}
/**
 * Adds user.
 *
 * @return void
 */
function complete_registration() {
	global $reg_errors, $username, $password, $email, $url, $first_name, $last_name;
	if ( 1 > count( $reg_errors->get_error_messages() ) ) {
		$userdata = array(
			'user_login' => $username,
			'user_email' => $email,
			'user_pass'  => $password,
			'user_url'   => $url,
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'role'       => 'student',
		);
		if ( wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce_register-student' ), 'register' ) ) {
			wp_insert_user( $userdata );
			echo '<div>';
			esc_html_e( 'Registration complete.', 'student' );
			echo '</div>';
		} else {
			echo '<div>';
			esc_html_e( 'You do not have permission to create a user.', 'student' );
			echo '</div>';
		}
	}
}
/**
 * Custom function to add shortcode.
 *
 * @return void
 */
function student_shortcode_callable() {
	if ( filter_input( INPUT_POST, 'submit' ) ) {
		registration_validation(
			filter_input( INPUT_POST, 'username' ),
			filter_input( INPUT_POST, 'password' ),
			filter_input( INPUT_POST, 'email' ),
			filter_input( INPUT_POST, 'url' ),
			filter_input( INPUT_POST, 'fname' ),
			filter_input( INPUT_POST, 'lname' )
		);
		// sanitize user form input.
		global $username, $password, $email, $url, $first_name, $last_name;
		$username   = sanitize_user( filter_input( INPUT_POST, 'username' ) );
		$password   = esc_attr( filter_input( INPUT_POST, 'password' ) );
		$email      = sanitize_email( filter_input( INPUT_POST, 'email' ) );
		$url        = esc_url( filter_input( INPUT_POST, 'url' ) );
		$first_name = sanitize_text_field( filter_input( INPUT_POST, 'fname' ) );
		$last_name  = sanitize_text_field( filter_input( INPUT_POST, 'lname' ) );

		// call @function complete_registration to create the user.
		// only when no WP_error is found.
		complete_registration(
			$username,
			$password,
			$email,
			$url,
			$first_name,
			$last_name
		);
	}

	registration_form();
}
add_shortcode( 'student_register_form', __NAMESPACE__ . '\student_shortcode_callable' );
