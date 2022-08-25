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
function redirect_to_student_logged_in() {
	?> <div> 
	<?php
	if ( is_user_logged_in() ) {
		print_r( get_userdata( get_current_user_id(), '', true ) );
		// user_login
		// user_email
		// user_url
		// first_name
		// last_name.
		$userdata = get_userdata( get_current_user_id(), '', true );
		?>
		<div>
			<form action="<?php filter_input( INPUT_SERVER, 'REQUEST_URI' ); ?>" method="post">
				<label>User Details</label><br>
				<div>
					<label for="username">Username </label>
					<input type="text" name="username" value="<?php echo esc_html( $userdata->user_login ); ?>">
				</div>
				<div>
					<label for="password">Update Password </label>
					<input type="password" name="password" value="">
				</div>
				<div>
					<label for="email">Email </label>
					<input type="email" name="email" value="<?php echo esc_html( $userdata->user_email ); ?>">
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
				<input type="submit" name="submit" value="Update Details"/><?php wp_nonce_field( 'update', '_wpnonce_update-details' ); ?>

			</form>
		</div>
		<?php
	} else {
		echo 'hello';
	}
}
/**
 * Validates input fields.
 *
 * @return WP_Error $errors WP_Errors if any.
 */
function validate_updation() {
	$errors = new \WP_Error();
	if ( empty( filter_input( INPUT_POST, 'username' ) ) || empty( filter_input( INPUT_POST, 'password' ) ) || empty( filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL ) ) || empty( filter_input( INPUT_POST, 'fname' ) ) || empty( filter_input( INPUT_POST, 'lname' ) ) ) {
		$errors->add( 'field', __( 'Required form field is missing', 'sturent' ) );
	} elseif ( 4 > strlen( filter_input( INPUT_POST, 'username' ) ) ) {
		$errors->add( 'username_length', __( 'Username too short. At least 4 characters is required', 'sturent' ) );
	} elseif ( username_exists( filter_input( INPUT_POST, 'username' ) ) ) {
		$errors->add( 'user_name', __( 'Sorry, that username already exists!', 'sturent' ) );
	} elseif ( ! validate_username( filter_input( INPUT_POST, 'username' ) ) ) {
		$errors->add( 'username_invalid', __( 'Sorry, the username you entered is not valid', 'sturent' ) );
	} elseif ( 5 > strlen( filter_input( INPUT_POST, 'password' ) ) ) {
		$errors->add( 'password', __( 'Password length must be greater than 5', 'sturent' ) );
	} elseif ( ! is_email( filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL ) ) ) {
		$errors->add( 'email_invalid', __( 'Email is not valid', 'sturent' ) );
	} elseif ( email_exists( filter_input( INPUT_POST, 'email', FILTER_VALIDATE_EMAIL ) ) ) {
		$errors->add( 'email', __( 'Email Already in use', 'sturent' ) );
	}
	if ( ! empty( filter_input( INPUT_POST, 'url', FILTER_VALIDATE_URL ) ) ) {
		if ( wp_http_validate_url( filter_input( INPUT_POST, 'url', FILTER_VALIDATE_URL ) ) ) {
			return;
		} else {
			$errors->add( 'url', 'It is not a valid URL' );
		}
	} elseif ( ! validate_username( filter_input( INPUT_POST, 'fname' ) ) ) {
		$errors->add( 'username_invalid', __( 'Sorry, Invalid First Name', 'sturent' ) );
	} elseif ( ! validate_username( filter_input( INPUT_POST, 'lname' ) ) ) {
		$errors->add( 'username_invalid', __( 'Sorry, Invalid Last Name', 'sturent' ) );
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
			'user_login' => sanitize_user( filter_input( INPUT_POST, 'username' ) ),
			'user_email' => sanitize_email( filter_input( INPUT_POST, 'email' ) ),
			'user_pass'  => esc_attr( filter_input( INPUT_POST, 'password' ) ),
			'user_url'   => esc_url( filter_input( INPUT_POST, 'url' ) ),
			'first_name' => sanitize_text_field( filter_input( INPUT_POST, 'fname' ) ),
			'last_name'  => sanitize_text_field( filter_input( INPUT_POST, 'lname' ) ),
		);
		if ( wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce_update-details' ), 'update' ) ) {
			if ( ! is_wp_error( wp_update_user( $updated_user_data ) ) ) {
				wp_update_user( $updated_user_data );
			} else {
				?>
				<div>
					<p>
						Cannot Update User Data, Try again later.
					</p>
				</div>
				<?php
			}
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
	if ( filter_input( INPUT_POST, 'submit' ) ) {
		update_user();
	}
	redirect_to_student_logged_in();
}
add_shortcode( 'student_login', __NAMESPACE__ . '\student_login_form_callable' );
