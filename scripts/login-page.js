jQuery(document).ready(function ()
{
	jQuery("#update_student_meta").submit(function (e)
	{
		e.preventDefault();
		var url = update_student_ajax.ajax_url;
		var blog_url = jQuery(this).find('input[name="url"]').val();
		var first_name = jQuery(this).find('input[name="fname"]').val();
		var last_name = jQuery(this).find('input[name="lname"]').val();
		console.log(blog_url + first_name + last_name);
		jQuery.ajax(
			{
				url: update_student_ajax.ajax_url,
				type: 'POST',
				data: {
					action: 'update_user',
					url: blog_url,
					fname: first_name,
					lname: last_name,
					ajax_nonce: update_student_ajax.ajax_nonce,

				},
				error: function (error)
				{
					jQuery("#validation_error").html(error.responseText);
					console.log(error.responseText);
				},
				success: function (response)
				{
					jQuery("#validation_error").html(response.data);
					console.log(response);
				},
				complete: function ()
				{
					console.log("Field Updated");
				}
			});
	});
});