<?php
/**
 * Add menu settings in admin panel.
 *
 * @package student
 */

namespace Devang\Admin_Setting;

add_action( 'admin_menu', __NAMESPACE__ . '\student_setting_menu' );
/**
 * Create admin setting for student.
 *
 * @return void
 */
function student_setting_menu() {

	add_menu_page(
		'Students',
		'Students',
		'manage_options',
		'student_status',
		__NAMESPACE__ . '\student_menu_callback_function',
		'dashicons-media-spreadsheet'
	);
}
/**
 * Student menu callback function.
 *
 * @return void
 */
function student_menu_callback_function() {
	$userids = filter_input( INPUT_POST, 'student_status', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY );

	if ( wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce_update-student-status' ), 'update' ) && null !== $userids ) {
		if ( array_key_exists( 'approve', $_POST ) ) {
			foreach ( $userids as $userid ) {
				update_user_meta( $userid, 'user_status', 'approve' );
			}
		} elseif ( array_key_exists( 'deny', $_POST ) ) {
			foreach ( $userids as $userid ) {
				update_user_meta( $userid, 'user_status', 'pending' );
			}
		}
	}
	$users = get_users(
		array(
			'role'       => 'student',
			'meta_key'   => 'user_status',
			'meta_value' => 'pending',
		)
	);
	if ( empty( $users ) ) {
		?>
		<div>
			<p>
				No Pending students.
			</p>
		</div>
		<?php
	} else {
		?>
		<div>
			<form action="<?php filter_input( INPUT_SERVER, 'REQUEST_URI' ); ?>" method="post">
				<div>
					<label>Pending Students: </label> <br> 
					<?php foreach ( $users as $user ) { ?>
						<input type="checkbox" name="student_status[]" value="<?php echo esc_html( $user->ID ); ?>"><?php echo esc_html( get_user_meta( $user->ID, 'nickname', true ) ); ?><br>
						<?php
					}
					?>
					<button type="submit" name="approve">Approve</button>
					<button type="submit" name="deny">deny</button><?php wp_nonce_field( 'update', '_wpnonce_update-student-status' ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}
