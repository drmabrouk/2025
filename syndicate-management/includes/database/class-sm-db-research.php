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
            'keywords' => sanitize_text_field($data['keywords'] ?? ''),
            'methodology' => sanitize_text_field($data['methodology'] ?? ''),
            'sample_size' => sanitize_text_field($data['sample_size'] ?? ''),
            'publication_year' => intval($data['publication_year'] ?? date('Y')),
            'doi' => sanitize_text_field($data['doi'] ?? ''),
            'supervisor' => sanitize_text_field($data['supervisor'] ?? ''),
            'guest_email' => sanitize_email($data['guest_email'] ?? ''),
            'guest_phone' => sanitize_text_field($data['guest_phone'] ?? ''),
            'guest_country' => sanitize_text_field($data['guest_country'] ?? ''),
            'submitted_by' => get_current_user_id() ?: 0,
            'submitted_at' => current_time('mysql'),
            'status' => 'pending'
        );

        $res = $wpdb->insert($table_name, $insert_data);
        if ($res) {
            $rid = $wpdb->insert_id;
            if (!empty($data['author_list']) && is_array($data['author_list'])) {
                foreach ($data['author_list'] as $idx => $name) {
                    $wpdb->insert($wpdb->prefix . 'sm_research_authors', [
                        'research_id' => $rid,
                        'author_name' => sanitize_text_field($name),
                        'is_main' => ($idx === 0) ? 1 : 0
                    ]);
                }
            }
            return $rid;
        }
        return false;
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
            $where .= " AND (title LIKE %s OR authors LIKE %s OR abstract LIKE %s OR keywords LIKE %s)";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }

        if (!empty($args['year'])) {
            $where .= " AND publication_year = %d";
            $params[] = intval($args['year']);
        }

        if (!empty($args['author_search'])) {
            $s = '%' . $wpdb->esc_like($args['author_search']) . '%';
            $where .= " AND authors LIKE %s";
            $params[] = $s;
        }

        if (!empty($args['submitted_by'])) {
            $where .= " AND submitted_by = %d";
            $params[] = intval($args['submitted_by']);
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

    public static function increment_metric($id, $metric) {
        global $wpdb;
        $allowed = ['view_count', 'like_count', 'download_count'];
        if (!in_array($metric, $allowed)) return false;
        return $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}sm_research SET $metric = $metric + 1 WHERE id = %d",
            $id
        ));
    }

    public static function toggle_favorite($rid, $uid) {
        global $wpdb;
        $table = $wpdb->prefix . 'sm_research_favorites';
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE research_id = %d AND user_id = %d", $rid, $uid));
        if ($exists) {
            return $wpdb->delete($table, ['id' => $exists]);
        } else {
            return $wpdb->insert($table, ['research_id' => $rid, 'user_id' => $uid]);
        }
    }

    public static function is_favorite($rid, $uid) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}sm_research_favorites WHERE research_id = %d AND user_id = %d", $rid, $uid));
    }

    public static function get_user_favorites($uid) {
        global $wpdb;
        $query = "SELECT r.* FROM {$wpdb->prefix}sm_research r
                  JOIN {$wpdb->prefix}sm_research_favorites f ON r.id = f.research_id
                  WHERE f.user_id = %d ORDER BY f.created_at DESC";
        return $wpdb->get_results($wpdb->prepare($query, $uid));
    }

    public static function get_research_authors($rid) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}sm_research_authors WHERE research_id = %d ORDER BY is_main DESC, id ASC", $rid));
    }
}
