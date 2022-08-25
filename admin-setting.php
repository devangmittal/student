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
	// Student custom admin menu.
	add_menu_page(
		'Students',
		'Students',
		'manage_options',
		'all_students',
		__NAMESPACE__ . '\student_menu_callback_function',
		'dashicons-media-spreadsheet'
	);
	// Sub menu Pending Students of Students custom menu.
	add_submenu_page(
		'all_students',
		'Pending Students',
		'Pending Students',
		'manage_options',
		'pending_students',
		__NAMESPACE__ . '\pending_students_menu_callback_function',
	);
	// Sub menu Apprived Students of Students custom menu.
	add_submenu_page(
		'all_students',
		'Approved Students',
		'Approved Students',
		'manage_options',
		'approved_students',
		__NAMESPACE__ . '\approved_students_menu_callback_function',
	);
	// Sub menu Denied Students of Students custom menu.
	add_submenu_page(
		'all_students',
		'Denied Students',
		'Denied Students',
		'manage_options',
		'denied_students',
		__NAMESPACE__ . '\denied_students_menu_callback_function',
	);
}
/**
 * Generates options field for select tag.
 *
 * @param array  $array Array with key value pairs.
 * @param string $string Value to check in selected().
 * @return void
 */
function display_select_option_loop( $array, $string ) {
	foreach ( $array as $key => $value ) {
		?>
		<option value = "<?php echo esc_attr( $key ); ?>"<?php selected( $key, $string, true ); ?>><?php echo esc_html( $value ); ?></option>
		<?php
	}
}
/**
 * Create Student menu callback function.
 *
 * @return void
 */
