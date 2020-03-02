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

        require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');

        add_filter('wp_enqueue_scripts', [$this, 'wp_enqueue_style']);
        add_filter('single_template', [$this, 'frontend_template']);
    }

    /**
     * Helper function to check correct post type.
     */
    public function isValidPostType()
    {
        if ('drop' === get_post_type()) {
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
        return (isset($path_info['extension'])) ? false : true;
    }

    /**
     * Helper method to list folders in a list
     */
    public function recursiveFileStructure($fileStructure, $url='')
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
					$file_path = str_replace(site_url('/'), '', esc_url($url));
					$file_url = $url.'/'.$children;
                    $output .= '<li class="tree-file">';
                    $output .= $folder;
                    $output .= '<div class="file-actions">';
                    $output .= '<a href="'.$file_url.'" target="_blank">'. __( 'Preview', 'drophtml' ) . '</a>';
                    //$output .= '<a href="#">'. __( 'Edit', 'drophtml' ) . '</a>';
                    $output .= '<a href="javascript:void(0)" class="delete-tree-file" data-path="'.$file_path.'" data-file="'.$children.'">'. __( 'Delete', 'drophtml' ) . '</a>';
                    $output .= '</div>';
                    $output .= '</li>';
                }
            }

            if (is_array($children) && !empty($children)) {
                $output .= '<ul class="folder">';
                $output .= $this->recursiveFileStructure($children, $url);
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
    function showFileList($id)
    {
        if (!$this->isValidPostType()) {
            return false;
        }

        $file = get_post_meta($id, 'wp_custom_attachment', true);
		$drop_url = get_post_meta($id, 'drop_preview_url', true);

        if ($file) {

            mbstring_binary_safe_encoding();

            //get the url
            $url = $file['url'];

            //Replace url to directory path
            $path = str_replace(site_url('/'), ABSPATH, esc_url($url));

            if (is_file($path)) {
                $filesize = size_format(filesize($path));
                $filename = basename($path);

                $html = '<div>' . __('Name:', 'drophtml') . ' ' . $filename . '</div>';
                $html .= '<div>' . __('Size:', 'drophtml') . ' ' . $filesize . '</div>';
                $html .= '<div>' . __('Files:', 'drophtml') . '</div>';

                $zip = new PclZip($path);
                $fileStructure = $this->flatToTree($zip->listContent());

                $html .= '<pre class="file-list">';
                $html .= '<ul id="tree-list" class="tree-list" role="tree" aria-labelledby="plugin-files-label">';
                if ($fileStructure) {
                    $html .= $this->recursiveFileStructure($fileStructure, $drop_url);
                }
                $html .= '</ul>';
                $html .= '<input type="hidden" id="zip-file-url" value="'.$path.'">';
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

    function frontend_template($template)
    {
        global $post;

        if ($this->isValidPostType()) {
            return plugin_dir_path(DROPHTML__FILE__) . '/templates/single-drop.php';
        }

        return $template;
    }
}
