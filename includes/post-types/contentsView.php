<?php

namespace DropHTML\Frontend;

use PclZip;

/**
 * Upload Screen with a listing Interface
 */
class ContentsView {

	/**
	 * Main Plugin construct
	 */
	public function __construct() {
		mbstring_binary_safe_encoding();

		require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');

		add_filter('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
		add_filter('wp_enqueue_scripts', [$this, 'wp_enqueue_style']);
		add_filter('single_template', [$this, 'frontend_template']);
	}

	/**
	 * Helper function to check correct post type.
	 */
	public function isValidPostType() {
		if ('drop' === get_post_type()) {
			return true;
		}
		return false;
	}

	/**
	 * Enqueue admin assets
	 */
	public function admin_enqueue_scripts() {
		if (!$this->isValidPostType()) {
			return false;
		}
		$cm_settings['php'] = wp_enqueue_code_editor(array('file' => 'example.php', 'codemirror' => array('autoRefresh' => true), 'htmlhint' => array('space-tab-mixed-disabled' => 'space')));
		$cm_settings['html'] = wp_enqueue_code_editor(array('file' => 'example.html', 'codemirror' => array('autoRefresh' => true), 'htmlhint' => array('space-tab-mixed-disabled' => 'space')));
		$cm_settings['css'] = wp_enqueue_code_editor(array('file' => 'example.css', 'codemirror' => array('autoRefresh' => true)));
		$cm_settings['js'] = wp_enqueue_code_editor(array('file' => 'example.js', 'codemirror' => array('autoRefresh' => true)));

		wp_localize_script('jquery', 'cm_settings', $cm_settings);
		wp_enqueue_script('wp-theme-plugin-editor');
		wp_enqueue_style('wp-codemirror');
	}
	public function wp_enqueue_style() {
		if (!$this->isValidPostType()) {
			return false;
		}
		wp_enqueue_style('drops-upload-form', plugins_url('assets/admin/css/style.css', DROPHTML__FILE__), [], '1.0.0', 'all');
		wp_enqueue_script('drops-upload-form', plugins_url('assets/admin/js/main.js', DROPHTML__FILE__), ['jquery'], '1.0.0');
	}

	/**
	 * Check if file has name
	 */
	public function isFolder($path) {
		$path_info = pathinfo($path);
		return (isset($path_info['extension'])) ? false : true;
	}

	/**
	 * Check if file is allowed for edit
	 */
	public function getFileExt($path) {
		$ext = '';
		$parts = pathinfo($path);
		if (isset($parts['extension'])) {
			$ext = $parts['extension'];
		}
		return $ext;
	}
	public function isAllowEdit($path) {
		$allow_ext = array('php', 'html', 'css', 'js');
		if (is_file($path)) {
			$ext = $this->getFileExt($path);
			if (in_array($ext, $allow_ext)) {
				return true;
			}
		}
		return false;
	}

	function listFolderFiles($dir, $url = '') {
		$ffs = scandir($dir);

		unset($ffs[array_search('.', $ffs, true)]);
		unset($ffs[array_search('..', $ffs, true)]);

		if (count($ffs) < 1) {
			return;
		}
		$output = '';
		foreach ($ffs as $ff) {
			$output .= '<li>';
			$file_path = str_replace(ABSPATH, '', $dir) . '/' . $ff;
			$file_url = site_url('/') . $file_path;
			if (is_dir($dir . '/' . $ff)) {
				$output .= '<span class="folder-name">' . $ff . '/</span>';
				/*$output .= '<div class="tree-folder">';
					$output .= '<div class="folder-actions">';
						$output .= '<label title="'.__('Upload', 'drophtml').'" class="upload-to-tree-folder" target="_blank">';
							$output .= '<i class="dashicons dashicons-upload"></i>';
							$output .= '<input type="file" name="tf[]" multiple="multiple">';
						$output .= '</label>';
						$output .= '<span title="'.__('Delete', 'drophtml').'" class="delete-tree-folder" data-folder="' . $file_path . '"><i class="dashicons dashicons-trash"></i></span>';
					$output .= '</div>';
				$output .= '</div>';*/
			} else {
				$fileSlug = sanitize_title($file_path);
				$output .= '<div class="tree-file">' . $ff;
					$output .= '<div class="file-actions">';
						$output .= '<a href="' . $file_url . '" title="'.__('Preview', 'drophtml').'" target="_blank"><span class="dashicons dashicons-visibility"></span></a>';
						if ($this->isAllowEdit($dir . '/' . $ff)) {
							$output .= '<a href="javascript:void(0)" title="'.__('Edit', 'drophtml').'" class="edit-tree-file" data-ext="'.$this->getFileExt($ff).'" data-slug="'.$fileSlug.'" data-file="' . $file_path . '" data-state="0"><span class="dashicons dashicons-edit"></span></a>';
						}
						$output .= '<a href="javascript:void(0)" title="'.__('Delete', 'drophtml').'" class="delete-tree-file" data-file="' . $file_path . '"><span class="dashicons dashicons-trash"></span></a>';
					$output .= '</div>';
					if ($this->isAllowEdit($dir . '/' . $ff)) {
						$content = file_get_contents($dir . '/' . $ff);
						$output .= '<div class="tree-file-editable-area" id="editor-'.$fileSlug.'" style="display:none;">';
							$output .= '<div class="save-loader"><div class="ldr"><div></div><div></div></div></div>';
							$output .= '<textarea class="fancy-textarea" id="editor-'.$fileSlug.'-textarea">'.htmlentities($content).'</textarea>';
							$output .= '<div class="tree-file-editable-submit-area">';
							$output .= '<span class="updated-msg">'.__('Changes Saved!', 'drophtml').'</span>';
							$output .= '<span class="error-msg">'.__('Something went wrong.', 'drophtml').'</span>';
							$output .= '<input type="button" class="save-tree-file" value="Save" data-file="' . $file_path . '">';
							$output .= '<input type="button" class="save-close-tree-file" value="Save & Close" data-file="' . $file_path . '">';
							$output .= '</div>';
						$output .= '</div>';
					}
				$output .= '</div>';
			}
			if (is_dir($dir . '/' . $ff)) {
				$output .= '<ul class="folder">';
				$output .= $this->listFolderFiles($dir . '/' . $ff, $url);
				$output .= '</ul>';
			}
			$output .= '</li>';
		}

		return $output;
	}

	/**
	 * List or show upload form
	 */
	function showFileList($id) {
		if (!$this->isValidPostType()) {
			return false;
		}

		$file = get_post_meta($id, 'wp_custom_attachment', true);
		$drop_url = get_post_meta($id, 'drop_preview_url', true);
		$drop_path = str_replace(site_url('/'), ABSPATH, esc_url($drop_url));

		$html = '';
		if ($file) {
			mbstring_binary_safe_encoding();

			$site_url = site_url('/');
			$parsed_site_url = parse_url($site_url);
			$url = $file['url'];
			if ($parsed_site_url['scheme'] == 'https') {
				$url = str_replace("http://", "https://", $url);
			} else {
				$url = str_replace("https://", "http://", $url);
			}

			//Replace url to directory path
			$path = str_replace($site_url, ABSPATH, esc_url($url));

			if (is_file($path)) {
				$filesize = size_format(filesize($path));
				$filename = basename($path);

				$html = '<div>' . __('Name:', 'drophtml') . ' ' . $filename . '</div>';
				$html .= '<div>' . __('Size:', 'drophtml') . ' ' . $filesize . '</div>';
				$html .= '<div>' . __('Files:', 'drophtml') . '</div>';

				$html .= '<pre class="file-list">';
				$html .= '<ul id="tree-list" class="tree-list" role="tree" aria-labelledby="plugin-files-label">';
				$html .= $this->listFolderFiles($drop_path, $drop_url);
				$html .= '</ul>';
				$html .= '<input type="hidden" id="zip-file-url" value="' . $path . '">';
				$html .= '</pre>';
			}

			reset_mbstring_encoding();
		} else {
			wp_nonce_field(plugin_basename(__FILE__), 'wp_custom_attachment_nonce');
			$html = '<p class="description">';
			$html .= __('Upload your ZIP here.', 'drophtml');
			$html .= '</p>';
			$html .= '<input type="file" id="wp_custom_attachment" name="wp_custom_attachment" value="" size="25">';
		}

		$output = '<div class="drophtml-file-view">';
		$output .= $html;
		$output .= '</div>';

		return $output;
	}

	function frontend_template($template) {
		global $post;

		if ($this->isValidPostType()) {
			return plugin_dir_path(DROPHTML__FILE__) . '/templates/single-drop.php';
		}

		return $template;
	}

}
