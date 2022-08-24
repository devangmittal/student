<?php

namespace Devang\Admin_Setting;

add_action( 'admin_menu', __NAMESPACE__ . '\student_setting_menu' );
/**
 * Create admin setting for student.
 *
 * @return void
 */
function student_setting_menu() {

	add_menu_page(
		'Registered Student',
		'Approval',
		'manage_options',
		'student_status',
		__NAMESPACE__ . '\student_setting_callback_function',
		'dashicons-media-spreadsheet'
	);
}
/**
 * Admin setting callback function.
 *
 * @return void
 */
function student_setting_callback_function() {
	$users = get_users( array( 'role' => 'student' ) );
	echo '<div><form action="' . filter_input( INPUT_SERVER, 'REQUEST_URI' ) . '" method="post">
	<div>
	<label>Pending Students: </label> <br>';
	foreach ( $users as $user ) {
		echo '
		<input type="checkbox" name="student_status[]" value="' . esc_html( $user->ID ) . '"> ' . esc_html( get_user_meta( $user->ID, 'nickname', true ) ) . '<br>';
	}
	echo '
		<button type="submit">Approve</button>
		<button type="submit">deny</button>
		</div>
		</form>';
	$approve = filter_input( INPUT_POST, 'deny' );
	echo '<div>';

	if ( filter_input( INPUT_POST, 'deny' )){
		update_user_meta( $user_id, 'user_status', 'pending' );
	} elseif ( filter_input( INPUT_POST, 'deny' ) ){
		update_user_meta( $user_id, 'user_status', 'pending' );

	}
}
