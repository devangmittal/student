<?php
/**
 * Add menu settings in admin panel.
 *
 * @package student
 */

namespace Devang\Admin_Setting;

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
 * Display Select Tag.
 *
 * @param string $name Name for select tag.
 * @param string $id Id for select tag.
 * @param array  $args Array of key value pairs.
 * @return void
 */
function render_select_tag( $name, $id, $args ) {
	?>
	<select name = "<?php $name; ?>" id="<?php $id; ?>" onchange="this.form.submit()">
		<?php
		display_select_option_loop( $args, filter_input( INPUT_POST, $name ) );
		?>
	</select>
	<?php
}

/**
 * Renders submit buttom HTML Whose name is key and button text is value given to the key.
 *
 * @param array $array array of key value pairs.
 * @return void
 */
function render_submit_button_loop( $array ) {
	foreach ( $array as $key => $value ) {
		?>
		<button type="submit" name="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></button>
		<?php
	}
}

/**
 * Renders label HTML using key value pairs
 * in which for attribute has the key and the label text is value of given to key.
 *
 * @param array $array array of key value pairs.
 * @return void
 */
function render_label( $array ) {
	foreach ( $array as $key => $value ) {
		?>
		<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?> </label> <br> 
		<?php
	}
}

/**
 * Render students list.
 *
 * @param array  $user_args Array of key value pairs for get_users function.
 * @param string $text Text to display if no student found.
 * @param array  $label_args Array of key value pairs.
 * @param array  $button_args Array of key value pairs.
 * @return void
 */
function render_student_from_meta( $user_args, $text, $label_args, $button_args ) {
	$users = get_users( $user_args );
	if ( empty( $users ) ) {
		?>
		<div>
			<p>
				<?php echo esc_html( $text ); ?>
			</p>
		</div>
		<?php
	} else {
		?>
		<div>
			<form action="<?php filter_input( INPUT_SERVER, 'REQUEST_URI' ); ?>" method="post">
				<div>
					<div>
						<?php
						render_label( $label_args );
						foreach ( $users as $user ) {
							?>
							<input type="checkbox" name="student_status[]" value="<?php echo esc_html( $user->ID ); ?>"><?php echo esc_html( get_user_meta( $user->ID, 'nickname', true ) ); ?><br>
							<?php
						}
						render_submit_button_loop( $button_args );
						?>
					</div>
					<?php wp_nonce_field( 'update', '_wpnonce_update-student-status' ); ?>
				</div>
			</form>
		</div>
		<?php
	}

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
}


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
	// Sub menu Apprived Students of Students custom menu.
	add_submenu_page(
		'all_students',
		'Approved Students',
		'Approved Students',
		'manage_options',
		'approved_students',
		__NAMESPACE__ . '\approved_callback',
	);
	// Sub menu Pending Students of Students custom menu.
	add_submenu_page(
		'all_students',
		'Pending Students',
		'Pending Students',
		'manage_options',
		'pending_students',
		__NAMESPACE__ . '\pending_callback',
	);
	// Sub menu Denied Students of Students custom menu.
	add_submenu_page(
		'all_students',
		'Denied Students',
		'Denied Students',
		'manage_options',
		'denied_students',
		__NAMESPACE__ . '\denied_callback',
	);
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
 * Approve submenu callback.
 *
 * @return void
 */
function approved_callback() {
	$users_arg  = array(
		'role'       => 'student',
		'meta_key'   => 'user_status',
		'meta_value' => 'approved',
	);
	$text       = "There isn't any Approved students.";
	$label_args = array(
		'approved_student' => 'Approved Students: ',
	);

	$button_args = array(
		'pending' => 'Pending',
		'deny'    => 'Deny',
	);
	render_student_from_meta( $users_arg, $text, $label_args, $button_args );
}

/**
 * Pending submenu callback.
 *
 * @return void
 */
function pending_callback() {
	$users_arg  = array(
		'role'       => 'student',
		'meta_key'   => 'user_status',
		'meta_value' => 'pending',
	);
	$text       = "There isn't any Pending students.";
	$label_args = array(
		'pending_student' => 'Pending Students: ',
	);

	$button_args = array(
		'approve' => 'Approve',
		'deny'    => 'Deny',
	);
	render_student_from_meta( $users_arg, $text, $label_args, $button_args );
}

/**
 * Denied submenu callback.
 *
 * @return void
 */
function denied_callback() {
	$users_arg  = array(
		'role'       => 'student',
		'meta_key'   => 'user_status',
		'meta_value' => 'denied',
	);
	$text       = "There isn't any Denied students.";
	$label_args = array(
		'denied_student' => 'Denied Students: ',
	);

	$button_args = array(
		'approve' => 'Approve',
		'pending' => 'Pending',
	);
	render_student_from_meta( $users_arg, $text, $label_args, $button_args );
}
