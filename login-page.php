<?php
/**
 * Add student login page.
 *
 * @package student
 */

namespace Devang\login_page;

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
				<form id="update_student_meta" action="<?php filter_input( INPUT_SERVER, 'REQUEST_URI' ); ?>" method="post">
					<div id="validation_error">

					</div>
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
					<input type="submit" name="update" value="Update Details"/>
				</form>
			</div>
			<?php
		}
	} else {
		wp_login_form();
	}
}

/**
 * Update a user meta in users table.
 *
 * @return void
 */
function update_user() {
	if ( empty( filter_input( INPUT_POST, 'fname' ) ) || empty( filter_input( INPUT_POST, 'lname' ) ) ) {
		wp_send_json_error( __( 'Required form field is missing', 'student' ) );
	} elseif ( ! empty( filter_input( INPUT_POST, 'url', FILTER_VALIDATE_URL ) ) ) {
		if ( wp_http_validate_url( filter_input( INPUT_POST, 'url', FILTER_VALIDATE_URL ) ) ) {
			return;
		} else {
			wp_send_json_error( __( 'It is not a valid URL', 'student' ) );
		}
	} elseif ( ! validate_username( filter_input( INPUT_POST, 'fname' ) ) ) {
		wp_send_json_error( __( 'Sorry, Invalid First Name', 'student' ) );
	} elseif ( ! validate_username( filter_input( INPUT_POST, 'lname' ) ) ) {
		wp_send_json_error( __( 'Sorry, Invalid Last Name', 'student' ) );
	}
	$updated_user_data = array(
		'ID'         => get_current_user_id(),
		'user_url'   => esc_url( filter_input( INPUT_POST, 'url' ) ),
		'first_name' => sanitize_text_field( filter_input( INPUT_POST, 'fname' ) ),
		'last_name'  => sanitize_text_field( filter_input( INPUT_POST, 'lname' ) ),
	);
	if ( wp_verify_nonce( filter_input( INPUT_POST, 'ajax_nonce' ), 'student_ajax_nonce' ) ) {
			wp_update_user( $updated_user_data );
			wp_send_json_success( 'Fields Updated' );
	}
}
// Add update_user function to wp_ajax hook.
add_action( 'wp_ajax_update_user', __NAMESPACE__ . '\update_user' );
/**
 * Create a shortcode for user profile if logged in
 * and login page if logged out.
 *
 * @return void
 */
function student_login_form_callable() {
	log_in_student();
}
// Add shortcode student_login.
add_shortcode( 'student_login', __NAMESPACE__ . '\student_login_form_callable' );

/**
 * Checks if student status is denied and prevents him to login.
 *
 * @param WP_User $user User object.
 * @return WP_User|null $user User object.
 */
function authenticate_student( $user ) {
	if ( in_array( 'student', $user->roles, true ) && 'denied' === get_user_meta( $user->ID, 'user_status', true ) ) {
		$referrer = filter_input( INPUT_SERVER, 'HTTP_REFERER' );
		if ( ! empty( $referrer && ! strstr( $referrer, 'wp-login' ) && ! strstr( $referrer, 'wp-admin' ) ) ) {
			wp_redirect( $referrer . '?login=failed&reason=Error: Your registration is denied.' );
			exit;
		}
		return $user;
	}
	return $user;
}
// Add authenticate_student function to wp_authenticate_user hook.
add_filter( 'wp_authenticate_user', __NAMESPACE__ . '\authenticate_student' );
/**
 * Content to display on top of wp_login_form function.
 *
 * @param string $content Content to be dispplayed.
 * @return string $content Content to be dispplayed.
 */
function render_denied_student_login_error( $content ) {
	$content = filter_input( INPUT_GET, 'reason' );
	return $content;
}
// Add render_denied_student_login_error to login_form_top hook.
add_filter( 'login_form_top', __NAMESPACE__ . '\render_denied_student_login_error' );
