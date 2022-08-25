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

