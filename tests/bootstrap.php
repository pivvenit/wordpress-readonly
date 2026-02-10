<?php
/**
 * PHPUnit bootstrap file for WordPress Readonly plugin tests
 */

// Mock WordPress functions for testing
if (!function_exists('add_filter')) {
    function add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return true;
    }
}

if (!function_exists('add_action')) {
    function add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1)
    {
        return add_filter($tag, $function_to_add, $priority, $accepted_args);
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false)
    {
        return $GLOBALS['wp_options'][$option] ?? $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value)
    {
        $GLOBALS['wp_options'][$option] = $value;
        return true;
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default')
    {
        return $text;
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false)
    {
        return true;
    }
}

if (!function_exists('plugins_url')) {
    function plugins_url($path = '', $plugin = '')
    {
        return 'http://example.com/wp-content/plugins/' . $path;
    }
}

if (!function_exists('wp_set_script_translations')) {
    function wp_set_script_translations($handle, $domain = 'default', $path = null)
    {
        return true;
    }
}

if (!function_exists('wp_is_json_request')) {
    function wp_is_json_request()
    {
        return false;
    }
}

if (!function_exists('wp_send_json')) {
    function wp_send_json($response, $status_code = null)
    {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}

if (!function_exists('wp_die')) {
    function wp_die($message = '')
    {
        exit($message);
    }
}

if (!class_exists('WP_Error')) {
    class WP_Error
    {
        public $errors = [];
        public $error_data = [];
        
        public function __construct($code = '', $message = '', $data = '')
        {
            if (empty($code)) {
                return;
            }
            $this->errors[$code][] = $message;
            if (!empty($data)) {
                $this->error_data[$code] = $data;
            }
        }
        
        public function get_error_code()
        {
            return !empty($this->errors) ? array_key_first($this->errors) : '';
        }
        
        public function get_error_message($code = '')
        {
            if (empty($code)) {
                $code = $this->get_error_code();
            }
            return $this->errors[$code][0] ?? '';
        }
    }
}

// Initialize global options array
$GLOBALS['wp_options'] = [];
