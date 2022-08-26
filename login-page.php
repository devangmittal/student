<?php
/**
 * Add student login page.
 *
 * @package student
 */

namespace Devang\login_page;

use WP_Error;

/**
 * Renders logged in user details if user is logged in
 * otherwise renders login form.
 *
 * @return void
 */
function log_in_student() {
	if ( is_user_logged_in() ) {
		$userdata       = get_userdata( get_current_user_id(), '', true );
		$user_meta_data = get_user_meta( get_current_user_id(), 'user_status', true );
		if ( 'pending' === $user_meta_data ) {
			?>
			<div>
				<p>
					Your Registration is still pending, Please try after some time.
				</p>
			</div>
			<?php
		} elseif ( 'denied' !== $user_meta_data ) {
			?>
			<div>
				<form action="<?php filter_input( INPUT_SERVER, 'REQUEST_URI' ); ?>" method="post">
					<label>User Details</label><br>
					<div>
						<label for="username">Username </label>
						<input type="text" name="username" value="<?php echo esc_html( $userdata->user_login ); ?>" disabled>
					</div>
					<div>
						<label for="email">Email </label>
						<input type="email" name="email" value="<?php echo esc_html( $userdata->user_email ); ?>"disabled>
					</div>
					<div>
						<label  for="url">Blog url </label>
						<input type="url" name="url" value="<?php echo esc_html( $userdata->user_url ); ?>">
					</div>
					<div>
						<label for="firstname">First Name </label>
						<input type="text" name="fname" value="<?php echo esc_html( $userdata->first_name ); ?>">
					</div>
					<div>
						<label for="lastname">Last Name </label>
						<input type="text" name="lname" value="<?php echo esc_html( $userdata->last_name ); ?>">
					</div>
					<input type="submit" name="update" value="Update Details"/><?php wp_nonce_field( 'update', '_wpnonce_update-details' ); ?>
				</form>
			</div>
			<?php
		}
	} else {
		wp_login_form();
	}
}

/**
 * Validates input fields.
 *
 * @return WP_Error $errors WP_Errors if any.
 */
function validate_updation() {
	$errors = new WP_Error();
	if ( empty( filter_input( INPUT_POST, 'fname' ) ) || empty( filter_input( INPUT_POST, 'lname' ) ) ) {
		$errors->add( 'field', __( 'Required form field is missing', 'student' ) );
	} elseif ( ! empty( filter_input( INPUT_POST, 'url', FILTER_VALIDATE_URL ) ) ) {
		if ( wp_http_validate_url( filter_input( INPUT_POST, 'url', FILTER_VALIDATE_URL ) ) ) {
			return;
		} else {
			$errors->add( 'url', 'It is not a valid URL' );
		}
	} elseif ( ! validate_username( filter_input( INPUT_POST, 'fname' ) ) ) {
		$errors->add( 'username_invalid', __( 'Sorry, Invalid First Name', 'student' ) );
	} elseif ( ! validate_username( filter_input( INPUT_POST, 'lname' ) ) ) {
		$errors->add( 'username_invalid', __( 'Sorry, Invalid Last Name', 'student' ) );
	}
	if ( is_wp_error( $errors ) ) {

		foreach ( $errors->get_error_messages() as $error ) {
			echo '<div>';
			echo '<strong>ERROR</strong>: ';
			echo esc_html( $error ) . '<br/>';
			echo '</div>';
		}
	}
	return $errors;
}
/**
 * Update a user in users table.
 *
 * @return void
 */
function update_user() {
	$errors = validate_updation();
	if ( 1 > count( $errors->get_error_messages() ) ) {
		$updated_user_data = array(
			'ID'         => get_current_user_id(),
			'user_url'   => esc_url( filter_input( INPUT_POST, 'url' ) ),
			'first_name' => sanitize_text_field( filter_input( INPUT_POST, 'fname' ) ),
			'last_name'  => sanitize_text_field( filter_input( INPUT_POST, 'lname' ) ),
		);
		if ( wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce_update-details' ), 'update' ) ) {
				wp_update_user( $updated_user_data );
		}
	}
}
/**
 * Create a shortcode for user profile if logged in
 * and login page if logged out.
 *
 * @return void
 */
function student_login_form_callable() {
	if ( filter_input( INPUT_POST, 'update' ) ) {
		update_user();
	}
	log_in_student();
}
add_shortcode( 'student_login', __NAMESPACE__ . '\student_login_form_callable' );

/**
 * Checks if student status is denied and prevents him to login.
 *
 * @param WP_User $user User object.
 * @return WP_User|null $user User object.
 */
function authenticate_student( $user ) {
	if ( in_array( 'student', $user->roles, true ) && 'denied' === get_user_meta( $user->ID, 'user_status', true ) ) {
		$user = new WP_Error( 'student_authentication_failed', __( '<strong>Error</strong>: Your registration is denied.', 'student' ) );
		return $user;
	}
	return $user;
}
add_filter( 'wp_authenticate_user', __NAMESPACE__ . '\authenticate_student', 9 );

/**
 * Redirect on the same page if student login fails.
 *
 * @return void
 */
function student_login_failed_redirect() {
	$referrer = filter_input( INPUT_SERVER, 'HTTP_REFERER' );
	if ( ! empty( $referrer && ! strstr( $referrer, 'wp-login' ) && ! strstr( $referrer, 'wp-admin' ) ) ) {
		wp_redirect( $referrer );
		exit;
	}
}
add_action( 'wp_login_failed', __NAMESPACE__ . '\student_login_failed_redirect' );
