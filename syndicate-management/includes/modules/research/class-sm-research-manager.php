<?php
if (!defined('ABSPATH')) {
    exit;
}

class SM_Research_Manager {

    public static function ajax_submit_research() {
        try {
            check_ajax_referer('sm_research_action', 'nonce');

            $data = $_POST;

            // Validation
            if (mb_strlen($data['title'] ?? '') < 30) {
                wp_send_json_error(['message' => 'عنوان البحث يجب أن لا يقل عن 30 حرفاً']);
            }
            if (mb_strlen($data['abstract'] ?? '') < 500) {
                wp_send_json_error(['message' => 'ملخص البحث يجب أن لا يقل عن 500 حرفاً']);
            }

            // Guest Validation
            if (!is_user_logged_in()) {
                if (empty($data['guest_email']) || !is_email($data['guest_email'])) {
                    wp_send_json_error(['message' => 'يرجى إدخال بريد إلكتروني صحيح لمتابعة الطلب']);
                }
                if (empty($data['guest_phone'])) {
                    wp_send_json_error(['message' => 'يرجى إدخال رقم الهاتف للتواصل']);
                }
            }

            // Multiple Authors Validation
            if (empty($data['author_list']) || !is_array($data['author_list'])) {
                wp_send_json_error(['message' => 'يرجى إضافة مؤلف واحد على الأقل']);
            }
            foreach ($data['author_list'] as $name) {
                $parts = array_filter(explode(' ', trim($name)));
                if (count($parts) < 3) {
                    wp_send_json_error(['message' => 'اسم المؤلف "' . $name . '" يجب أن يكون ثلاثياً على الأقل']);
                }
            }
            $data['authors'] = implode('، ', $data['author_list']);

            if (isset($_FILES['research_file'])) {
                if (!function_exists('wp_handle_upload')) {
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                }
                $uploaded_file = $_FILES['research_file'];
                $upload_overrides = array('test_form' => false);
                $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

                if ($movefile && !isset($movefile['error'])) {
                    $data['file_url'] = $movefile['url'];
                } else {
                    wp_send_json_error(['message' => 'فشل رفع الملف: ' . ($movefile['error'] ?? 'خطأ غير معروف')]);
                }
            } else {
                wp_send_json_error(['message' => 'يرجى إرفاق ملف البحث بصيغة PDF']);
            }

            $res = SM_DB_Research::add_research($data);
            if ($res) {
                // Send notification to admins
                $syndicate = SM_Settings::get_syndicate_info();
                $admin_email = get_option('admin_email');
                $subject = "طلب نشر بحث جديد - " . $syndicate['syndicate_name'];
                $message = "تم استلام طلب نشر بحث جديد في مركز الأبحاث والدراسات.\n\n";
                $message .= "العنوان: " . sanitize_text_field($data['title']) . "\n";
                $message .= "الباحث: " . sanitize_text_field($data['authors']) . "\n";
                $message .= "رابط المراجعة: " . add_query_arg('sm_tab', 'research-studies', home_url('/dashboard')) . "\n";

                wp_mail($admin_email, $subject, $message);

                // Add system alert for admins/officers
                SM_DB::save_alert([
                    'title' => 'طلب بحث جديد: ' . sanitize_text_field($data['title']),
                    'message' => 'قام الباحث ' . sanitize_text_field($data['authors']) . ' بتقديم مادة علمية جديدة للمراجعة والنشر.',
                    'severity' => 'info',
                    'target_roles' => ['administrator', 'sm_general_officer'],
                    'target_url' => add_query_arg('sm_tab', 'research-studies', home_url('/dashboard'))
                ]);

                wp_send_json_success('تم تقديم طلب البحث بنجاح، سيتم مراجعته من قبل الإدارة قبل النشر');
            } else {
                wp_send_json_error(['message' => 'فشل إرسال الطلب، يرجى مراجعة كافة الحقول']);
            }
        } catch (Throwable $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public static function ajax_update_research_status() {
        if (!current_user_can('sm_branch_access')) wp_send_json_error(['message' => 'Unauthorized']);
        check_ajax_referer('sm_admin_action', 'nonce');

        $id = intval($_POST['id']);
        $status = sanitize_text_field($_POST['status']);

        $update_data = ['status' => $status];
        if ($status === 'approved') {
            $update_data['approved_by'] = get_current_user_id();
            $update_data['approved_at'] = current_time('mysql');
        }

        $res = SM_DB_Research::update_research($id, $update_data);
        if ($res !== false) {
            wp_send_json_success('تم تحديث حالة البحث بنجاح');
        } else {
            wp_send_json_error(['message' => 'فشل التحديث']);
        }
    }

    public static function ajax_delete_research() {
        if (!current_user_can('sm_full_access')) wp_send_json_error(['message' => 'Unauthorized']);
        check_ajax_referer('sm_admin_action', 'nonce');

        $id = intval($_POST['id']);
        $res = SM_DB_Research::delete_research($id);
        if ($res) {
            wp_send_json_success('تم حذف البحث نهائياً');
        } else {
            wp_send_json_error(['message' => 'فشل الحذف']);
        }
    }

    public static function ajax_toggle_featured_research() {
        if (!current_user_can('sm_branch_access')) wp_send_json_error(['message' => 'Unauthorized']);
        check_ajax_referer('sm_admin_action', 'nonce');

        $id = intval($_POST['id']);
        $res = SM_DB_Research::toggle_featured($id);
        if ($res) {
            wp_send_json_success('تم تحديث التمييز');
        } else {
            wp_send_json_error(['message' => 'فشل التحديث']);
        }
    }

    public static function ajax_get_researches_html() {
        $args = array(
            'status' => 'approved',
            'featured_first' => true,
            'search' => sanitize_text_field($_GET['search'] ?? ''),
            'university' => sanitize_text_field($_GET['university'] ?? ''),
            'research_type' => sanitize_text_field($_GET['research_type'] ?? ''),
            'specialization' => sanitize_text_field($_GET['specialization'] ?? ''),
            'year' => sanitize_text_field($_GET['year'] ?? ''),
            'author_search' => sanitize_text_field($_GET['author'] ?? '')
        );

        if (!empty($_GET['show_favorites']) && is_user_logged_in()) {
            $results = SM_DB_Research::get_user_favorites(get_current_user_id());
        } else {
            $results = SM_DB_Research::get_researches($args);
        }

        ob_start();
        include SM_PLUGIN_DIR . 'templates/public-research-list-partial.php';
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
    }

    public static function ajax_toggle_favorite() {
        if (!is_user_logged_in()) wp_send_json_error(['message' => 'يجب تسجيل الدخول أولاً']);
        check_ajax_referer('sm_research_action', 'nonce');
        $rid = intval($_POST['id']);
        $res = SM_DB_Research::toggle_favorite($rid, get_current_user_id());
        wp_send_json_success($res);
    }

    public static function ajax_record_interaction() {
        $id = intval($_POST['id']);
        $type = sanitize_text_field($_POST['type']);
        SM_DB_Research::increment_metric($id, $type . '_count');
        wp_send_json_success();
    }

    public static function ajax_print_research() {
        if (!current_user_can('sm_branch_access')) wp_die('Unauthorized');
        check_ajax_referer('sm_print_nonce', 'nonce');

        $id = intval($_GET['id']);
        $r = SM_DB_Research::get_research($id);
        if (!$r) wp_die('البحث غير موجود');

        $type_map = [
            'journal_article' => 'مقال محكم',
            'master_thesis' => 'ماجستير',
            'phd_dissertation' => 'دكتوراه',
            'case_study' => 'دراسة حالة',
            'book_chapter' => 'فصل كتاب'
        ];

        $syndicate = SM_Settings::get_syndicate_info();
        ?>
        <!DOCTYPE html>
        <html dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>طباعة بيانات البحث العلمي</title>
            <style>
                body { font-family: 'Arial', sans-serif; padding: 40px; color: #333; }
                .print-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
                .title { font-size: 24px; font-weight: bold; text-align: center; margin-bottom: 30px; }
                .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px; }
                .info-item { border: 1px solid #eee; padding: 15px; border-radius: 8px; }
                .info-label { font-size: 12px; color: #666; margin-bottom: 5px; font-weight: bold; }
                .info-value { font-size: 16px; font-weight: bold; }
                .abstract-box { border: 1px solid #eee; padding: 20px; border-radius: 8px; line-height: 1.8; }
                @media print { .no-print { display: none; } }
            </style>
        </head>
        <body>
            <div class="no-print" style="margin-bottom: 20px; text-align: left;">
                <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">بدء الطباعة</button>
            </div>

            <div class="print-header">
                <div>
                    <h2 style="margin:0;"><?php echo esc_html($syndicate['syndicate_name']); ?></h2>
                    <p style="margin:5px 0 0 0;">مركز الأبحاث والدراسات</p>
                </div>
                <?php if ($syndicate['syndicate_logo']): ?>
                    <img src="<?php echo esc_url($syndicate['syndicate_logo']); ?>" style="max-height: 80px;">
                <?php endif; ?>
            </div>

            <div class="title">تقرير بيانات المادة العلمية</div>

            <div class="info-grid">
                <div class="info-item" style="grid-column: span 2;">
                    <div class="info-label">عنوان البحث / الدراسة:</div>
                    <div class="info-value" style="font-size: 20px;"><?php echo esc_html($r->title); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">المؤلفون:</div>
                    <div class="info-value"><?php echo esc_html($r->authors); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">نوع البحث:</div>
                    <div class="info-value"><?php echo $type_map[$r->research_type] ?? $r->research_type; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">الجامعة:</div>
                    <div class="info-value"><?php echo SM_Settings::get_universities()[$r->university] ?? $r->university; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">التخصص:</div>
                    <div class="info-value"><?php echo SM_Settings::get_specializations()[$r->specialization] ?? $r->specialization; ?></div>
                </div>
            </div>

            <div class="info-label" style="margin-bottom: 10px;">الملخص (Abstract):</div>
            <div class="abstract-box">
                <?php echo nl2br(esc_html($r->abstract)); ?>
            </div>

            <div style="margin-top: 50px; display: flex; justify-content: space-between; font-size: 14px;">
                <div>تاريخ التقديم: <?php echo date('Y/m/d', strtotime($r->submitted_at)); ?></div>
                <div>توقيع المسؤول: .............................</div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}
