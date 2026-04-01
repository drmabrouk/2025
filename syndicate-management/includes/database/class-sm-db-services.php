<?php
if (!defined('ABSPATH')) {
    exit;
}

class SM_DB_Services {

    public static function get_service_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sm_services WHERE id = %d", intval($id)));
    }

    public static function get_service_request_by_id($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sm_service_requests WHERE id = %d", intval($id)));
    }

    public static function get_services($args = array()) {
        global $wpdb;
        $where = "1=1";
        $params = [];

        if (!empty($args['id'])) {
            $where .= " AND id = %d";
            $params[] = intval($args['id']);
        }

        if (isset($args['is_deleted'])) {
            $where .= " AND is_deleted = %d";
            $params[] = (int)$args['is_deleted'];
        } else {
            $where .= " AND is_deleted = 0";
        }

        if (!empty($args['status']) && $args['status'] !== 'any' && $args['status'] !== 'all') {
            $where .= " AND status = %s";
            $params[] = sanitize_text_field($args['status']);
        }

        if (!empty($args['category'])) {
            $where .= " AND category = %s";
            $params[] = sanitize_text_field($args['category']);
        }

        $user = wp_get_current_user();
        if ($user->ID > 0 && !current_user_can('manage_options') && !current_user_can('sm_full_access')) {
            $my_gov = get_user_meta($user->ID, 'sm_governorate', true);
            if ($my_gov) {
                $where .= " AND (branch = %s OR branch = 'all')";
                $params[] = $my_gov;
            } else {
                $where .= " AND branch = 'all'";
            }
        }

        $query = "SELECT * FROM {$wpdb->prefix}sm_services WHERE $where ORDER BY created_at DESC";
        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($query, $params));
        }
        return $wpdb->get_results($query);
    }

    public static function add_service($data) {
        global $wpdb;
        return $wpdb->insert("{$wpdb->prefix}sm_services", array(
            'name' => sanitize_text_field($data['name']),
            'category' => sanitize_text_field($data['category'] ?? 'عام'),
            'branch' => sanitize_text_field($data['branch'] ?? 'all'),
            'icon' => sanitize_text_field($data['icon'] ?? 'dashicons-cloud'),
            'requires_login' => isset($data['requires_login']) ? (int)$data['requires_login'] : 1,
            'description' => sanitize_textarea_field($data['description']),
            'fees' => floatval($data['fees']),
            'required_fields' => $data['required_fields'] ?? '[]',
            'selected_profile_fields' => $data['selected_profile_fields'] ?? '[]',
            'status' => $data['status'] ?? 'active',
            'created_at' => current_time('mysql')
        ));
    }

    public static function update_service($id, $data) {
        global $wpdb;
        $update_data = [];
        if (isset($data['name'])) $update_data['name'] = sanitize_text_field($data['name']);
        if (isset($data['category'])) $update_data['category'] = sanitize_text_field($data['category']);
        if (isset($data['branch'])) $update_data['branch'] = sanitize_text_field($data['branch']);
        if (isset($data['icon'])) $update_data['icon'] = sanitize_text_field($data['icon']);
        if (isset($data['requires_login'])) $update_data['requires_login'] = (int)$data['requires_login'];
        if (isset($data['description'])) $update_data['description'] = sanitize_textarea_field($data['description']);
        if (isset($data['fees'])) $update_data['fees'] = floatval($data['fees']);
        if (isset($data['status'])) $update_data['status'] = sanitize_text_field($data['status']);
        if (isset($data['required_fields'])) $update_data['required_fields'] = $data['required_fields'];
        if (isset($data['selected_profile_fields'])) $update_data['selected_profile_fields'] = $data['selected_profile_fields'];

        return $wpdb->update("{$wpdb->prefix}sm_services", $update_data, array('id' => intval($id)));
    }

    public static function delete_service($id, $permanent = false) {
        global $wpdb;
        if ($permanent) {
            $service = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sm_services WHERE id = %d", $id));
            if ($service) {
                 SM_Logger::log('حذف خدمة رقمية نهائياً', 'ROLLBACK_DATA:' . json_encode(['table' => 'services', 'data' => (array)$service]));
            }
            return $wpdb->delete("{$wpdb->prefix}sm_services", array('id' => $id));
        } else {
            return $wpdb->update("{$wpdb->prefix}sm_services", array('is_deleted' => 1), array('id' => intval($id)));
        }
    }

    public static function restore_service($id) {
        global $wpdb;
        return $wpdb->update("{$wpdb->prefix}sm_services", array('is_deleted' => 0), array('id' => intval($id)));
    }

    public static function submit_service_request($data) {
        global $wpdb;
        $insert_data = array(
            'service_id' => intval($data['service_id']),
            'member_id' => intval($data['member_id']),
            'request_data' => $data['request_data'], // JSON string
            'fees_paid' => 0,
            'status' => 'pending',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );
        if (isset($data['transaction_code'])) $insert_data['transaction_code'] = sanitize_text_field($data['transaction_code']);
        if (isset($data['payment_receipt_url'])) $insert_data['payment_receipt_url'] = esc_url_raw($data['payment_receipt_url']);

        $res = $wpdb->insert("{$wpdb->prefix}sm_service_requests", $insert_data);
        if ($res) {
            $request_id = $wpdb->insert_id;
            $member = SM_DB_Members::get_member_by_id($data['member_id']);
            $service = self::get_service_by_id($data['service_id']);
            SM_DB_System::save_alert([
                'title' => 'طلب خدمة جديد',
                'message' => 'قام العضو ' . ($member->name ?? 'ID:'.$data['member_id']) . ' بطلب خدمة: ' . ($service->name ?? 'ID:'.$data['service_id']),
                'severity' => 'info',
                'target_roles' => ['administrator', 'sm_general_officer', 'sm_branch_officer'],
                'target_branch' => $member->governorate ?? '',
                'target_url' => add_query_arg(['sm_tab' => 'digital-services', 'sub_tab' => 'requests'], home_url('/dashboard'))
            ]);
            return $request_id;
        }
        return false;
    }

    public static function get_service_requests($args = array()) {
        global $wpdb;
        $where = "1=1";
        $params = [];

        $user = wp_get_current_user();
        $has_full_access = current_user_can('sm_full_access') || current_user_can('manage_options');
        $my_gov = get_user_meta($user->ID, 'sm_governorate', true);

        if (!$has_full_access && $my_gov) {
            $where .= " AND m.governorate = %s";
            $params[] = $my_gov;
        }

        if (!empty($args['status'])) {
            $where .= " AND r.status = %s";
            $params[] = $args['status'];
        }

        if (!empty($args['member_id'])) {
            $where .= " AND r.member_id = %d";
            $params[] = intval($args['member_id']);
        }

        $query = "SELECT r.*, s.name as service_name, s.required_fields as service_fields, m.name as member_name, m.governorate, m.national_id, m.phone, m.email
                  FROM {$wpdb->prefix}sm_service_requests r
                  JOIN {$wpdb->prefix}sm_services s ON r.service_id = s.id
                  LEFT JOIN {$wpdb->prefix}sm_members m ON r.member_id = m.id
                  WHERE $where
                  ORDER BY r.created_at DESC";

        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($query, $params));
        }
        return $wpdb->get_results($query);
    }

    public static function update_service_request_status($request_id, $status, $fees_paid = null, $notes = '') {
        global $wpdb;
        $data = array(
            'status' => $status,
            'admin_notes' => sanitize_textarea_field($notes),
            'processed_by' => get_current_user_id(),
            'updated_at' => current_time('mysql')
        );
        if ($fees_paid !== null) $data['fees_paid'] = floatval($fees_paid);

        $res = $wpdb->update("{$wpdb->prefix}sm_service_requests", $data, array('id' => $request_id));

        if ($res !== false) {
            $req = $wpdb->get_row($wpdb->prepare("SELECT service_id, member_id FROM {$wpdb->prefix}sm_service_requests WHERE id = %d", $request_id));
            if ($req) {
                $member = SM_DB_Members::get_member_by_id($req->member_id);
                $service = self::get_service_by_id($req->service_id);
                SM_Finance::invalidate_financial_caches($member->governorate ?? null);

                if ($member && $member->wp_user_id) {
                    $m_user = get_userdata($member->wp_user_id);
                    if ($m_user) {
                        $status_map = ['approved' => 'مقبول', 'rejected' => 'مرفوض', 'processing' => 'قيد التنفيذ'];
                        $status_text = $status_map[$status] ?? $status;
                        SM_DB_System::save_alert([
                            'title' => 'تحديث حالة طلب الخدمة',
                            'message' => 'تم تغيير حالة طلبك لخدمة (' . ($service->name ?? '') . ') إلى: ' . $status_text,
                            'severity' => ($status === 'rejected' ? 'warning' : 'info'),
                            'target_users' => $m_user->user_login,
                            'target_url' => home_url('/my-account')
                        ]);
                    }
                }
            }
        }

        return $res;
    }

    public static function add_professional_request($member_id, $type) {
        global $wpdb;
        $res = $wpdb->insert("{$wpdb->prefix}sm_professional_requests", array(
            'member_id' => intval($member_id),
            'request_type' => $type,
            'status' => 'pending',
            'created_at' => current_time('mysql')
        ));
        if ($res) {
            $member = SM_DB_Members::get_member_by_id($member_id);
            $type_map = [
                'permit_test' => 'دخول امتحان مزاولة',
                'permit_renewal' => 'تجديد تصريح مزاولة',
                'facility_new' => 'ترخيص منشأة جديدة',
                'facility_renewal' => 'تجديد ترخيص منشأة'
            ];
            SM_DB_System::save_alert([
                'title' => 'طلب مهني جديد',
                'message' => 'قام العضو ' . ($member->name ?? 'ID:'.$member_id) . ' بتقديم طلب: ' . ($type_map[$type] ?? $type),
                'severity' => 'info',
                'target_roles' => ['administrator', 'sm_general_officer', 'sm_branch_officer'],
                'target_branch' => $member->governorate ?? '',
                'target_url' => add_query_arg('sm_tab', 'professional-requests', home_url('/dashboard'))
            ]);
        }
        return $res;
    }

    public static function get_professional_requests($args = []) {
        global $wpdb;
        $user = wp_get_current_user();
        $has_full_access = current_user_can('sm_full_access') || current_user_can('manage_options');
        $my_gov = get_user_meta($user->ID, 'sm_governorate', true);

        $query = "SELECT r.*, m.name as member_name, m.national_id, m.governorate
                 FROM {$wpdb->prefix}sm_professional_requests r
                 JOIN {$wpdb->prefix}sm_members m ON r.member_id = m.id WHERE 1=1";
        $params = [];

        if (!$has_full_access && $my_gov) {
            $query .= " AND m.governorate = %s";
            $params[] = $my_gov;
        }

        if (!empty($args['member_id'])) {
            $query .= " AND r.member_id = %d";
            $params[] = intval($args['member_id']);
        }
        if (!empty($args['status'])) {
            $query .= " AND r.status = %s";
            $params[] = $args['status'];
        }
        if (!empty($args['type'])) {
            $query .= " AND r.request_type = %s";
            $params[] = $args['type'];
        }
        if (!empty($args['governorate']) && $has_full_access) {
            $query .= " AND m.governorate = %s";
            $params[] = $args['governorate'];
        }

        $query .= " ORDER BY r.created_at DESC";
        if (!empty($params)) return $wpdb->get_results($wpdb->prepare($query, $params));
        return $wpdb->get_results($query);
    }

    public static function process_professional_request($id, $status, $notes = '') {
        global $wpdb;
        $res = $wpdb->update(
            "{$wpdb->prefix}sm_professional_requests",
            array(
                'status' => $status,
                'admin_notes' => sanitize_textarea_field($notes),
                'processed_at' => current_time('mysql'),
                'processed_by' => get_current_user_id()
            ),
            array('id' => intval($id))
        );
        if ($res) {
            $req = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sm_professional_requests WHERE id = %d", $id));
            if ($req) {
                $member = SM_DB_Members::get_member_by_id($req->member_id);
                if ($member && $member->wp_user_id) {
                    $m_user = get_userdata($member->wp_user_id);
                    if ($m_user) {
                        $status_text = ($status === 'approved' ? 'مقبول' : 'مرفوض');
                        SM_DB_System::save_alert([
                            'title' => 'تحديث طلب مهني',
                            'message' => 'تم ' . $status_text . ' طلبك المهني رقم: ' . $id,
                            'severity' => ($status === 'approved' ? 'info' : 'warning'),
                            'target_users' => $m_user->user_login,
                            'target_url' => home_url('/my-account')
                        ]);
                    }
                }
            }
        }
        return $res;
    }
}
