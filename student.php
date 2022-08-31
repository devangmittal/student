<?php
/**
 * Plugin Name: Student
 * Plugin URI: https://github.com/devangmittal/student
 * Description: This is just a plugin build for learning WordPress.
 * Author: Devang Mittal
 * Version: 1.0.0
 * Author URI: http://eaxmple.com/
 * Text Domain: student
 * Domain Path: /languages

 * @package student
 */

namespace Devang\Student;

// define plugin path.
define( 'MY_PLUGIN_PAT', plugin_dir_path( __FILE__ ) );

// include shortcode.php file.
require_once MY_PLUGIN_PAT . 'shortcode.php';
require_once MY_PLUGIN_PAT . 'admin-setting.php';
require_once MY_PLUGIN_PAT . 'login-page.php';

add_role( 'student', 'Student' );
/**
 * Enqueue custom scripts.
 *
 * @return void
 */
function load_scripts() {
	wp_enqueue_script( 'ajax_update_student_meta', plugin_dir_url( __FILE__ ) . 'scripts/login-page.js', array( 'jquery' ), '1.0', true );
	wp_localize_script(
		'ajax_update_student_meta',
		'update_student_ajax',
		array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce( 'student_ajax_nonce' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\load_scripts' );

