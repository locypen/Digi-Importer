<?php
/**
 * Plugin Name: Digikala Product Importer
 * Plugin URI: https://yourwebsite.com
 * Description: افزونه وارد کردن محصولات از دیجی‌کالا به ووکامرس
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: digikala-importer
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

// تعریف ثابت‌های پلاگین
define('DIGIKALA_IMPORTER_VERSION', '1.0.0');
define('DIGIKALA_IMPORTER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DIGIKALA_IMPORTER_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * کلاس اصلی پلاگین
 */
class Digikala_Product_Importer {
    
    private static $instance = null;
    
    /**
     * متد singleton برای دریافت نمونه واحد از کلاس
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * سازنده کلاس
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'check_woocommerce'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * راه‌اندازی اولیه پلاگین
     */
    public function init() {
        // بارگذاری فایل‌های ترجمه
        load_plugin_textdomain('digikala-importer', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // راه‌اندازی هوک‌های مدیریتی
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            add_action('wp_ajax_digikala_import_product', array($this, 'ajax_import_product'));
            add_action('wp_ajax_digikala_preview_product', array($this, 'ajax_preview_product'));
        }
    }
    
    /**
     * بررسی فعال بودن ووکامرس
     */
    public function check_woocommerce() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
    }
    
    /**
     * نمایش پیام خطا در صورت عدم وجود ووکامرس
     */
    public function woocommerce_missing_notice() {
        echo '<div class="notice notice-error"><p>';
        echo __('برای استفاده از افزونه Digikala Product Importer، باید ووکامرس نصب و فعال باشد.', 'digikala-importer');
        echo '</p></div>';
    }
    
    /**
     * افزودن منوی مدیریت
     */
    public function add_admin_menu() {
        add_menu_page(
            __('وارد کردن از دیجی‌کالا', 'digikala-importer'),
            __('دیجی‌کالا', 'digikala-importer'),
            'manage_woocommerce',
            'digikala-importer',
            array($this, 'admin_page'),
            'dashicons-download',
            56
        );
        
        add_submenu_page(
            'digikala-importer',
            __('تنظیمات', 'digikala-importer'),
            __('تنظیمات', 'digikala-importer'),
            'manage_woocommerce',
            'digikala-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * بارگذاری اسکریپت‌ها و استایل‌های مدیریت
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'digikala') === false) {
            return;
        }
        
        wp_enqueue_script(
            'digikala-admin',
            DIGIKALA_IMPORTER_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            DIGIKALA_IMPORTER_VERSION,
            true
        );
        
        wp_enqueue_style(
            'digikala-admin',
            DIGIKALA_IMPORTER_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            DIGIKALA_IMPORTER_VERSION
        );
        
        wp_localize_script('digikala-admin', 'digikala_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('digikala_nonce'),
            'messages' => array(
                'loading' => __('در حال پردازش...', 'digikala-importer'),
                'error' => __('خطا در پردازش درخواست', 'digikala-importer'),
                'success' => __('محصول با موفقیت وارد شد', 'digikala-importer'),
            )
        ));
    }
    
    /**
     * صفحه اصلی مدیریت
     */
    public function admin_page() {
        include_once DIGIKALA_IMPORTER_PLUGIN_DIR . 'includes/admin-page.php';
    }
    
    /**
     * صفحه تنظیمات
     */
    public function settings_page() {
        include_once DIGIKALA_IMPORTER_PLUGIN_DIR . 'includes/settings-page.php';
    }
    
    /**
     * پردازش درخواست AJAX برای پیش‌نمایش محصول
     */
    public function ajax_preview_product() {
        check_ajax_referer('digikala_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('شما مجوز انجام این عمل را ندارید.', 'digikala-importer'));
        }
        
        $url = sanitize_url($_POST['product_url']);
        $product_id = $this->extract_product_id($url);
        
        if (!$product_id) {
            wp_send_json_error(__('لینک محصول معتبر نیست.', 'digikala-importer'));
        }
        
        $product_data = $this->fetch_digikala_data($product_id);
        
        if (!$product_data) {
            wp_send_json_error(__('خطا در دریافت اطلاعات محصول.', 'digikala-importer'));
        }
        
        $preview_data = $this->prepare_preview_data($product_data);
        wp_send_json_success($preview_data);
    }
    
    /**
     * پردازش درخواست AJAX برای وارد کردن محصول
     */
    public function ajax_import_product() {
        check_ajax_referer('digikala_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('شما مجوز انجام این عمل را ندارید.', 'digikala-importer'));
        }
        
        $url = sanitize_url($_POST['product_url']);
        $custom_settings = $_POST['custom_settings'] ?? array();
        
        $product_id = $this->extract_product_id($url);
        
        if (!$product_id) {
            wp_send_json_error(__('لینک محصول معتبر نیست.', 'digikala-importer'));
        }
        
        $product_data = $this->fetch_digikala_data($product_id);
        
        if (!$product_data) {
            wp_send_json_error(__('خطا در دریافت اطلاعات محصول.', 'digikala-importer'));
        }
        
        $woo_product_id = $this->create_woocommerce_product($product_data, $custom_settings);
        
        if ($woo_product_id) {
            wp_send_json_success(array(
                'product_id' => $woo_product_id,
                'edit_url' => admin_url('post.php?post=' . $woo_product_id . '&action=edit')
            ));
        } else {
            wp_send_json_error(__('خطا در ایجاد محصول.', 'digikala-importer'));
        }
    }
    
    /**
     * استخراج شناسه محصول از URL
     */
    private function extract_product_id($url) {
        // الگوهای مختلف URL دیجی‌کالا
        $patterns = array(
            '/digikala\.com\/product\/dkp-(\d+)/',
            '/digikala\.com\/product\/.*-dkp-(\d+)/',
            '/dkp-(\d+)/'
        );
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }
        
        return false;
    }
    
   /**
 * دریافت اطلاعات محصول از API دیجی‌کالا
 */
