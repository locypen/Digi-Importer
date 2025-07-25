jQuery(document).ready(function($) {
    'use strict';
    
    // المان‌های اصلی
    const $productUrl = $('#product_url');
    const $previewBtn = $('#preview-product');
    const $importBtn = $('#import-product');
    const $previewContainer = $('#product-preview');
    const $customSettings = $('#custom-settings');
    const $messagesContainer = $('#digikala-messages');
    const $warrantySelect = $('#warranty');
    const $warrantyCustom = $('#warranty-custom');
    
    // متغیرهای سراسری
    let currentProductData = null;
    let isProcessing = false;
    
    // رویدادهای اصلی
    $previewBtn.on('click', handlePreviewClick);
    $importBtn.on('click', handleImportClick);
    $warrantySelect.on('change', handleWarrantyChange);
    $productUrl.on('input', handleUrlInput);
    
    /**
     * مدیریت تغییر URL محصول
     */
    function handleUrlInput() {
        const url = $productUrl.val().trim();
        
        if (url && isValidDigikalaUrl(url)) {
            $previewBtn.prop('disabled', false);
        } else {
            $previewBtn.prop('disabled', true);
            $importBtn.prop('disabled', true);
            $previewContainer.hide();
            $customSettings.hide();
        }
    }
    
    /**
     * بررسی معتبر بودن URL دیجی‌کالا
     */
    function isValidDigikalaUrl(url) {
        const patterns = [
            /digikala\.com\/product\/dkp-\d+/,
            /digikala\.com\/product\/.*-dkp-\d+/,
            /dkp-\d+/
        ];
        
        return patterns.some(pattern => pattern.test(url));
    }
    
    /**
     * مدیریت کلیک دکمه پیش‌نمایش
     */
    function handlePreviewClick(e) {
        e.preventDefault();
        
        if (isProcessing) return;
        
        const url = $productUrl.val().trim();
        
        if (!url || !isValidDigikalaUrl(url)) {
            showMessage('لینک محصول معتبر نیست.', 'error');
            return;
        }
        
        previewProduct(url);
    }
    
    /**
     * مدیریت کلیک دکمه وارد کردن
     */
    function handleImportClick(e) {
        e.preventDefault();
        
        if (isProcessing || !currentProductData) return;
        
        const url = $productUrl.val().trim();
        const customSettings = getCustomSettings();
        
        importProduct(url, customSettings);
    }
    
    /**
     * مدیریت تغییر انتخاب گارانتی
     */
    function handleWarrantyChange() {
        const selectedValue = $warrantySelect.val();
        
        if (selectedValue === 'custom') {
            $warrantyCustom.show().focus();
        } else {
            $warrantyCustom.hide();
        }
    }
    
    /**
     * پیش‌نمایش محصول
     */
    function previewProduct(url) {
        setProcessing(true);
        showMessage('در حال دریافت اطلاعات محصول...', 'info');
        
        const data = {
            action: 'digikala_preview_product',
            nonce: digikala_ajax.nonce,
            product_url: url
        };
        
        $.post(digikala_ajax.ajax_url, data)
            .done(function(response) {
                if (response.success) {
                    currentProductData = response.data;
                    displayPreview(response.data);
                    $importBtn.prop('disabled', false);
                    $customSettings.show();
                    showMessage('اطلاعات محصول با موفقیت دریافت شد.', 'success');
                } else {
                    showMessage(response.data || 'خطا در دریافت اطلاعات محصول.', 'error');
                    resetForm();
                }
            })
            .fail(function() {
                showMessage('خطا در ارتباط با سرور.', 'error');
                resetForm();
            })
            .always(function() {
                setProcessing(false);
            });
    }
    
    /**
     * وارد کردن محصول
     */
    function importProduct(url, customSettings) {
        setProcessing(true);
        showMessage('در حال وارد کردن محصول...', 'info');
        
        const data = {
            action: 'digikala_import_product',
            nonce: digikala_ajax.nonce,
            product_url: url,
            custom_settings: customSettings
        };
        
        $.post(digikala_ajax.ajax_url, data)
            .done(function(response) {
                if (response.success) {
                    showMessage('محصول با موفقیت وارد شد!', 'success');
                    
                    // نمایش لینک ویرایش محصول
                    if (response.data.edit_url) {
                        const editLink = `<a href="${response.data.edit_url}" target="_blank" class="button button-primary">ویرایش محصول</a>`;
                        showMessage(`محصول با موفقیت ایجاد شد. ${editLink}`, 'success', false);
                    }
                    
                    // ریست فرم
                    setTimeout(resetForm, 2000);
                    
                    // رفرش لیست محصولات (در صورت نیاز)
                    location.reload();
                } else {
                    showMessage(response.data || 'خطا در وارد کردن محصول.', 'error');
                }
            })
            .fail(function() {
                showMessage('خطا در ارتباط با سرور.', 'error');
            })
            .always(function() {
                setProcessing(false);
            });
    }
    
    /**
     * نمایش پیش‌نمایش محصول
     */
    function displayPreview(data) {
        const template = $('#preview-template').html();
        
        // آماده‌سازی داده‌ها برای نمایش
        const templateData = {
            title: data.title || 'بدون عنوان',
            main_image: data.images.main || 'https://via.placeholder.com/150',
            regular_price: formatPrice(data.price.regular),
            sale_price: data.price.sale ? formatPrice(data.price.sale) : null,
            discount: data.price.discount || 0,
            category: data.category || 'نامشخص',
            brand: data.brand || 'نامشخص',
            available: data.availability,
            short_description: truncateText(data.description.short, 200),
            specifications: formatSpecifications(data.specifications),
            gallery: data.images.gallery || []
        };
        
        // رندر کردن template (استفاده از Handlebars ساده)
        let html = template;
        Object.keys(templateData).forEach(key => {
            const value = templateData[key];
            const regex = new RegExp(`{{${key}}}`, 'g');
            html = html.replace(regex, value || '');
        });
        
        // مدیریت بلوک‌های شرطی
        html = handleConditionalBlocks(html, templateData);
        
        $('#preview-content').html(html);
        $previewContainer.show();
        
        // اسکرول به پیش‌نمایش
        $('html, body').animate({
            scrollTop: $previewContainer.offset().top - 50
        }, 500);
    }
    
    /**
     * مدیریت بلوک‌های شرطی در template
     */
    function handleConditionalBlocks(html, data) {
        // {{#if condition}} ... {{/if}}
        html = html.replace(/\{\{#if\s+(\w+)\}\}(.*?)\{\{\/if\}\}/gs, function(match, condition, content) {
            return data[condition] ? content : '';
        });
        
        // {{#each array}} ... {{/each}}
        html = html.replace(/\{\{#each\s+(\w+)\}\}(.*?)\{\{\/each\}\}/gs, function(match, arrayName, itemTemplate) {
            const array = data[arrayName];
            if (!Array.isArray(array) || array.length === 0) return '';
            
            return array.map(item => {
                let itemHtml = itemTemplate;
                if (typeof item === 'object') {
                    Object.keys(item).forEach(key => {
                        itemHtml = itemHtml.replace(new RegExp(`\\{\\{${key}\\}\\}`, 'g'), item[key] || '');
                    });
                } else {
                    itemHtml = itemHtml.replace(/\{\{this\}\}/g, item);
                }
                return itemHtml;
            }).join('');
        });
        
        return html;
    }
    
    /**
     * فرمت کردن قیمت
     */
    function formatPrice(price) {
        if (!price) return '0';
        return parseInt(price).toLocaleString('fa-IR');
    }
    
    /**
     * کوتاه کردن متن
     */
    function truncateText(text, maxLength) {
        if (!text) return '';
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }
    
    /**
     * فرمت کردن ویژگی‌ها
     */
    function formatSpecifications(specs) {
        if (!Array.isArray(specs)) return [];
        
        return specs.slice(0, 10).map(spec => ({
            title: spec.title || 'نامشخص',
            value: spec.values && spec.values[0] ? spec.values[0].title : 'نامشخص'
        }));
    }
    
    /**
     * دریافت تنظیمات سفارشی
     */
    function getCustomSettings() {
        const warranty = $warrantySelect.val() === 'custom' 
            ? $warrantyCustom.val() 
            : $warrantySelect.val();
        
        return {
            warranty: warranty,
            return_policy: $('#return_policy').val(),
            shipping_class: $('#shipping_class').val(),
            tax_status: $('#tax_status').val(),
            product_status: $('#product_status').val(),
            import_images: $('#import_images').is(':checked'),
            import_attributes: $('#import_attributes').is(':checked'),
            update_existing: $('#update_existing').is(':checked')
        };
    }
    
    /**
     * تنظیم وضعیت پردازش
     */
    function setProcessing(processing) {
        isProcessing = processing;
        
        if (processing) {
            $previewBtn.prop('disabled', true).text('در حال پردازش...');
            $importBtn.prop('disabled', true).text('در حال وارد کردن...');
        } else {
            $previewBtn.prop('disabled', false).text('پیش‌نمایش محصول');
            $importBtn.prop('disabled', !currentProductData).text('وارد کردن محصول');
        }
    }
    
    /**
     * نمایش پیام
     */
    function showMessage(message, type = 'info', autoHide = true) {
        const alertClass = {
            'success': 'notice-success',
            'error': 'notice-error',
            'warning': 'notice-warning',
            'info': 'notice-info'
        }[type] || 'notice-info';
        
        const messageHtml = `
            <div class="notice ${alertClass} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">بستن این پیام</span>
                </button>
            </div>
        `;
        
        $messagesContainer.html(messageHtml);
        
        // مدیریت دکمه بستن
        $messagesContainer.find('.notice-dismiss').on('click', function() {
            $(this).parent().fadeOut();
        });
        
        // حذف خودکار پیام (به جز پیام‌های خطا)
        if (autoHide && type !== 'error') {
            setTimeout(() => {
                $messagesContainer.find('.notice').fadeOut();
            }, 5000);
        }
        
        // اسکرول به پیام
        $('html, body').animate({
            scrollTop: $messagesContainer.offset().top - 100
        }, 300);
    }
    
    /**
     * ریست کردن فرم
     */
    function resetForm() {
        $productUrl.val('');
        $previewContainer.hide();
        $customSettings.hide();
        $importBtn.prop('disabled', true);
        $previewBtn.prop('disabled', true);
        currentProductData = null;
        
        // ریست تنظیمات سفارشی
        $('#warranty').val('');
        $('#warranty-custom').hide().val('');
        $('#return_policy').val('');
        $('#shipping_class').val('');
        $('#tax_status').val('taxable');
        $('#product_status').val('publish');
        $('#import_images, #import_attributes').prop('checked', true);
        $('#update_existing').prop('checked', false);
    }
    
    /**
     * اعتبارسنجی فرم
     */
    function validateForm() {
        const url = $productUrl.val().trim();
        
        if (!url) {
            showMessage('لطفاً لینک محصول را وارد کنید.', 'error');
            return false;
        }
        
        if (!isValidDigikalaUrl(url)) {
            showMessage('لینک وارد شده معتبر نیست.', 'error');
            return false;
        }
        
        return true;
    }
    
    /**
     * کپی کردن لینک محصول
     */
    function copyProductLink(productId) {
        const url = window.location.origin + '/?p=' + productId;
        
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(() => {
                showMessage('لینک محصول کپی شد.', 'success');
            });
        } else {
            // fallback برای مرورگرهای قدیمی
            const textArea = document.createElement('textarea');
            textArea.value = url;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showMessage('لینک محصول کپی شد.', 'success');
        }
    }
    
    /**
     * فیلترینگ محصولات در جدول
     */
    function initTableFilter() {
        const $searchInput = $('<input type="text" placeholder="جستجو در محصولات..." style="margin-bottom: 10px; width: 200px;">');
        $('.wp-list-table').before($searchInput);
        
        $searchInput.on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('.wp-list-table tbody tr').each(function() {
                const rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.includes(searchTerm));
            });
        });
    }
    
    // راه‌اندازی فیلتر جدول
    if ($('.wp-list-table').length) {
        initTableFilter();
    }
    
    // مدیریت تغییر اندازه صفحه
    $(window).on('resize', function() {
        // تنظیم responsive برای موبایل
        if ($(window).width() < 768) {
            $('.form-table th').css('display', 'block');
            $('.form-table td').css('display', 'block');
        } else {
            $('.form-table th, .form-table td').css('display', '');
        }
    }).trigger('resize');
    
    // ذخیره پیش‌فرض تنظیمات
    const defaultSettings = getCustomSettings();
    
    // بارگذاری تنظیمات ذخیره شده
    function loadSavedSettings() {
        const saved = localStorage.getItem('digikala_importer_settings');
        if (saved) {
            try {
                const settings = JSON.parse(saved);
                Object.keys(settings).forEach(key => {
                    const $element = $('#' + key);
                    if ($element.length) {
                        if ($element.is(':checkbox')) {
                            $element.prop('checked', settings[key]);
                        } else {
                            $element.val(settings[key]);
                        }
                    }
                });
            } catch (e) {
                console.log('خطا در بارگذاری تنظیمات:', e);
            }
        }
    }
    
    // ذخیره تنظیمات
    function saveSettings() {
        const settings = getCustomSettings();
        localStorage.setItem('digikala_importer_settings', JSON.stringify(settings));
    }
    
    // رویداد تغییر تنظیمات
    $('#custom-settings input, #custom-settings select, #custom-settings textarea').on('change', saveSettings);
    
    // بارگذاری تنظیمات در شروع
    loadSavedSettings();
});