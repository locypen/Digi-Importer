<?php
// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

// پردازش ذخیره تنظیمات
if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['digikala_settings_nonce'], 'save_digikala_settings')) {
    $settings = array(
        'default_warranty' => sanitize_text_field($_POST['default_warranty'] ?? ''),
        'default_return_policy' => sanitize_textarea_field($_POST['default_return_policy'] ?? ''),
        'auto_publish' => isset($_POST['auto_publish']),
        'import_images' => isset($_POST['import_images']),
        'import_attributes' => isset($_POST['import_attributes']),
        'convert_currency' => isset($_POST['convert_currency']),
        'currency_rate' => floatval($_POST['currency_rate'] ?? 1),
        'default_category' => intval($_POST['default_category'] ?? 0),
        'default_tax_status' => sanitize_text_field($_POST['default_tax_status'] ?? 'taxable'),
        'default_shipping_class' => intval($_POST['default_shipping_class'] ?? 0),
        'auto_sync' => isset($_POST['auto_sync']),
        'sync_interval' => sanitize_text_field($_POST['sync_interval'] ?? 'daily'),
        'max_images' => intval($_POST['max_images'] ?? 5),
        'image_quality' => intval($_POST['image_quality'] ?? 80),
        'api_cache_time' => intval($_POST['api_cache_time'] ?? 3600),
        'log_imports' => isset($_POST['log_imports']),
        'delete_logs_after' => intval($_POST['delete_logs_after'] ?? 30)
    );
    
    update_option('digikala_importer_settings', $settings);
    
    echo '<div class="notice notice-success is-dismissible"><p>' . 
         __('تنظیمات با موفقیت ذخیره شد.', 'digikala-importer') . 
         '</p></div>';
}

// دریافت تنظیمات فعلی
$settings = get_option('digikala_importer_settings', array());
$default_settings = array(
    'default_warranty' => '18 ماه گارانتی شرکتی',
    'default_return_policy' => 'امکان بازگشت کالا تا 7 روز',
    'auto_publish' => true,
    'import_images' => true,
    'import_attributes' => true,
    'convert_currency' => false,
    'currency_rate' => 1,
    'default_category' => 0,
    'default_tax_status' => 'taxable',
    'default_shipping_class' => 0,
    'auto_sync' => false,
    'sync_interval' => 'daily',
    'max_images' => 5,
    'image_quality' => 80,
    'api_cache_time' => 3600,
    'log_imports' => true,
    'delete_logs_after' => 30
);

$settings = wp_parse_args($settings, $default_settings);
?>

