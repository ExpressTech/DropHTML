<?php
namespace DropHTML\Admin;
use DropHTML\Frontend\ContentsView;
use WP_Error;

/**
 * Upload Screen with a listing Interface
 */
class Upload {
    
    /**
     * Main Plugin construct
     */
    public function __construct() {

        require_once(ABSPATH . '/wp-admin/includes/file.php');
        WP_Filesystem();

        $this->post_type = 'drop';
        $this->contentsView = new ContentsView();

        add_action('admin_enqueue_scripts', [$this->contentsView, 'wp_enqueue_style' ]);
        add_action('post_edit_form_tag', [ $this, 'update_edit_form' ]);
        add_action('add_meta_boxes', [ $this, 'add_custom_meta_boxes' ]);  
        add_action('save_post', [ $this, 'save_custom_meta_data' ]);
        add_action("manage_posts_custom_column", [$this, 'custom_columns']);

        add_filter("manage_edit-{$this->post_type}_columns",  [$this, 'add_new_columns']);
        add_filter("manage_edit-{$this->post_type}_sortable_columns", [$this, 'register_sortable_columns']);
        add_filter('post_row_actions', [$this, 'action_row'], 10, 2);
        add_action('before_delete_post', [$this, 'delete_all_attached_media']);

		add_action('wp_ajax_delete_tree_file', [$this, 'delete_tree_file']);
		add_action('wp_ajax_save_tree_file', [$this, 'save_tree_file']);
	}

    /**
     * Helper function to check correct post type.
     */
    public function isValidPostType() {
        if ( $this->post_type === get_post_type() ) {
            return true;
        }
        return false;
    }

    /**
     * Load pload metabox
     */
    public function add_custom_meta_boxes() {  
        add_meta_box('wp_custom_attachment', __( 'Drop File', 'drophtml' ), [ $this, 'wp_custom_attachment' ], $this->post_type, 'normal', 'high');  
    }

    /**
     * Unpack a compressed package file.
     *
     * @since 2.8.0
     *
     * @global WP_Filesystem_Base $wp_filesystem WordPress filesystem subclass.
     *
     * @param string $package        Full path to the package file.
     * @param bool   $delete_package Optional. Whether to delete the package file after attempting
     *                               to unpack it. Default true.
     * @return string|WP_Error The path to the unpacked contents, or a WP_Error on failure.
     */
    public function unZipToBaseFolder($id, $package, $filename, $delete_package = false)
    {

        global $wp_filesystem;
        $post = get_post($id);

        $fileBaseName = basename(basename($package, '.tmp'), '.zip');
        $originalBaseName = basename(basename($filename, '.tmp'), '.zip');
        $folderName = sanitize_title( $post->post_title, $fileBaseName );

        // We need a working directory - Strip off any .tmp or .zip suffixes
        // $basename = $upgrade_folder . basename(basename($package, '.tmp'), '.zip');
        $working_dir = trailingslashit(ABSPATH) . trailingslashit('drop'). $folderName;

        // Clean up working directory
        if ($wp_filesystem->is_dir($working_dir)) {
            $wp_filesystem->delete($working_dir, true);
        }

        // Unzip package to working directory
        $result = unzip_file($package, $working_dir);

        // Once extracted, delete the package if required.
        if ($delete_package) {
            unlink($package);
        }
  
        if ( is_wp_error($result)) {
            $wp_filesystem->delete($working_dir, true);
            if ('incompatible_archive' == $result->get_error_code()) {
                return new WP_Error('incompatible_archive', $this->strings['incompatible_archive'], $result->get_error_data());
            }
            return $result;
        } else {
            copy_dir( trailingslashit($working_dir) . trailingslashit($originalBaseName), $working_dir );
            $wp_filesystem->delete(trailingslashit($working_dir) . trailingslashit($originalBaseName), true);
            update_post_meta($id, 'drop_preview_url', trailingslashit(get_site_url()) . trailingslashit('drop') . $folderName);
        }

        return $working_dir;
    }

    /**
     * Extract files
     */
    function extractFiles($file)
    {

        if ($file) {
            //get the url
            $url = $file['url'];

            //Replace url to directory path
            $path = str_replace(site_url('/'), ABSPATH, esc_url($url));

            if (is_file($path)) {
                // get the absolute path to $file
                $path = pathinfo(realpath($file['file']), PATHINFO_DIRNAME);
                return true;
            }
        }

        return false;
    }
   
