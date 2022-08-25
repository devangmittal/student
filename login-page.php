<?php

namespace Devang\login_page;
function redirect_to_student_logged_in(){
	if ( is_user_logged_in( ) ) {
		echo 'hello user';
	} else {
		echo 'hello';
	}
}
function student_login_form_callable() {
	redirect_to_student_logged_in();
}
add_shortcode('student_login_form', __NAMESPACE__ . '\student_login_form_callable');
