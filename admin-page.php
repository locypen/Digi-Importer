<?php
// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e('وارد کردن محصولات از دیجی‌کالا', 'digikala-importer'); ?></h1>
    
    <div class="digikala-importer-container">
        <!-- بخش وارد کردن لینک -->
        <div class="postbox">
            <h2 class="hndle"><?php _e('وارد کردن محصول جدید', 'digikala-importer'); ?></h2>
            <div class="inside">
                <form id="digikala-import-form">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="product_url"><?php _e('لینک محصول دیجی‌کالا', 'digikala-importer'); ?></label>
                            </th>
                            <td>
                                <input type="url" id="product_url" name="product_url" class="large-text" 
                                       placeholder="https://www.digikala.com/product/dkp-1234567/" />
                                <p class="description">
                                    <?php _e('لینک کامل صفحه محصول از دیجی‌کالا را وارد کنید', 'digikala-importer'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="button" id="preview-product" class="button">
                            <?php _e('پیش‌نمایش محصول', 'digikala-importer'); ?>
                        </button>
                        <button type="button" id="import-product" class="button button-primary" disabled>
                            <?php _e('وارد کردن محصول', 'digikala-importer'); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
        
        <!-- نتیجه پیش‌نمایش -->
        <div id="product-preview" class="postbox" style="display: none;">
            <h2 class="hndle"><?php _e('پیش‌نمایش محصول', 'digikala-importer'); ?></h2>
            <div class="inside">
                <div id="preview-content">
                    <!-- محتوا توسط JavaScript پر می‌شود -->
                </div>
            </div>
        </div>
        
        <!-- تنظیمات سفارشی -->
        <div id="custom-settings" class="postbox" style="display: none;">
            <h2 class="hndle"><?php _e('تنظیمات سفارشی', 'digikala-importer'); ?></h2>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="warranty"><?php _e('گارانتی محصول', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <select id="warranty" name="warranty">
                                <option value=""><?php _e('انتخاب کنید', 'digikala-importer'); ?></option>
                                <option value="18 ماه گارانتی شرکتی">18 ماه گارانتی شرکتی</option>
                                <option value="12 ماه گارانتی شرکتی">12 ماه گارانتی شرکتی</option>
                                <option value="24 ماه گارانتی شرکتی">24 ماه گارانتی شرکتی</option>
                                <option value="بدون گارانتی">بدون گارانتی</option>
                                <option value="custom"><?php _e('سایر...', 'digikala-importer'); ?></option>
                            </select>
                            <input type="text" id="warranty-custom" name="warranty_custom" 
                                   placeholder="گارانتی سفارشی را وارد کنید" style="display: none; margin-top: 5px;" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="return_policy"><?php _e('سیاست بازگشت کالا', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <textarea id="return_policy" name="return_policy" rows="3" class="large-text"
                                      placeholder="امکان بازگشت کالا تا 7 روز پس از خرید با حفظ شرایط اولیه"></textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="shipping_class"><?php _e('کلاس ارسال', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <select id="shipping_class" name="shipping_class">
                                <option value=""><?php _e('پیش‌فرض', 'digikala-importer'); ?></option>
                                <?php
                                $shipping_classes = WC()->shipping->get_shipping_classes();
                                foreach ($shipping_classes as $class) {
                                    echo '<option value="' . esc_attr($class->term_id) . '">' . esc_html($class->name) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="tax_status"><?php _e('وضعیت مالیاتی', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <select id="tax_status" name="tax_status">
                                <option value="taxable"><?php _e('مشمول مالیات', 'digikala-importer'); ?></option>
                                <option value="none"><?php _e('بدون مالیات', 'digikala-importer'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="product_status"><?php _e('وضعیت انتشار', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <select id="product_status" name="product_status">
                                <option value="publish"><?php _e('منتشر شده', 'digikala-importer'); ?></option>
                                <option value="draft"><?php _e('پیش‌نویس', 'digikala-importer'); ?></option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('گزینه‌های اضافی', 'digikala-importer'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" id="import_images" name="import_images" checked />
                                    <?php _e('وارد کردن تصاویر', 'digikala-importer'); ?>
                                </label><br />
                                
                                <label>
                                    <input type="checkbox" id="import_attributes" name="import_attributes" checked />
                                    <?php _e('وارد کردن ویژگی‌ها', 'digikala-importer'); ?>
                                </label><br />
                                
                                <label>
                                    <input type="checkbox" id="update_existing" name="update_existing" />
                                    <?php _e('به‌روزرسانی محصول در صورت وجود', 'digikala-importer'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- پیام‌های سیستم -->
        <div id="digikala-messages"></div>
        
        <!-- تاریخچه وارد شده‌ها -->
        <div class="postbox">
            <h2 class="hndle"><?php _e('محصولات وارد شده اخیر', 'digikala-importer'); ?></h2>
            <div class="inside">
                <?php $this->display_recent_imports(); ?>
            </div>
        </div>
    </div>
</div>

<script type="text/template" id="preview-template">
    <div class="product-preview-content">
        <div class="preview-header">
            <div class="preview-image">
                <img src="{{main_image}}" alt="{{title}}" style="max-width: 150px; height: auto;" />
            </div>
            <div class="preview-info">
                <h3>{{title}}</h3>
                <p class="preview-price">
                    <span class="regular-price">قیمت اصلی: {{regular_price}} تومان</span>
                    {{#if sale_price}}
                    <span class="sale-price">قیمت فروش: {{sale_price}} تومان</span>
                    <span class="discount">تخفیف: {{discount}}%</span>
                    {{/if}}
                </p>
                <p class="preview-category">دسته‌بندی: {{category}}</p>
                <p class="preview-brand">برند: {{brand}}</p>
                <p class="preview-availability">
                    وضعیت: 
                    <span class="{{#if available}}available{{else}}unavailable{{/if}}">
                        {{#if available}}موجود{{else}}ناموجود{{/if}}
                    </span>
                </p>
            </div>
        </div>
        
        <div class="preview-description">
            <h4>توضیحات کوتاه:</h4>
            <p>{{short_description}}</p>
        </div>
        
        {{#if specifications}}
        <div class="preview-specifications">
            <h4>ویژگی‌ها:</h4>
            <ul>
                {{#each specifications}}
                <li><strong>{{title}}:</strong> {{value}}</li>
                {{/each}}
            </ul>
        </div>
        {{/if}}
        
        {{#if gallery}}
        <div class="preview-gallery">
            <h4>گالری تصاویر:</h4>
            <div class="gallery-images">
                {{#each gallery}}
                <img src="{{this}}" alt="تصویر محصول" style="width: 80px; height: 80px; margin: 5px;" />
                {{/each}}
            </div>
        </div>
        {{/if}}
    </div>
</script>

<?php
// نمایش محصولات وارد شده اخیر
function display_recent_imports() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'digikala_imports';
    
    // بررسی وجود جدول
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        echo '<p>' . __('هنوز محصولی وارد نشده است.', 'digikala-importer') . '</p>';
        return;
    }
    
    $results = $wpdb->get_results(
        "SELECT di.*, p.post_title 
         FROM $table_name di 
         LEFT JOIN {$wpdb->posts} p ON di.woo_product_id = p.ID 
         ORDER BY di.import_date DESC 
         LIMIT 10"
    );
    
    if (empty($results)) {
        echo '<p>' . __('هنوز محصولی وارد نشده است.', 'digikala-importer') . '</p>';
        return;
    }
    
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . __('نام محصول', 'digikala-importer') . '</th>';
    echo '<th>' . __('شناسه دیجی‌کالا', 'digikala-importer') . '</th>';
    echo '<th>' . __('تاریخ وارد', 'digikala-importer') . '</th>';
    echo '<th>' . __('وضعیت', 'digikala-importer') . '</th>';
    echo '<th>' . __('عملیات', 'digikala-importer') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($results as $row) {
        echo '<tr>';
        echo '<td>' . esc_html($row->post_title ?: 'نامشخص') . '</td>';
        echo '<td>DKP-' . esc_html($row->digikala_id) . '</td>';
        echo '<td>' . date_i18n('Y/m/d H:i', strtotime($row->import_date)) . '</td>';
        echo '<td>';
        
        if ($row->status === 'active') {
            echo '<span class="status-active">فعال</span>';
        } else {
            echo '<span class="status-inactive">غیرفعال</span>';
        }
        
        echo '</td>';
        echo '<td>';
        
        if ($row->woo_product_id && get_post($row->woo_product_id)) {
            $edit_url = admin_url('post.php?post=' . $row->woo_product_id . '&action=edit');
            echo '<a href="' . esc_url($edit_url) . '" class="button button-small">' . __('ویرایش', 'digikala-importer') . '</a> ';
            
            $view_url = get_permalink($row->woo_product_id);
            echo '<a href="' . esc_url($view_url) . '" class="button button-small" target="_blank">' . __('مشاهده', 'digikala-importer') . '</a>';
        } else {
            echo '<span class="error">' . __('محصول حذف شده', 'digikala-importer') . '</span>';
        }
        
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
}

// فراخوانی تابع نمایش
display_recent_imports();
?>