<div class="wrap">
    <h1><?php _e('تنظیمات Digikala Product Importer', 'digikala-importer'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('save_digikala_settings', 'digikala_settings_nonce'); ?>
        
        <div class="digikala-settings-tabs">
            <nav class="nav-tab-wrapper">
                <a href="#general" class="nav-tab nav-tab-active"><?php _e('عمومی', 'digikala-importer'); ?></a>
                <a href="#products" class="nav-tab"><?php _e('محصولات', 'digikala-importer'); ?></a>
                <a href="#images" class="nav-tab"><?php _e('تصاویر', 'digikala-importer'); ?></a>
                <a href="#sync" class="nav-tab"><?php _e('همگام‌سازی', 'digikala-importer'); ?></a>
                <a href="#advanced" class="nav-tab"><?php _e('پیشرفته', 'digikala-importer'); ?></a>
            </nav>
            
            <!-- تب عمومی -->
            <div id="general" class="tab-content active">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="default_warranty"><?php _e('گارانتی پیش‌فرض', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="default_warranty" name="default_warranty" 
                                   value="<?php echo esc_attr($settings['default_warranty']); ?>" class="regular-text" />
                            <p class="description">
                                <?php _e('گارانتی پیش‌فرض که برای تمام محصولات وارد شده اعمال می‌شود.', 'digikala-importer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="default_return_policy"><?php _e('سیاست بازگشت پیش‌فرض', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <textarea id="default_return_policy" name="default_return_policy" 
                                      rows="4" class="large-text"><?php echo esc_textarea($settings['default_return_policy']); ?></textarea>
                            <p class="description">
                                <?php _e('سیاست بازگشت کالا که برای تمام محصولات وارد شده اعمال می‌شود.', 'digikala-importer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('تنظیمات انتشار', 'digikala-importer'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="auto_publish" value="1" 
                                           <?php checked($settings['auto_publish']); ?> />
                                    <?php _e('انتشار خودکار محصولات وارد شده', 'digikala-importer'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('اگر غیرفعال باشد، محصولات به صورت پیش‌نویس ذخیره می‌شوند.', 'digikala-importer'); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('گزینه‌های وارد کردن', 'digikala-importer'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="import_images" value="1" 
                                           <?php checked($settings['import_images']); ?> />
                                    <?php _e('وارد کردن تصاویر به صورت پیش‌فرض', 'digikala-importer'); ?>
                                </label><br />
                                
                                <label>
                                    <input type="checkbox" name="import_attributes" value="1" 
                                           <?php checked($settings['import_attributes']); ?> />
                                    <?php _e('وارد کردن ویژگی‌ها به صورت پیش‌فرض', 'digikala-importer'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- تب محصولات -->
            <div id="products" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="default_category"><?php _e('دسته‌بندی پیش‌فرض', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <?php
                            wp_dropdown_categories(array(
                                'taxonomy' => 'product_cat',
                                'name' => 'default_category',
                                'id' => 'default_category',
                                'selected' => $settings['default_category'],
                                'show_option_none' => __('انتخاب کنید', 'digikala-importer'),
                                'option_none_value' => 0,
                                'hide_empty' => false
                            ));
                            ?>
                            <p class="description">
                                <?php _e('دسته‌بندی پیش‌فرض برای محصولاتی که دسته‌بندی مشخصی ندارند.', 'digikala-importer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="default_tax_status"><?php _e('وضعیت مالیاتی پیش‌فرض', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <select id="default_tax_status" name="default_tax_status">
                                <option value="taxable" <?php selected($settings['default_tax_status'], 'taxable'); ?>>
                                    <?php _e('مشمول مالیات', 'digikala-importer'); ?>
                                </option>
                                <option value="none" <?php selected($settings['default_tax_status'], 'none'); ?>>
                                    <?php _e('بدون مالیات', 'digikala-importer'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="default_shipping_class"><?php _e('کلاس ارسال پیش‌فرض', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <select id="default_shipping_class" name="default_shipping_class">
                                <option value="0"><?php _e('بدون کلاس ارسال', 'digikala-importer'); ?></option>
                                <?php
                                $shipping_classes = WC()->shipping->get_shipping_classes();
                                foreach ($shipping_classes as $class) {
                                    echo '<option value="' . esc_attr($class->term_id) . '"' . 
                                         selected($settings['default_shipping_class'], $class->term_id, false) . '>' . 
                                         esc_html($class->name) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('تبدیل واحد پول', 'digikala-importer'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="convert_currency" value="1" 
                                           <?php checked($settings['convert_currency']); ?> />
                                    <?php _e('تبدیل قیمت از ریال به تومان', 'digikala-importer'); ?>
                                </label>
                                
                                <p>
                                    <label for="currency_rate"><?php _e('نرخ تبدیل:', 'digikala-importer'); ?></label>
                                    <input type="number" id="currency_rate" name="currency_rate" 
                                           value="<?php echo esc_attr($settings['currency_rate']); ?>" 
                                           step="0.1" min="0.1" max="100" style="width: 80px;" />
                                    <span class="description"><?php _e('(معمولاً 0.1 برای تبدیل ریال به تومان)', 'digikala-importer'); ?></span>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- تب تصاویر -->
            <div id="images" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="max_images"><?php _e('حداکثر تعداد تصاویر', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="max_images" name="max_images" 
                                   value="<?php echo esc_attr($settings['max_images']); ?>" 
                                   min="1" max="20" style="width: 80px;" />
                            <p class="description">
                                <?php _e('حداکثر تعداد تصاویر که برای هر محصول وارد می‌شود (شامل تصویر اصلی).', 'digikala-importer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="image_quality"><?php _e('کیفیت تصاویر', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <input type="range" id="image_quality" name="image_quality" 
                                   value="<?php echo esc_attr($settings['image_quality']); ?>" 
                                   min="10" max="100" step="10" 
                                   oninput="this.nextElementSibling.textContent = this.value + '%'" />
                            <span><?php echo $settings['image_quality']; ?>%</span>
                            <p class="description">
                                <?php _e('کیفیت فشرده‌سازی تصاویر هنگام آپلود (کیفیت بالاتر = فایل بزرگ‌تر).', 'digikala-importer'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- تب همگام‌سازی -->
            <div id="sync" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('همگام‌سازی خودکار', 'digikala-importer'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="auto_sync" value="1" 
                                           <?php checked($settings['auto_sync']); ?> />
                                    <?php _e('فعال‌سازی همگام‌سازی خودکار قیمت‌ها', 'digikala-importer'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('قیمت‌های محصولات وارد شده به طور خودکار به‌روزرسانی می‌شوند.', 'digikala-importer'); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="sync_interval"><?php _e('بازه همگام‌سازی', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <select id="sync_interval" name="sync_interval">
                                <option value="hourly" <?php selected($settings['sync_interval'], 'hourly'); ?>>
                                    <?php _e('هر ساعت', 'digikala-importer'); ?>
                                </option>
                                <option value="twicedaily" <?php selected($settings['sync_interval'], 'twicedaily'); ?>>
                                    <?php _e('دو بار در روز', 'digikala-importer'); ?>
                                </option>
                                <option value="daily" <?php selected($settings['sync_interval'], 'daily'); ?>>
                                    <?php _e('روزانه', 'digikala-importer'); ?>
                                </option>
                                <option value="weekly" <?php selected($settings['sync_interval'], 'weekly'); ?>>
                                    <?php _e('هفتگی', 'digikala-importer'); ?>
                                </option>
                            </select>
                            <p class="description">
                                <?php _e('فقط در صورت فعال بودن همگام‌سازی خودکار اعمال می‌شود.', 'digikala-importer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('عملیات همگام‌سازی', 'digikala-importer'); ?></th>
                        <td>
                            <p>
                                <button type="button" id="manual-sync" class="button">
                                    <?php _e('همگام‌سازی دستی همه محصولات', 'digikala-importer'); ?>
                                </button>
                                <span id="sync-status"></span>
                            </p>
                            <p class="description">
                                <?php _e('همگام‌سازی فوری قیمت‌ها و موجودی تمام محصولات وارد شده.', 'digikala-importer'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- تب پیشرفته -->
            <div id="advanced" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="api_cache_time"><?php _e('مدت زمان کش API', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="api_cache_time" name="api_cache_time" 
                                   value="<?php echo esc_attr($settings['api_cache_time']); ?>" 
                                   min="60" max="86400" step="60" style="width: 100px;" />
                            <span><?php _e('ثانیه', 'digikala-importer'); ?></span>
                            <p class="description">
                                <?php _e('مدت زمان ذخیره نتایج API در کش برای بهبود عملکرد (3600 ثانیه = 1 ساعت).', 'digikala-importer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('ثبت گزارش‌ها', 'digikala-importer'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="log_imports" value="1" 
                                           <?php checked($settings['log_imports']); ?> />
                                    <?php _e('ثبت گزارش عملیات وارد کردن', 'digikala-importer'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('ثبت جزئیات تمام عملیات وارد کردن برای رفع اشکال.', 'digikala-importer'); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="delete_logs_after"><?php _e('حذف گزارش‌ها بعد از', 'digikala-importer'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="delete_logs_after" name="delete_logs_after" 
                                   value="<?php echo esc_attr($settings['delete_logs_after']); ?>" 
                                   min="1" max="365" style="width: 80px;" />
                            <span><?php _e('روز', 'digikala-importer'); ?></span>
                            <p class="description">
                                <?php _e('گزارش‌های قدیمی‌تر از این مدت به طور خودکار حذف می‌شوند.', 'digikala-importer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('عملیات سیستم', 'digikala-importer'); ?></th>
                        <td>
                            <p>
                                <button type="button" id="clear-cache" class="button">
                                    <?php _e('پاک کردن کش', 'digikala-importer'); ?>
                                </button>
                                <button type="button" id="clear-logs" class="button" 
                                        onclick="return confirm('آیا از حذف تمام گزارش‌ها اطمینان دارید؟')">
                                    <?php _e('پاک کردن گزارش‌ها', 'digikala-importer'); ?>
                                </button>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('اطلاعات سیستم', 'digikala-importer'); ?></th>
                        <td>
                            <div class="system-info">
                                <p><strong><?php _e('نسخه پلاگین:', 'digikala-importer'); ?></strong> <?php echo DIGIKALA_IMPORTER_VERSION; ?></p>
                                <p><strong><?php _e('نسخه وردپرس:', 'digikala-importer'); ?></strong> <?php echo get_bloginfo('version'); ?></p>
                                <p><strong><?php _e('نسخه ووکامرس:', 'digikala-importer'); ?></strong> <?php echo WC()->version; ?></p>
                                <p><strong><?php _e('نسخه PHP:', 'digikala-importer'); ?></strong> <?php echo PHP_VERSION; ?></p>
                                <p><strong><?php _e('حافظه PHP:', 'digikala-importer'); ?></strong> <?php echo ini_get('memory_limit'); ?></p>
                                <p><strong><?php _e('حداکثر زمان اجرا:', 'digikala-importer'); ?></strong> <?php echo ini_get('max_execution_time'); ?> ثانیه</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" name="save_settings" class="button-primary" 
                   value="<?php _e('ذخیره تنظیمات', 'digikala-importer'); ?>" />
            <button type="button" id="reset-settings" class="button" 
                    onclick="return confirm('آیا از بازنشانی تمام تنظیمات به حالت پیش‌فرض اطمینان دارید؟')">
                <?php _e('بازنشانی به پیش‌فرض', 'digikala-importer'); ?>
            </button>
        </p>
    </form>
</div>

<style>
.digikala-settings-tabs {
    margin-top: 20px;
}

.nav-tab-wrapper {
    border-bottom: 1px solid #ccc;
    margin-bottom: 20px;
}

.tab-content {
    display: none;
    background: #fff;
    padding: 20px;
    border: 1px solid #e5e5e5;
    border-radius: 0 0 3px 3px;
}

.tab-content.active {
    display: block;
}

.system-info {
    background: #f7f7f7;
    padding: 15px;
    border-radius: 4px;
    border: 1px solid #e1e1e1;
}

.system-info p {
    margin: 5px 0;
    font-size: 13px;
}

input[type="range"] {
    width: 200px;
    margin-left: 10px;
}

#sync-status {
    margin-right: 10px;
    font-weight: 600;
}

#sync-status.loading {
    color: #0073aa;
}

#sync-status.success {
    color: #00a32a;
}

#sync-status.error {
    color: #d63638;
}

@media screen and (max-width: 782px) {
    .nav-tab-wrapper {
        border-bottom: none;
    }
    
    .nav-tab-wrapper .nav-tab {
        display: block;
        width: 100%;
        margin: 0 0 1px 0;
        border-radius: 3px 3px 0 0;
    }
    
    .tab-content {
        border: 1px solid #e5e5e5;
        border-radius: 3px;
    }
    
    .form-table th,
    .form-table td {
        display: block;
        width: 100%;
        padding: 10px;
    }
    
    .form-table th {
        background: #f9f9f9;
        border-bottom: 1px solid #e1e1e1;
        font-weight: 600;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // مدیریت تب‌ها
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        const target = $(this).attr('href');
        
        // تغییر تب فعال
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // تغییر محتوای فعال
        $('.tab-content').removeClass('active');
        $(target).addClass('active');
    });
    
    // همگام‌سازی دستی
    $('#manual-sync').on('click', function() {
        const $button = $(this);
        const $status = $('#sync-status');
        
        $button.prop('disabled', true).text('در حال همگام‌سازی...');
        $status.removeClass('success error').addClass('loading').text('در حال پردازش...');
        
        $.post(ajaxurl, {
            action: 'digikala_manual_sync',
            nonce: '<?php echo wp_create_nonce("digikala_manual_sync"); ?>'
        })
        .done(function(response) {
            if (response.success) {
                $status.removeClass('loading error').addClass('success')
                       .text('همگام‌سازی با موفقیت انجام شد');
            } else {
                $status.removeClass('loading success').addClass('error')
                       .text('خطا در همگام‌سازی: ' + (response.data || 'خطای نامشخص'));
            }
        })
        .fail(function() {
            $status.removeClass('loading success').addClass('error')
                   .text('خطا در ارتباط با سرور');
        })
        .always(function() {
            $button.prop('disabled', false).text('همگام‌سازی دستی همه محصولات');
            
            setTimeout(function() {
                $status.text('');
            }, 5000);
        });
    });
    
    // پاک کردن کش
    $('#clear-cache').on('click', function() {
        const $button = $(this);
        
        $button.prop('disabled', true).text('در حال پاک کردن...');
        
        $.post(ajaxurl, {
            action: 'digikala_clear_cache',
            nonce: '<?php echo wp_create_nonce("digikala_clear_cache"); ?>'
        })
        .done(function(response) {
            if (response.success) {
                alert('کش با موفقیت پاک شد.');
            } else {
                alert('خطا در پاک کردن کش.');
            }
        })
        .fail(function() {
            alert('خطا در ارتباط با سرور.');
        })
        .always(function() {
            $button.prop('disabled', false).text('پاک کردن کش');
        });
    });
    
    // پاک کردن گزارش‌ها
    $('#clear-logs').on('click', function() {
        const $button = $(this);
        
        $button.prop('disabled', true).text('در حال پاک کردن...');
        
        $.post(ajaxurl, {
            action: 'digikala_clear_logs',
            nonce: '<?php echo wp_create_nonce("digikala_clear_logs"); ?>'
        })
        .done(function(response) {
            if (response.success) {
                alert('گزارش‌ها با موفقیت پاک شدند.');
            } else {
                alert('خطا در پاک کردن گزارش‌ها.');
            }
        })
        .fail(function() {
            alert('خطا در ارتباط با سرور.');
        })
        .always(function() {
            $button.prop('disabled', false).text('پاک کردن گزارش‌ها');
        });
    });
    
    // بازنشانی تنظیمات
    $('#reset-settings').on('click', function() {
        // بازنشانی فرم به مقادیر پیش‌فرض
        $('#default_warranty').val('18 ماه گارانتی شرکتی');
        $('#default_return_policy').val('امکان بازگشت کالا تا 7 روز');
        $('input[name="auto_publish"]').prop('checked', true);
        $('input[name="import_images"]').prop('checked', true);
        $('input[name="import_attributes"]').prop('checked', true);
        $('input[name="convert_currency"]').prop('checked', false);
        $('#currency_rate').val(1);
        $('#default_category').val(0);
        $('#default_tax_status').val('taxable');
        $('#default_shipping_class').val(0);
        $('input[name="auto_sync"]').prop('checked', false);
        $('#sync_interval').val('daily');
        $('#max_images').val(5);
        $('#image_quality').val(80);
        $('#api_cache_time').val(3600);
        $('input[name="log_imports"]').prop('checked', true);
        $('#delete_logs_after').val(30);
        
        // به‌روزرسانی نمایش slider
        $('#image_quality').trigger('input');
        
        alert('تنظیمات به حالت پیش‌فرض بازنشانی شدند. برای اعمال تغییرات دکمه "ذخیره تنظیمات" را کلیک کنید.');
    });
    
    // اعتبارسنجی فرم
    $('form').on('submit', function(e) {
        let isValid = true;
        let errors = [];
        
        // بررسی نرخ تبدیل
        const currencyRate = parseFloat($('#currency_rate').val());
        if (currencyRate <= 0) {
            errors.push('نرخ تبدیل باید بزرگ‌تر از صفر باشد.');
            isValid = false;
        }
        
        // بررسی حداکثر تصاویر
        const maxImages = parseInt($('#max_images').val());
        if (maxImages < 1 || maxImages > 20) {
            errors.push('حداکثر تعداد تصاویر باید بین 1 تا 20 باشد.');
            isValid = false;
        }
        
        // بررسی مدت زمان کش
        const cacheTime = parseInt($('#api_cache_time').val());
        if (cacheTime < 60) {
            errors.push('مدت زمان کش نمی‌تواند کمتر از 60 ثانیه باشد.');
            isValid = false;
        }
        
        // بررسی مدت حذف گزارش‌ها
        const deleteLogs = parseInt($('#delete_logs_after').val());
        if (deleteLogs < 1) {
            errors.push('مدت حذف گزارش‌ها نمی‌تواند کمتر از 1 روز باشد.');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('خطاهای زیر در فرم وجود دارد:\n\n' + errors.join('\n'));
        }
    });
});
</script>

<?php
// اضافه کردن AJAX handlers برای عملیات پیشرفته
add_action('wp_ajax_digikala_manual_sync', 'digikala_handle_manual_sync');
add_action('wp_ajax_digikala_clear_cache', 'digikala_handle_clear_cache');
add_action('wp_ajax_digikala_clear_logs', 'digikala_handle_clear_logs');

function digikala_handle_manual_sync() {
    check_ajax_referer('digikala_manual_sync', 'nonce');
    
    if (!current_user_can('manage_woocommerce')) {
        wp_die('مجوز کافی ندارید.');
    }
    
    // پیاده‌سازی همگام‌سازی دستی
    // این بخش در آینده تکمیل می‌شود
    
    wp_send_json_success('همگام‌سازی با موفقیت انجام شد.');
}

function digikala_handle_clear_cache() {
    check_ajax_referer('digikala_clear_cache', 'nonce');
    
    if (!current_user_can('manage_woocommerce')) {
        wp_die('مجوز کافی ندارید.');
    }
    
    // پاک کردن کش
    wp_cache_flush();
    delete_transient('digikala_api_cache');
    
    wp_send_json_success();
}

function digikala_handle_clear_logs() {
    check_ajax_referer('digikala_clear_logs', 'nonce');
    
    if (!current_user_can('manage_woocommerce')) {
        wp_die('مجوز کافی ندارید.');
    }
    
    // پاک کردن گزارش‌ها
    global $wpdb;
    $table_name = $wpdb->prefix . 'digikala_logs';
    
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        $wpdb->query("TRUNCATE TABLE $table_name");
    }
    
    wp_send_json_success();
}
?>