    /**
     * Validate and Save uplaoded file 
     */
    public function save_custom_meta_data($id) {
        if ( ! $this->isValidPostType() ) {
            return false;
        }
		if (isset($_POST['post_name']) && !empty($_POST['post_name'])) {
			$post = get_post($id);
			$newslug = $_POST['post_name'];
			$drop_url = get_post_meta($id, 'drop_preview_url', true);
			$new_drop_url = trailingslashit(get_site_url()) . trailingslashit('drop') . $newslug;
			/**
			 * Rename Folder
			 */
			$oldpath = str_replace(site_url('/'), ABSPATH, esc_url($drop_url));
			$newpath = str_replace(site_url('/'), ABSPATH, esc_url($new_drop_url));
			if (rename($oldpath, $newpath)) {
				update_post_meta($id, 'drop_preview_url', $new_drop_url);
			}
		}
        if(!empty($_FILES['wp_custom_attachment']['name'])) {
            $supported_types = array('application/zip', 'application/octet-stream', 'application/x-zip-compressed','multipart/x-zip');
            $arr_file_type = wp_check_filetype(basename($_FILES['wp_custom_attachment']['name']));
            $uploaded_type = $arr_file_type['type'];
    
            if( in_array($uploaded_type, $supported_types) ) {
                $upload = wp_upload_bits($_FILES['wp_custom_attachment']['name'], null, file_get_contents($_FILES['wp_custom_attachment']['tmp_name']));
                if(isset($upload['error']) && $upload['error'] != 0) {
                    wp_die( __( 'There was an error uploading your file. The error is:', 'drophtml' ) . ' ' . $upload['error']);
                } else {
                    $extract = $this->unZipToBaseFolder($id, $upload['file'], $_FILES['wp_custom_attachment']['name'], false );
                    if ($extract) {
                        update_post_meta($id, 'wp_custom_attachment', $upload);
                    } else {
                        wp_die( __( 'Error extracting the file.', 'drophtml') );
                    }
                }
            } else {
                wp_die(__('The file type that you\'ve uploaded is not a ZIP file.', 'drophtml') );
            }
        }
    }
    
    /**
     * Set upload form enctype type
     */
    public function update_edit_form() {
        if ( ! $this->isValidPostType() ) {
            return false;
        }
        echo ' enctype="multipart/form-data"';
    }

    /**
     * Show file List
     */
    public function wp_custom_attachment() {
        echo $this->contentsView->showFileList(get_the_ID());
    }

    /**
     * Add new columns to the post table
     *
     * @param Array $columns - Current columns on the list post
     */
    public function add_new_columns($columns){
        $column_meta = array( 'username' => __( 'Username', 'drophtml' ) );
        $columns = array_slice( $columns, 0, 6, true ) + $column_meta + array_slice( $columns, 6, NULL, true );
        return $columns;
    }

    // Register the columns as sortable
    public function register_sortable_columns( $columns ) {
        $columns['username'] = 'username';
        return $columns;
    }

    /**
    * Display data in new columns
    *
    * @param  $column Current column
    *
    * @return Data for the column
    */
    public function custom_columns($column) {
        global $post;

        switch ( $column ) {
            case 'username':
                echo get_the_author_meta( 'user_login', $post->post_author );
                break;
        }
    }

    function action_row($actions, $post){
        if ( $post->post_type === $this->post_type ) {
            unset($actions['view']);
            unset($actions['inline hide-if-no-js']);
            $url = get_post_meta($post->ID, 'drop_preview_url', true);
            $actions['demo'] = '<a href="'. $url . '/" target="_blank">'. __( 'Preview', 'drophtml' ) . '</a>';
        }
        return $actions;
    }

    function delete_all_attached_media( $post_id ) {

        if( get_post_type($post_id) === $this->post_type ) {
            $attachments = get_attached_media( '', $post_id );

            foreach ($attachments as $attachment) {
                wp_delete_attachment( $attachment->ID, 'true' );
            }
        }
    }

	function delete_tree_file() {
		$result = '0';
		if (isset($_POST['file']) && !empty($_POST['file'])) {
			/*$zipfile = $_POST['zip'];
			$zip = new \ZipArchive();
			$zip->open($zipfile);
			$zip->deleteName($file);
			$zip->close();*/

			$file = $_POST['file'];
			$file_path = trailingslashit(ABSPATH) . $file;
			if (file_exists($file_path)) {
				unlink($file_path);
			}
			$result = '1';
		}
		echo $result;
		exit;
	}
	
	function save_tree_file() {
		$result = '0';
		if (isset($_POST['file']) && !empty($_POST['file'])) {
			$file = $_POST['file'];
			$content = $_POST['content'];
			$content = stripslashes($content);
			$file_path = trailingslashit(ABSPATH) . $file;
			
			$update = file_put_contents($file_path, $content);
			if ($update !== false) {
				$result = '1';
			}
		}
		echo $result;
		exit;
	}

}