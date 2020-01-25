<?php
namespace DropHTML\Frontend;
use PclZip;

/**
 * Upload Screen with a listing Interface
 */
class ContentsView
{

    /**
     * Main Plugin construct
     */
    public function __construct()
    {
        mbstring_binary_safe_encoding();

        require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );

        add_filter('wp_enqueue_scripts', [$this, 'wp_enqueue_style']);
        add_filter('single_template', [ $this, 'frontend_template' ]);
    }

    /**
     * Helper function to check correct post type.
     */
    public function isValidPostType()
    {
        if ('drop' == get_post_type()) {
            return true;
        }
        return false;
    }

    /**
     * Enqueue admin css
     */
    public function wp_enqueue_style()
    {
        if (!$this->isValidPostType()) {
            return false;
        }
        wp_enqueue_style('drops-upload-form', plugins_url('assets/admin/css/style.css', DROPHTML__FILE__), [], '1.0.0', 'all');
        wp_enqueue_script('drops-upload-form', plugins_url('assets/admin/js/main.js', DROPHTML__FILE__), ['jquery'], '1.0.0');
    }

    /**
     * Check if file has name
     */
    public function isFolder($path)
    {
        $path_info = pathinfo($path);
        return $path_info['extension'] ? false : true;
    }

    /**
     * Helper methong to list folders in a list
     */
    public function recursiveFileStructure($fileStructure)
    {
        $output = '';
        foreach ($fileStructure as $folder => $children) {
            if (is_array($children) && !empty($children)) {
                $output .= '<li>';
            }

            if ($folder !== 'file_name' && $folder !== 'children') {
                if ($this->isFolder($folder) && !empty($folder)) {
                    $output .= '<strong>' . $folder . '/</strong>';
                } elseif ($folder && !empty($folder)) {
                    $output .= '<li>' . $folder . '</li>';
                }
            }

            if (is_array($children) && !empty($children)) {
                $output .= '<ul class="folder">';
                $output .= $this->recursiveFileStructure($children);
                $output .= '</ul>';
            }

            if (is_array($children) && !empty($children)) {
                $output .= '</li>';
            }
        }
        return $output;
    }

    /**
     * Flatten file and folders strctre into an associative array
     */
    public function flatToTree($flat)
    {
        $tree_list = array();
        foreach ($flat as $file) {
            $list = explode('/', $file['filename']);
            $last_dir = &$tree_list;
            foreach ($list as $dir) {
                $last_dir = &$last_dir[$dir];
            }
            $last_dir = $file['filename'];
        }
        return $tree_list;
    }

    /**
     * List or show upload form
     */
    function showFileList( $id )
    {
        if (!$this->isValidPostType()) {
            return false;
        }

        $file = get_post_meta($id, 'wp_custom_attachment', true);

        if ($file) {

            mbstring_binary_safe_encoding();

            //get the url
            $url = $file['url'];

            //Replace url to directory path
            $path = str_replace(site_url('/'), ABSPATH, esc_url($url));

            if (is_file($path)) {
                $filesize = size_format(filesize($path));
                $filename = basename($path);

                $html = '<div>Name: ' . $filename . '</div>';
                $html .= '<div>Size: ' . $filesize . '</div>';
                $html .= '<div>Files:</div>';

                $zip = new PclZip($path);
                $fileStructure = $this->flatToTree($zip->listContent());

                $html .= '<pre class="file-list">';
                $html .= '<ul id="tree-list" class="tree-list" role="tree" aria-labelledby="plugin-files-label">';
                if ($fileStructure) {
                    $html .= $this->recursiveFileStructure($fileStructure);
                }
                $html .= '</ul>';
                $html .= '</pre>';
            }

            reset_mbstring_encoding();

        } else {
            wp_nonce_field(plugin_basename(__FILE__), 'wp_custom_attachment_nonce');
            $html = '<p class="description">';
            $html .= 'Upload your ZIP here.';
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
            return plugin_dir_path( DROPHTML__FILE__) . '/templates/single-drop.php';
        }
        
        return $template;
    }

}