private function fetch_digikala_data($product_id) {
    // URL API دیجی‌کالا
    $api_url = "https://api.digikala.com/v2/product/{$product_id}/";
    
    // تنظیمات درخواست
    $args = array(
        'timeout' => 30,
        'headers' => array(
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Accept' => 'application/json',
            'Accept-Language' => 'fa-IR,fa;q=0.9,en-US;q=0.8,en;q=0.7'
        )
    );
    
    $response = wp_remote_get($api_url, $args);
    
    if (is_wp_error($response)) {
        error_log('Digikala API Error: ' . $response->get_error_message());
        return false;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        error_log('Digikala API HTTP Error: ' . $response_code);
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Digikala API JSON Error: ' . json_last_error_msg());
        return false;
    }
    
    return $data;
}
    
  /**
 * آماده‌سازی داده‌های پیش‌نمایش
 */
private function prepare_preview_data($digikala_data) {
    if (!isset($digikala_data['data']['product'])) {
        return false;
    }
    
    $product = $digikala_data['data']['product'];
    
    // استخراج تصاویر
    $images = array(
    'main' => $product['images']['main']['url'][0] ?? '',
    'gallery' => array()
);

if (!empty($product['images']['list'])) {
    foreach ($product['images']['list'] as $image) {
        $img_url = $image['url'][0] ?? $image['url'] ?? '';
        if ($img_url) {
            $images['gallery'][] = $img_url;
        }
    }
}
    
    // استخراج ویژگی‌ها
    $specifications = array();
    if (!empty($product['review']['attributes'])) {
        foreach ($product['review']['attributes'] as $attr) {
            $specifications[] = array(
                'title' => $attr['title'] ?? '',
                'value' => isset($attr['values'][0]) ? implode(', ', $attr['values']) : ''
            );
        }
    }
    
    return array(
        'title' => $product['title_fa'] ?? '',
        'price' => array(
            'regular' => $product['default_variant']['price']['rrp_price'] ?? 0,
            'sale' => $product['default_variant']['price']['selling_price'] ?? 0,
            'discount' => $product['default_variant']['price']['discount_percent'] ?? 0
        ),
        'description' => array(
            'short' => $product['review']['description'] ?? '',
            'full' => $product['review']['description'] ?? '' // در API جدید expert_reviews وجود ندارد
        ),
        'images' => $images,
        'category' => $product['category']['title_fa'] ?? $product['category'] ?? $product['data_layer']['category'] ?? '',
        'brand' => $product['brand']['title_fa'] ?? $product['brand'] ?? $product['data_layer']['brand'] ?? '',
        'availability' => ($product['default_variant']['status'] ?? '') === 'marketable',
        'specifications' => $specifications
    );
}

    /**
     * ایجاد محصول ووکامرس
     */
    private function create_woocommerce_product($digikala_data, $custom_settings = array()) {
        if (!isset($digikala_data['data']['product'])) {
            return false;
        }
        
        $product_data = $digikala_data['data']['product'];
        
        // ایجاد محصول جدید
        $product = new WC_Product_Simple();
        
        // تنظیم اطلاعات پایه
        $product->set_name($product_data['title_fa'] ?? '');
        $product->set_regular_price($product_data['default_variant']['price']['rrp_price'] ?? 0);
        $product->set_sale_price($product_data['default_variant']['price']['selling_price'] ?? 0);
        
        // تنظیم توضیحات
        $product->set_short_description($product_data['review']['description'] ?? '');
        $product->set_description($product_data['review']['description'] ?? '');
        
        // تنظیم وضعیت موجودی
        $is_in_stock = ($product_data['default_variant']['status'] ?? '') === 'marketable';
        $product->set_stock_status($is_in_stock ? 'instock' : 'outofstock');
        
        // اعمال تنظیمات سفارشی
        if (!empty($custom_settings['warranty'])) {
            $product->update_meta_data('_warranty', sanitize_text_field($custom_settings['warranty']));
        }
        
        if (!empty($custom_settings['return_policy'])) {
            $product->update_meta_data('_return_policy', sanitize_textarea_field($custom_settings['return_policy']));
        }
        
        // ذخیره محصول
        $product_id = $product->save();
        
        if ($product_id) {
        // تنظیم دسته‌بندی
        $this->set_product_category($product_id, $product_data['category']['title_fa'] ?? $product_data['category'] ?? $product_data['data_layer']['category'] ?? '');

        // تنظیم برند
        $this->set_product_brand($product_id, $product_data['brand']['title_fa'] ?? $product_data['brand'] ?? $product_data['data_layer']['brand'] ?? '');
            // تنظیم تصاویر
            $this->set_product_images($product_id, $product_data['images'] ?? array());
            
            // تنظیم ویژگی‌ها
            $this->set_product_attributes($product_id, $product_data['review']['attributes'] ?? array());        }
        
        return $product_id;
    }
    
    /**
     * تنظیم دسته‌بندی محصول
     */
    private function set_product_category($product_id, $category_name) {
        if (empty($category_name)) {
            return;
        }
        
        $term = get_term_by('name', $category_name, 'product_cat');
        
        if (!$term) {
            $term = wp_insert_term($category_name, 'product_cat');
            if (!is_wp_error($term)) {
                $term_id = $term['term_id'];
            } else {
                return;
            }
        } else {
            $term_id = $term->term_id;
        }
        
        wp_set_object_terms($product_id, $term_id, 'product_cat');
    }
    
    /**
     * تنظیم برند محصول
     */
    private function set_product_brand($product_id, $brand_name) {
        if (empty($brand_name)) {
            return;
        }
        
        // بررسی وجود تکسونومی برند
        if (!taxonomy_exists('product_brand')) {
            return;
        }
        
        $term = get_term_by('name', $brand_name, 'product_brand');
        
        if (!$term) {
            $term = wp_insert_term($brand_name, 'product_brand');
            if (!is_wp_error($term)) {
                $term_id = $term['term_id'];
            } else {
                return;
            }
        } else {
            $term_id = $term->term_id;
        }
        
        wp_set_object_terms($product_id, $term_id, 'product_brand');
    }
    
    /**
     * تنظیم تصاویر محصول
     */
    private function set_product_images($product_id, $images_data) {
        if (empty($images_data)) {
            return;
        }
        
        $image_ids = array();
        
        // تصویر اصلی
        if (!empty($images_data['main']['url'][0])) {
            $image_id = $this->upload_image_from_url($images_data['main']['url'][0], $product_id);
            if ($image_id) {
                set_post_thumbnail($product_id, $image_id);
                $image_ids[] = $image_id;
            }
        }
        
      // گالری تصاویر
if (!empty($images_data['list'])) {
    foreach (array_slice($images_data['list'], 1, 4) as $image_item) {
        $img_url = $image_item['url'][0] ?? $image_item['url'] ?? '';
        if ($img_url) {
            $image_id = $this->upload_image_from_url($img_url, $product_id);
            if ($image_id) {
                $image_ids[] = $image_id;
            }
        }
    }
}
    
    /**
     * آپلود تصویر از URL
     */
    private function upload_image_from_url($image_url, $product_id) {
        if (empty($image_url)) {
            return false;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        $tmp = download_url($image_url);
        
        if (is_wp_error($tmp)) {
            return false;
        }
        
        $file_array = array(
            'name' => basename($image_url) . '.jpg',
            'tmp_name' => $tmp
        );
        
        $id = media_handle_sideload($file_array, $product_id);
        
        if (is_wp_error($id)) {
            @unlink($tmp);
            return false;
        }
        
        return $id;
    }
    
    /**
     * تنظیم ویژگی‌های محصول
     */
    private function set_product_attributes($product_id, $specifications) {
        if (empty($specifications)) {
            return;
        }
        
        $attributes = array();
        $position = 0;
        
        foreach ($specifications as $spec) {
            $attribute_name = $spec['title'] ?? '';
            $attribute_value = $spec['values'][0]['title'] ?? '';
            
            if (empty($attribute_name) || empty($attribute_value)) {
                continue;
            }
            
            $attribute_slug = wc_sanitize_taxonomy_name($attribute_name);
            
            $attributes[$attribute_slug] = array(
                'name' => $attribute_name,
                'value' => $attribute_value,
                'position' => $position++,
                'is_visible' => 1,
                'is_variation' => 0,
                'is_taxonomy' => 0
            );
        }
        
        update_post_meta($product_id, '_product_attributes', $attributes);
    }
    
    /**
     * فعال‌سازی پلاگین
     */
    public function activate() {
        // ایجاد جداول مورد نیاز (در صورت نیاز)
        $this->create_tables();
        
        // تنظیمات پیش‌فرض
        add_option('digikala_importer_settings', array(
            'default_warranty' => '18 ماه گارانتی شرکتی',
            'default_return_policy' => 'امکان بازگشت کالا تا 7 روز',
            'auto_publish' => true,
            'import_images' => true,
            'import_attributes' => true
        ));
    }
    
    /**
     * غیرفعال‌سازی پلاگین
     */
    public function deactivate() {
        // پاک‌سازی cache و فایل‌های موقت
        wp_clear_scheduled_hook('digikala_sync_products');
    }
    
    /**
     * ایجاد جداول مورد نیاز
     */
    private function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'digikala_imports';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            digikala_id varchar(20) NOT NULL,
            woo_product_id int(11) NOT NULL,
            import_date datetime DEFAULT CURRENT_TIMESTAMP,
            last_sync datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'active',
            PRIMARY KEY (id),
            UNIQUE KEY digikala_id (digikala_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// راه‌اندازی پلاگین
function digikala_importer_init() {
    return Digikala_Product_Importer::get_instance();
}

add_action('plugins_loaded', 'digikala_importer_init');
