<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-admin-research-wrap" dir="rtl">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="font-weight: 900; color: var(--sm-dark-color); margin: 0;">إدارة مركز الأبحاث والدراسات</h2>
    </div>

    <!-- Admin Filter/Search Box -->
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 15px; padding: 20px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <form id="sm-admin-research-filter" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
            <div class="sm-form-group" style="margin-bottom: 0;">
                <label class="sm-label" style="font-size: 11px;">البحث بالكلمات:</label>
                <input type="text" name="search" placeholder="عنوان، مؤلف..." class="sm-input" style="height: 40px; font-size: 13px;">
            </div>
            <div class="sm-form-group" style="margin-bottom: 0;">
                <label class="sm-label" style="font-size: 11px;">الحالة:</label>
                <select name="status" class="sm-select" style="height: 40px; font-size: 13px;">
                    <option value="">كافة الحالات</option>
                    <option value="pending">قيد المراجعة</option>
                    <option value="approved">منشور</option>
                    <option value="disabled">معطل</option>
                </select>
            </div>
            <div class="sm-form-group" style="margin-bottom: 0;">
                <label class="sm-label" style="font-size: 11px;">الجامعة:</label>
                <select name="university" class="sm-select" style="height: 40px; font-size: 13px;">
                    <option value="">كافة الجامعات</option>
                    <?php foreach(SM_Settings::get_universities() as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                </select>
            </div>
            <button type="button" onclick="smRefreshAdminResearchList()" class="sm-btn" style="height: 40px; font-weight: 800;">تطبيق الفلاتر</button>
        </form>
    </div>

    <div id="sm-admin-research-list-container" class="sm-table-container">
        <!-- Table will be loaded here via AJAX if we want dynamic, but for now we render normally and refresh page or use JS filter -->
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
            <tbody id="sm-admin-research-tbody">
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
                    <tr data-status="<?php echo $r->status; ?>" data-university="<?php echo $r->university; ?>" data-title="<?php echo esc_attr($r->title); ?>" data-authors="<?php echo esc_attr($r->authors); ?>">
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

                                <button onclick="smPrintResearch(<?php echo $r->id; ?>)" class="sm-btn" style="padding: 4px 8px; font-size: 10px; background: #4a5568;" title="طباعة بيانات البحث"><span class="dashicons dashicons-printer" style="font-size:14px; width:14px; height:14px;"></span></button>

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
window.smRefreshAdminResearchList = function() {
    const form = document.getElementById('sm-admin-research-filter');
    const search = form.search.value.toLowerCase();
    const status = form.status.value;
    const university = form.university.value;

    const rows = document.querySelectorAll('#sm-admin-research-tbody tr');
    rows.forEach(row => {
        if (row.children.length < 2) return; // Skip empty message row

        let show = true;
        if (status && row.dataset.status !== status) show = false;
        if (university && row.dataset.university !== university) show = false;
        if (search && !(row.dataset.title.toLowerCase().includes(search) || row.dataset.authors.toLowerCase().includes(search))) show = false;

        row.style.display = show ? '' : 'none';
    });
};

window.smPrintResearch = function(id) {
    const printUrl = ajaxurl + '?action=sm_print_research&id=' + id + '&nonce=<?php echo wp_create_nonce("sm_print_nonce"); ?>';
    const win = window.open(printUrl, '_blank');
    win.focus();
};

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
