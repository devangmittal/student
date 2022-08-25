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
	$userids = filter_input( INPUT_POST, 'student_status', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY );

	if ( null !== $userids && array_key_exists( 'approve', $_POST ) ) {
		foreach ( $userids as $userid ) {
			update_user_meta( $userid, 'user_status', 'approve' );
		}
	} elseif ( null !== $userids && array_key_exists( 'deny', $_POST ) ) {
		foreach ( $userids as $userid ) {
			update_user_meta( $userid, 'user_status', 'pending' );
		}
	}
	$users = get_users(
		array(
			'role'       => 'student',
			'meta_key'   => 'user_status',
			'meta_value' => 'pending',
		)
	);
	echo '
	<div>
	<form action="' . filter_input( INPUT_SERVER, 'REQUEST_URI' ) . '" method="post">
	<div>
	<label>Pending Students: </label> <br>';
	foreach ( $users as $user ) {
		echo '
		<input type="checkbox" name="student_status[]" value="' . esc_html( $user->ID ) . '"> ' . esc_html( get_user_meta( $user->ID, 'nickname', true ) ) . '<br>';
	}
	echo '
		<button type="submit" name="approve">Approve</button>
		<button type="submit" name="deny">deny</button>
		</div>
		</form>
		</div>';
}
