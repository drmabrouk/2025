<?php
if (!defined('ABSPATH')) {
    exit;
}

class SM_Research_Manager {

    public static function ajax_submit_research() {
        try {
            check_ajax_referer('sm_research_action', 'nonce');

            $data = $_POST;

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
                $message = "تم استلام طلب نشر بحث جديد في المنصة العلمية.\n\n";
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
            'specialization' => sanitize_text_field($_GET['specialization'] ?? '')
        );

        $results = SM_DB_Research::get_researches($args);
        ob_start();
        include SM_PLUGIN_DIR . 'templates/public-research-list-partial.php';
        $html = ob_get_clean();
        wp_send_json_success(['html' => $html]);
    }
}
