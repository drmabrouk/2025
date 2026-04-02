<?php
if (!defined('ABSPATH')) {
    exit;
}

class SM_DB_Research {

    public static function add_research($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sm_research';

        $insert_data = array(
            'title' => sanitize_text_field($data['title']),
            'abstract' => sanitize_textarea_field($data['abstract']),
            'research_type' => sanitize_text_field($data['research_type']),
            'authors' => sanitize_text_field($data['authors']),
            'university' => sanitize_text_field($data['university']),
            'department' => sanitize_text_field($data['department']),
            'specialization' => sanitize_text_field($data['specialization']),
            'file_url' => esc_url_raw($data['file_url']),
            'submitted_by' => get_current_user_id(),
            'submitted_at' => current_time('mysql'),
            'status' => 'pending'
        );

        $res = $wpdb->insert($table_name, $insert_data);
        return $res ? $wpdb->insert_id : false;
    }

    public static function get_researches($args = []) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sm_research';

        $where = "1=1";
        $params = [];

        if (!empty($args['status'])) {
            $where .= " AND status = %s";
            $params[] = $args['status'];
        }

        if (!empty($args['university'])) {
            $where .= " AND university = %s";
            $params[] = $args['university'];
        }

        if (!empty($args['department'])) {
            $where .= " AND department = %s";
            $params[] = $args['department'];
        }

        if (!empty($args['specialization'])) {
            $where .= " AND specialization = %s";
            $params[] = $args['specialization'];
        }

        if (!empty($args['research_type'])) {
            $where .= " AND research_type = %s";
            $params[] = $args['research_type'];
        }

        if (!empty($args['search'])) {
            $s = '%' . $wpdb->esc_like($args['search']) . '%';
            $where .= " AND (title LIKE %s OR authors LIKE %s OR abstract LIKE %s)";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        $order = "submitted_at DESC";
        if (!empty($args['featured_first'])) {
            $order = "is_featured DESC, " . $order;
        }

        $query = "SELECT * FROM $table_name WHERE $where ORDER BY $order";

        if (!empty($params)) {
            return $wpdb->get_results($wpdb->prepare($query, $params));
        }
        return $wpdb->get_results($query);
    }

    public static function get_research($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sm_research';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));
    }

    public static function update_research($id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sm_research';
        return $wpdb->update($table_name, $data, array('id' => $id));
    }

    public static function delete_research($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sm_research';
        return $wpdb->delete($table_name, array('id' => $id));
    }

    public static function toggle_featured($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'sm_research';
        $current = $wpdb->get_var($wpdb->prepare("SELECT is_featured FROM $table_name WHERE id = %d", $id));
        return $wpdb->update($table_name, array('is_featured' => !$current), array('id' => $id));
    }
}