function student_menu_callback_function() {
	$userids = filter_input( INPUT_POST, 'student_status', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY );

	if ( wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce_update-student-status' ), 'update' ) && null !== $userids ) {
		if ( array_key_exists( 'approve', $_POST ) ) {
			foreach ( $userids as $userid ) {
				update_user_meta( $userid, 'user_status', 'approved' );
			}
		} elseif ( array_key_exists( 'pending', $_POST ) ) {
			foreach ( $userids as $userid ) {
				update_user_meta( $userid, 'user_status', 'pending' );
			}
		} elseif ( array_key_exists( 'deny', $_POST ) ) {
			foreach ( $userids as $userid ) {
				update_user_meta( $userid, 'user_status', 'denied' );
			}
		}
	}
	$users = get_users(
		array(
			'role'    => 'student',
			'orderby' => filter_input( INPUT_POST, 'order_by' ),
			'order'   => filter_input( INPUT_POST, 'order' ),
		)
	);
	if ( empty( $users ) ) {
		?>
		<div>
			<p>
				There isn't any registered students.
			</p>
		</div>
		<?php
	} else {
		?>
		<div>
			<form action="<?php filter_input( INPUT_SERVER, 'REQUEST_URI' ); ?>" method="post">
				<div>
					<div>
						<label for="registered_student">Registered Students: </label> <br> 
						<?php foreach ( $users as $user ) { ?>
							<input type="checkbox" name="student_status[]" value="<?php echo esc_html( $user->ID ); ?>"><?php echo esc_html( get_user_meta( $user->ID, 'nickname', true ) ); ?><br>
							<?php
						}
						?>
						<button type="submit" name="approve">Approve</button>
						<button type="submit" name="pending">Pending</button>
						<button type="submit" name="deny">Deny</button>
					</div>
					<div>
					<label for="filter">Show students by</label>
						<select name = "order_by" id="filter" onchange="this.form.submit()">
							<?php
							$filter = array(
								'nicename' => 'Name',
								'ID'       => 'User ID',
							);
							display_select_option_loop( $filter, filter_input( INPUT_POST, 'order_by' ) );
							?>
						</select>
					</div>
					<div>
					<label for="filter_order">Order by</label>
						<select name = "order" id="filter_order" onchange="this.form.submit()">
							<?php
							$filter = array(
								'ASC'  => 'Ascending',
								'DESC' => 'Descending',
							);
							display_select_option_loop( $filter, filter_input( INPUT_POST, 'order' ) );
							?>
						</select>
					</div>
					<?php wp_nonce_field( 'update', '_wpnonce_update-student-status' ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}

/**
 * Create Pending Students sub menu of Students admin menu.
 *
 * @return void
 */
function pending_students_menu_callback_function() {
	$userids = filter_input( INPUT_POST, 'student_status', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY );

	if ( wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce_update-pending-student-status' ), 'update' ) && null !== $userids ) {
		if ( array_key_exists( 'approve', $_POST ) ) {
			foreach ( $userids as $userid ) {
				update_user_meta( $userid, 'user_status', 'approved' );
			}
		} elseif ( array_key_exists( 'deny', $_POST ) ) {
			foreach ( $userids as $userid ) {
				update_user_meta( $userid, 'user_status', 'denied' );
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
				There isn't any Pending students.
			</p>
		</div>
		<?php
	} else {
		?>
		<div>
			<form action="<?php filter_input( INPUT_SERVER, 'REQUEST_URI' ); ?>" method="post">
				<div>
					<label for="pending_student">Pending Students: </label> <br> 
					<?php foreach ( $users as $user ) { ?>
						<input type="checkbox" name="student_status[]" value="<?php echo esc_html( $user->ID ); ?>"><?php echo esc_html( get_user_meta( $user->ID, 'nickname', true ) ); ?><br>
						<?php
					}
					?>
					<button type="submit" name="approve">Approve</button>
					<button type="submit" name="deny">Deny</button><?php wp_nonce_field( 'update', '_wpnonce_update-pending-student-status' ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}
/**
 * Create Approved Students sub menu of Students admin menu.
 *
 * @return void
 */
function approved_students_menu_callback_function() {
	$userids = filter_input( INPUT_POST, 'student_status', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY );

	if ( wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce_update-approved-student-status' ), 'update' ) && null !== $userids ) {
		if ( array_key_exists( 'pending', $_POST ) ) {
			foreach ( $userids as $userid ) {
				update_user_meta( $userid, 'user_status', 'pending' );
			}
		} elseif ( array_key_exists( 'deny', $_POST ) ) {
			foreach ( $userids as $userid ) {
				update_user_meta( $userid, 'user_status', 'denied' );
			}
		}
	}
	$users = get_users(
		array(
			'role'       => 'student',
			'meta_key'   => 'user_status',
			'meta_value' => 'approved',
		)
	);
	if ( empty( $users ) ) {
		?>
		<div>
			<p>
				There isn't any Approved students.
			</p>
		</div>
		<?php
	} else {
		?>
		<div>
			<form action="<?php filter_input( INPUT_SERVER, 'REQUEST_URI' ); ?>" method="post">
				<div>
					<label for="approved_student">Approved Students: </label> <br> 
					<?php foreach ( $users as $user ) { ?>
						<input type="checkbox" name="student_status[]" value="<?php echo esc_html( $user->ID ); ?>"><?php echo esc_html( get_user_meta( $user->ID, 'nickname', true ) ); ?><br>
						<?php
					}
					?>
					<button type="submit" name="pending">Pending</button>
					<button type="submit" name="deny">Deny</button><?php wp_nonce_field( 'update', '_wpnonce_update-approved-student-status' ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}
/**
 * Create Denied Students sub menu of Students admin menu.
 *
 * @return void
 */
function denied_students_menu_callback_function() {
	$userids = filter_input( INPUT_POST, 'student_status', FILTER_VALIDATE_INT, FILTER_REQUIRE_ARRAY );

	if ( wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce_update-denied-student-status' ), 'update' ) && null !== $userids ) {
		if ( array_key_exists( 'pending', $_POST ) ) {
			foreach ( $userids as $userid ) {
				update_user_meta( $userid, 'user_status', 'pending' );
			}
		} elseif ( array_key_exists( 'approve', $_POST ) ) {
			foreach ( $userids as $userid ) {
				update_user_meta( $userid, 'user_status', 'approved' );
			}
		}
	}
	$users = get_users(
		array(
			'role'       => 'student',
			'meta_key'   => 'user_status',
			'meta_value' => 'denied',
		)
	);
	if ( empty( $users ) ) {
		?>
		<div>
			<p>
				There isn't any Denied students.
			</p>
		</div>
		<?php
	} else {
		?>
		<div>
			<form action="<?php filter_input( INPUT_SERVER, 'REQUEST_URI' ); ?>" method="post">
				<div>
					<label for="denied_student">Denied Students: </label> <br> 
					<?php foreach ( $users as $user ) { ?>
						<input type="checkbox" name="student_status[]" value="<?php echo esc_html( $user->ID ); ?>"><?php echo esc_html( get_user_meta( $user->ID, 'nickname', true ) ); ?><br>
						<?php
					}
					?>
					<button type="submit" name="approve">Approve</button>
					<button type="submit" name="pending">Pending</button><?php wp_nonce_field( 'update', '_wpnonce_update-denied-student-status' ); ?>
				</div>
			</form>
		</div>
		<?php
	}
}
