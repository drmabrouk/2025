<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-admin-research-wrap" dir="rtl">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="font-weight: 900; color: var(--sm-dark-color); margin: 0;">إدارة الأبحاث والدراسات العلمية</h2>
    </div>

    <div class="sm-table-container">
        <table class="sm-table">
            <thead>
                <tr>
                    <th>تاريخ التقديم</th>
                    <th>عنوان البحث</th>
                    <th>الباحث / المؤسسة</th>
                    <th>النوع</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $researches = SM_DB_Research::get_researches();
                if (empty($researches)): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 30px; color: #94a3b8;">لا توجد طلبات أبحاث مسجلة حالياً.</td></tr>
                <?php else: foreach ($researches as $r):
                    $type_map = [
                        'journal_article' => 'مقال محكم',
                        'master_thesis' => 'ماجستير',
                        'phd_dissertation' => 'دكتوراه',
                        'case_study' => 'دراسة حالة',
                        'book_chapter' => 'فصل كتاب'
                    ];
                    $status_map = [
                        'pending' => ['label' => 'قيد المراجعة', 'class' => 'sm-badge-medium'],
                        'approved' => ['label' => 'منشور', 'class' => 'sm-badge-high'],
                        'disabled' => ['label' => 'معطل', 'class' => 'sm-badge-low']
                    ];
                ?>
                    <tr>
                        <td><?php echo date('Y/m/d', strtotime($r->submitted_at)); ?></td>
                        <td>
                            <strong><?php echo esc_html($r->title); ?></strong>
                            <?php if ($r->is_featured): ?>
                                <span style="background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px; font-size: 10px; margin-right: 5px; font-weight: 800;">متميز</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-size: 12px;"><?php echo esc_html($r->authors); ?></div>
                            <div style="font-size: 10px; color: #94a3b8;"><?php echo SM_Settings::get_universities()[$r->university] ?? $r->university; ?></div>
                        </td>
                        <td><span class="sm-badge sm-badge-low"><?php echo $type_map[$r->research_type] ?? $r->research_type; ?></span></td>
                        <td><span class="sm-badge <?php echo $status_map[$r->status]['class'] ?? ''; ?>"><?php echo $status_map[$r->status]['label'] ?? $r->status; ?></span></td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <button onclick="smPreviewResearch('<?php echo esc_url($r->file_url); ?>', '<?php echo esc_js($r->title); ?>')" class="sm-btn" style="padding: 4px 8px; font-size: 10px; background: #2d3748;"><span class="dashicons dashicons-visibility" style="font-size:14px; width:14px; height:14px;"></span></button>

                                <?php if ($r->status !== 'approved'): ?>
                                    <button onclick="smUpdateResearchStatus(<?php echo $r->id; ?>, 'approved')" class="sm-btn" style="padding: 4px 8px; font-size: 10px; background: #38a169;" title="موافقة ونشر">نشر</button>
                                <?php else: ?>
                                    <button onclick="smUpdateResearchStatus(<?php echo $r->id; ?>, 'disabled')" class="sm-btn" style="padding: 4px 8px; font-size: 10px; background: #718096;" title="تعطيل العرض">تعطيل</button>
                                <?php endif; ?>

                                <button onclick="smToggleFeaturedResearch(<?php echo $r->id; ?>)" class="sm-btn" style="padding: 4px 8px; font-size: 10px; background: #d69e2e;" title="تمييز البحث"><span class="dashicons dashicons-star-filled" style="font-size:14px; width:14px; height:14px;"></span></button>

                                <?php if (current_user_can('sm_full_access')): ?>
                                    <button onclick="smDeleteResearch(<?php echo $r->id; ?>)" class="sm-btn" style="padding: 4px 8px; font-size: 10px; background: #e53e3e;" title="حذف نهائي"><span class="dashicons dashicons-trash" style="font-size:14px; width:14px; height:14px;"></span></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
window.smUpdateResearchStatus = function(id, status) {
    if (!confirm('هل أنت متأكد من تغيير حالة هذا البحث؟')) return;
    const fd = new FormData();
    fd.append('action', 'sm_update_research_status');
    fd.append('id', id);
    fd.append('status', status);
    fd.append('nonce', '<?php echo wp_create_nonce("sm_admin_action"); ?>');

    fetch(ajaxurl + '?action=sm_update_research_status', { method: 'POST', body: fd })
    .then(r => r.json()).then(res => {
        if(res.success) location.reload();
        else alert(res.data.message);
    });
};

window.smToggleFeaturedResearch = function(id) {
    const fd = new FormData();
    fd.append('action', 'sm_toggle_featured_research');
    fd.append('id', id);
    fd.append('nonce', '<?php echo wp_create_nonce("sm_admin_action"); ?>');

    fetch(ajaxurl + '?action=sm_toggle_featured_research', { method: 'POST', body: fd })
    .then(r => r.json()).then(res => {
        if(res.success) location.reload();
        else alert(res.data.message);
    });
};

window.smDeleteResearch = function(id) {
    if (!confirm('تحذير: سيتم حذف البحث وكافة بياناته نهائياً. هل أنت متأكد؟')) return;
    const fd = new FormData();
    fd.append('action', 'sm_delete_research_ajax');
    fd.append('id', id);
    fd.append('nonce', '<?php echo wp_create_nonce("sm_admin_action"); ?>');

    fetch(ajaxurl + '?action=sm_delete_research_ajax', { method: 'POST', body: fd })
    .then(r => r.json()).then(res => {
        if(res.success) location.reload();
        else alert(res.data.message);
    });
};
</script>
