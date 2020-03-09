jQuery(function ($) {
	/**
	 * Init File Editor
	 */
	jQuery(document).on('click', '.edit-tree-file', function () {
		var parent = jQuery(this).parents('div.tree-file').find('.tree-file-editable-area');
		var slug = jQuery(this).attr('data-slug');
		var ext = jQuery(this).attr('data-ext');
		var active = jQuery(this).attr('data-state');
		parent.slideToggle();
		if (active == '0') {
			jQuery(this).attr('data-state', '1');
			var settings = cm_settings[ext];
			var cm_editor = wp.codeEditor.initialize(jQuery('#editor-'+slug+'-textarea'), settings);
			parent.find('.CodeMirror-code').on('keyup',function(){
				$('#editor-'+slug+'-textarea').text(cm_editor.codemirror.getValue());
				$('#editor-'+slug+'-textarea').trigger('change');
			});
		}
	});
	/**
	 * Save File Content
	 */
	jQuery(document).on('click', '.save-tree-file', function () {
		var wrapper = jQuery(this).parents('.tree-file-editable-area');
		var file = jQuery(this).attr('data-file');
		var file_content = wrapper.find('textarea').text();
		save_file_data(wrapper, file, file_content);
	});
	jQuery(document).on('click', '.save-close-tree-file', function () {
		var wrapper = jQuery(this).parents('.tree-file-editable-area');
		var file = jQuery(this).attr('data-file');
		var file_content = wrapper.find('textarea').text();
		save_file_data(wrapper, file, file_content, true);
	});
	function save_file_data(wrapper, file, content, hide = false) {
		var form_data = new FormData();
		form_data.append('action', 'save_tree_file');
		form_data.append('file', file);
		form_data.append('content', content);
		jQuery.ajax({url: ajaxurl, type: "post", dataType: "text",
			cache: false,
			contentType: false,
			processData: false,
			data: form_data,
			success: function (msg) {
				if (msg != 1) {
					wrapper.find('.error-msg').show().delay(5000).fadeOut();
				} else {
					wrapper.find('.updated-msg').show().delay(5000).fadeOut();
					if (hide) {
						wrapper.slideToggle();
					}
				}
			},
			beforeSend: function () {
				wrapper.find('.save-loader').addClass('active');
			},
			complete: function () {
				wrapper.find('.save-loader').removeClass('active');
			}
		});
	}
	/**
	 * Delete File
	 */
	jQuery(document).on('click', '.delete-tree-file', function () {
		var $thisele = jQuery(this);
		var zip = jQuery('#zip-file-url').val();
		var file = jQuery(this).attr('data-file');
		if (confirm('Are you sure you want to remove this file?')) {
			jQuery.ajax({type: "post", dataType: "json",
				url: ajaxurl,
				data: {action: 'delete_tree_file', file: file, zip: zip},
				success: function (msg) {
					if (msg != 1) {
						return false;
					} else {
						$thisele.parents('div.jstree-anchor').parent('li').remove();
					}
				}
			});
		}
		return false;
	});
	/**
	 * Delete Folder
	 */
	jQuery(document).on('click', '.delete-tree-folder', function () {
		var $thisele = jQuery(this);
		var folder = jQuery(this).attr('data-folder');
		if (confirm('Are you sure you want to remove this folder?')) {
			jQuery.ajax({type: "post", dataType: "json",
				url: ajaxurl,
				data: {action: 'delete_tree_folder', folder: folder},
				success: function (msg) {
					if (msg != 1) {
						return false;
					} else {
						$thisele.parents('div.jstree-anchor').parent('li').remove();
					}
				}
			});
		}
		return false;
	});
	/**
	 * Upload Files
	 */
	jQuery(document).on('change', '.upload-to-tree-folder input', function (e) {
		e.stopPropagation();
		var folder = jQuery(this).parent('.upload-to-tree-folder').attr('data-folder');
		var form = new FormData();
		form.append('action', 'upload_to_tree_folder');
		form.append('folder', folder);
		for (var i = 0; i < jQuery(this).get(0).files.length; ++i) {
			form.append('tf[]', jQuery(this).get(0).files[i]);
		}
		jQuery.ajax({url: ajaxurl, type: "post", dataType: "text",
			cache: false,
			contentType: false,
			processData: false,
			data: form,
			success: function (msg) {
				if (msg != 1) {
					alert('There is an error while uploading file(s). Please try again later.');
				} else {
					alert('File(s) Uploaded Successfully!');
					location.reload();
				}
			},
			beforeSend: function () {
				jQuery('.upload-loader').addClass('active');
			},
			complete: function () {
				jQuery('.upload-loader').removeClass('active');
			}
		});
		
	});
});