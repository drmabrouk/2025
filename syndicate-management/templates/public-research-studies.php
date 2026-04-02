<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-research-page sm-public-page" dir="rtl">
    <!-- Header Section with Search and Action Buttons -->
    <div class="sm-research-header-new" style="max-width: 1200px; margin: 40px auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center;">
        <div class="sm-header-text-side">
            <h1 style="font-weight: 900; font-size: 2.4em; margin: 0; color: var(--sm-dark-color);">مركز الأبحاث والدراسات</h1>
            <p style="color: #64748b; font-size: 1.1em; margin-top: 8px; font-weight: 500;">مركز بحث علمي متخصص في علوم الصحة الرياضية والإصابات وعلوم التأهيل.</p>
        </div>

        <div class="sm-header-actions-side" style="display: flex; gap: 12px;">
            <button onclick="smOpenResearchSubmission()" class="sm-btn" style="height: 48px; padding: 0 25px; border-radius: 12px; font-weight: 800; font-size: 14px; background: var(--sm-primary-color);">
                <span class="dashicons dashicons-cloud-upload" style="margin-left:8px;"></span> تقديم بحث جديد
            </button>
            <a href="#search-anchor" class="sm-btn sm-btn-outline" style="height: 48px; padding: 0 25px; border-radius: 12px; font-weight: 800; font-size: 14px; border: 2px solid #e2e8f0; color: var(--sm-dark-color) !important;">
                استكشاف الأبحاث
            </a>
            <?php if(is_user_logged_in()): ?>
                <button onclick="smToggleFavoritesOnly(this)" class="sm-btn sm-btn-outline" style="height: 48px; width: 48px; padding: 0; border-radius: 12px; border: 2px solid #e2e8f0;">
                    <span class="dashicons dashicons-star-filled" style="color: #94a3b8;"></span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="sm-research-layout" id="search-anchor" style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 320px 1fr; gap: 40px; padding: 0 20px 80px 20px; align-items: start;">
        <!-- Sidebar Filters -->
        <aside class="sm-research-sidebar" style="position: sticky; top: 100px;">
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.03);">
                <h4 style="margin: 0 0 25px 0; font-weight: 900; color: var(--sm-dark-color); border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; font-size: 17px;">البحث المتقدم</h4>

                <form id="sm-research-filter-form">
                    <input type="hidden" name="show_favorites" value="0">

                    <div class="sm-form-group">
                        <input type="text" name="search" placeholder="عنوان البحث أو كلمات مفتاحية..." class="sm-input" oninput="smRefreshResearchList()" style="border-radius:12px; height: 45px;">
                    </div>

                    <div class="sm-form-group">
                        <input type="text" name="author" placeholder="اسم الباحث..." class="sm-input" oninput="smRefreshResearchList()" style="border-radius:12px; height: 45px;">
                    </div>

                    <div class="sm-form-group">
                        <select name="research_type" class="sm-select" onchange="smRefreshResearchList()" style="border-radius:12px; height: 45px;">
                            <option value="">كافة أنواع المواد العلمية</option>
                            <option value="journal_article">مقال علمي محكم</option>
                            <option value="master_thesis">رسالة ماجستير</option>
                            <option value="phd_dissertation">أطروحة دكتوراه</option>
                            <option value="case_study">دراسة حالة (Case Study)</option>
                            <option value="systematic_review">مراجعة منهجية (Systematic Review)</option>
                            <option value="meta_analysis">تحليل شمولي (Meta-analysis)</option>
                            <option value="book_chapter">فصل في كتاب</option>
                        </select>
                    </div>

                    <div class="sm-form-group">
                        <select name="year" class="sm-select" onchange="smRefreshResearchList()" style="border-radius:12px; height: 45px;">
                            <option value="">سنة النشر (الكل)</option>
                            <?php for($y = date('Y'); $y >= 2000; $y--) echo "<option value='$y'>$y</option>"; ?>
                        </select>
                    </div>

                    <div class="sm-form-group">
                        <select name="specialization" class="sm-select" onchange="smRefreshResearchList()" style="border-radius:12px; height: 45px;">
                            <option value="">كافة التخصصات</option>
                            <?php foreach(SM_Settings::get_specializations() as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                        </select>
                    </div>

                    <button type="reset" onclick="setTimeout(smRefreshResearchList, 10)" class="sm-btn sm-btn-outline" style="width: 100%; font-size: 13px; height: 45px; border-radius: 12px; font-weight: 700; margin-top: 10px;">إعادة تعيين</button>
                </form>
            </div>

            <div style="margin-top: 25px; background: #f8fafc; border-radius: 24px; padding: 30px; border: 1px solid #e2e8f0;">
                <h5 style="margin: 0 0 12px 0; font-weight: 800; color: var(--sm-dark-color); font-size: 15px;">تحتاج مساعدة؟</h5>
                <p style="font-size: 13px; color: #64748b; margin: 0; line-height: 1.7;">إذا واجهت مشكلة في البحث أو تقديم مادة علمية، يرجى التواصل مع فريق الدعم العلمي.</p>
                <a href="mailto:support@irseg.org" class="sm-read-more" style="margin-top: 15px; display: block; font-weight: 800;">support@irseg.org</a>
            </div>
        </aside>

        <!-- Search Results -->
        <main class="sm-research-main">
            <div id="sm-research-list-container">
                <div style="text-align: center; padding: 100px 0;">
                    <div class="sm-loader-mini"></div>
                    <p style="color: #94a3b8; margin-top: 15px; font-weight: 600;">جاري تحميل المكتبة العلمية...</p>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Submission Modal -->
<div id="sm-research-submit-modal" class="sm-modal-overlay" style="align-items: flex-start; padding: 40px 20px; overflow-y: auto;">
    <div class="sm-modal-content" style="max-width: 850px; padding: 0; overflow: visible; border-radius: 30px; margin: auto; box-shadow: 0 30px 60px rgba(0,0,0,0.2);">
        <div class="sm-modal-header" style="background: #f8fafc; padding: 30px 40px; margin: 0; border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 10; border-radius: 30px 30px 0 0;">
            <h2 style="font-weight: 900; margin: 0; font-size: 1.6em; color: var(--sm-dark-color);">تقديم مادة علمية جديدة</h2>
            <button class="sm-modal-close" onclick="document.getElementById('sm-research-submit-modal').style.display='none'">&times;</button>
        </div>
        <form id="sm-research-submission-form" style="padding: 40px;">
            <?php wp_nonce_field('sm_research_action', 'nonce'); ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div class="sm-form-group" style="grid-column: span 2;">
                    <input type="text" name="title" class="sm-input" required placeholder="عنوان البحث / الدراسة (30 حرفاً على الأقل)..." style="font-weight: 700; height: 50px;">
                </div>

                <div class="sm-form-group">
                    <select name="research_type" class="sm-select" required style="height: 50px;">
                        <option value="" disabled selected>نوع المادة العلمية...</option>
                        <option value="journal_article">مقال علمي محكم</option>
                        <option value="master_thesis">رسالة ماجستير</option>
                        <option value="phd_dissertation">أطروحة دكتوراه</option>
                        <option value="case_study">دراسة حالة (Case Study)</option>
                        <option value="systematic_review">مراجعة منهجية (Systematic Review)</option>
                        <option value="meta_analysis">تحليل شمولي (Meta-analysis)</option>
                        <option value="book_chapter">فصل في كتاب</option>
                    </select>
                </div>

                <div class="sm-form-group">
                    <input type="number" name="publication_year" class="sm-input" placeholder="سنة النشر..." value="<?php echo date('Y'); ?>" style="height: 50px;">
                </div>
            </div>

            <!-- Author Management -->
            <div style="background: #f8fafc; padding: 25px; border-radius: 20px; border: 1px solid #edf2f7; margin-bottom: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h5 style="margin: 0; font-weight: 800; color: var(--sm-dark-color);">بيانات المؤلفين</h5>
                    <label style="font-size: 12px; color: var(--sm-primary-color); font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" id="not-the-author" onchange="smToggleMainAuthor(this)"> لست أنا المؤلف الرئيسي
                    </label>
                </div>

                <div id="author-list-container">
                    <div class="author-item" style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <input type="text" name="author_list[]" class="sm-input author-input" required readonly value="<?php echo is_user_logged_in() ? wp_get_current_user()->display_name : ''; ?>" placeholder="اسم المؤلف الكامل (ثلاثي على الأقل)..." style="height: 45px;">
                        <button type="button" class="sm-btn sm-btn-outline" style="width: 45px; padding: 0; flex-shrink: 0; border-color: #eee; cursor: default;" disabled><span class="dashicons dashicons-admin-users"></span></button>
                    </div>
                </div>
                <button type="button" onclick="smAddAuthorField()" class="sm-btn sm-btn-outline" style="width: 100%; height: 40px; font-size: 12px; border-radius: 10px; margin-top: 5px; border-style: dashed;">+ إضافة مؤلف مشارك</button>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 25px;">
                <div class="sm-form-group">
                    <select name="university" class="sm-select" style="height: 50px;">
                        <option value="" disabled selected>الجامعة / المؤسسة العلمية...</option>
                        <?php foreach(SM_Settings::get_universities() as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                    </select>
                </div>
                <div class="sm-form-group">
                    <select name="specialization" class="sm-select" style="height: 50px;">
                        <option value="" disabled selected>التخصص الدقيق...</option>
                        <?php foreach(SM_Settings::get_specializations() as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                    </select>
                </div>
                <div class="sm-form-group">
                    <input type="text" name="methodology" class="sm-input" placeholder="منهجية البحث (مثلاً: وصفي، تجريبي)..." style="height: 50px;">
                </div>
                <div class="sm-form-group">
                    <input type="text" name="sample_size" class="sm-input" placeholder="عينة الدراسة..." style="height: 50px;">
                </div>
                <div class="sm-form-group">
                    <input type="text" name="doi" class="sm-input" placeholder="DOI (معرف الكائن الرقمي) إن وجد..." style="height: 50px;">
                </div>
                <div class="sm-form-group">
                    <input type="text" name="supervisor" class="sm-input" placeholder="اسم المشرف (للرسائل العلمية)..." style="height: 50px;">
                </div>
            </div>

            <div class="sm-form-group" style="margin-bottom: 20px;">
                <textarea name="keywords" class="sm-textarea" rows="2" placeholder="الكلمات المفتاحية (افصل بينها بفواصل)..." style="border-radius: 15px;"></textarea>
            </div>

            <div class="sm-form-group" style="margin-bottom: 25px;">
                <textarea name="abstract" class="sm-textarea" rows="6" required placeholder="الملخص العلمي (Abstract) - (500 حرف على الأقل)..." style="border-radius: 15px;"></textarea>
            </div>

            <div class="sm-form-group" style="margin-bottom: 35px; background: #fff5f5; padding: 25px; border-radius: 20px; border: 2px dashed var(--sm-primary-color);">
                <label class="sm-label" style="color: var(--sm-primary-color); font-weight: 800; margin-bottom: 15px; display: block; text-align: center;">رفع ملف المادة العلمية (PDF فقط)</label>
                <input type="file" name="research_file" accept=".pdf" class="sm-input" required style="padding: 12px; background: #fff; border-color: #feb2b2; height: auto;">
                <p style="font-size: 11px; color: #c53030; margin-top: 10px; font-weight: 600; text-align: center;">* يرجى التأكد من أن الملف بصيغة PDF ولا يتجاوز الحجم المسموح به.</p>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="sm-btn" style="width: auto; padding: 0 80px; height: 60px; font-size: 1.2em; font-weight: 900; border-radius: 18px; box-shadow: 0 10px 25px rgba(246, 48, 73, 0.2);">إرسال المادة العلمية للمراجعة</button>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal (Remains same) -->
<div id="sm-research-preview-modal" class="sm-modal-overlay">
    <div class="sm-modal-content" style="max-width: 1000px; padding: 0; overflow: hidden; border-radius: 20px; height: 90vh; display: flex; flex-direction: column;">
        <div class="sm-modal-header" style="padding: 20px 30px; margin: 0; flex-shrink: 0; background: #fff;">
            <h3 id="preview-title" style="font-weight: 900; margin: 0; color: var(--sm-dark-color);">معاينة المادة العلمية</h3>
            <button class="sm-modal-close" onclick="document.getElementById('sm-research-preview-modal').style.display='none'">&times;</button>
        </div>
        <div id="preview-frame-container" style="flex: 1; background: #525659;">
            <div style="text-align: center; color: #fff; padding-top: 50px;">
                <div class="sm-loader-mini" style="border-top-color: #fff;"></div>
                <p>جاري تحميل ملف PDF للمعاينة...</p>
            </div>
        </div>
    </div>
</div>

<script>
window.smOpenResearchSubmission = function() {
    <?php if(!is_user_logged_in()): ?>
        if(confirm('يجب تسجيل الدخول لتقديم مادة علمية. هل تود الانتقال لصفحة الدخول؟')) {
            window.location.href = '<?php echo home_url('/sm-login'); ?>';
        }
        return;
    <?php endif; ?>
    document.getElementById('sm-research-submit-modal').style.display = 'flex';
};

window.smRefreshResearchList = function() {
    const container = document.getElementById('sm-research-list-container');
    const form = document.getElementById('sm-research-filter-form');
    const fd = new FormData(form);
    const params = new URLSearchParams(fd).toString();

    fetch(ajaxurl + '?action=sm_get_researches_html&' + params)
    .then(r => r.json())
    .then(res => {
        if(res.success) container.innerHTML = res.data.html;
    });
};

window.smToggleFavoritesOnly = function(btn) {
    const form = document.getElementById('sm-research-filter-form');
    const input = form.querySelector('input[name="show_favorites"]');
    const icon = btn.querySelector('.dashicons');

    if (input.value === "1") {
        input.value = "0";
        icon.style.color = "#94a3b8";
        btn.style.borderColor = "#e2e8f0";
    } else {
        input.value = "1";
        icon.style.color = "#d69e2e";
        btn.style.borderColor = "#d69e2e";
    }
    smRefreshResearchList();
};

window.smAddAuthorField = function() {
    const container = document.getElementById('author-list-container');
    const div = document.createElement('div');
    div.className = 'author-item';
    div.style.display = 'flex';
    div.style.gap = '10px';
    div.style.marginBottom = '10px';
    div.innerHTML = `
        <input type="text" name="author_list[]" class="sm-input author-input" required placeholder="اسم المؤلف المشارك الكامل..." style="height: 45px;">
        <button type="button" onclick="this.parentElement.remove()" class="sm-btn sm-btn-outline" style="width: 45px; padding: 0; flex-shrink: 0; border-color: #feb2b2; color: #e53e3e !important;"><span class="dashicons dashicons-trash"></span></button>
    `;
    container.appendChild(div);
};

window.smToggleMainAuthor = function(cb) {
    const mainInput = document.querySelector('#author-list-container .author-item:first-child .author-input');
    if (cb.checked) {
        mainInput.readOnly = false;
        mainInput.value = '';
        mainInput.placeholder = 'اسم المؤلف الرئيسي الكامل (ثلاثي)...';
    } else {
        mainInput.readOnly = true;
        mainInput.value = '<?php echo is_user_logged_in() ? wp_get_current_user()->display_name : ""; ?>';
    }
};

document.getElementById('sm-research-submission-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerText = 'جاري المعالجة والرفع...';

    const fd = new FormData(this);
    fd.append('action', 'sm_submit_research');

    fetch(ajaxurl + '?action=sm_submit_research', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            alert(res.data);
            location.reload();
        } else {
            alert(res.data.message || 'خطأ غير معروف');
            btn.disabled = false;
            btn.innerText = 'إرسال المادة العلمية للمراجعة';
        }
    });
});

window.smToggleResearchAbstract = function(id) {
    const content = document.getElementById('research-abstract-' + id);
    const icon = document.getElementById('research-icon-' + id);
    const isHidden = content.style.display === 'none';
    content.style.display = isHidden ? 'block' : 'none';
    icon.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
};

window.smPreviewResearch = function(url, title) {
    document.getElementById('preview-title').innerText = title;
    document.getElementById('preview-frame-container').innerHTML = `<iframe src="${url}" width="100%" height="100%" style="border:none;"></iframe>`;
    document.getElementById('sm-research-preview-modal').style.display = 'flex';
    smRecordInteraction(url.split('/').pop(), 'view'); // Simple tracking
};

window.smRecordInteraction = function(id, type) {
    const fd = new FormData();
    fd.append('action', 'sm_record_research_interaction');
    fd.append('id', id);
    fd.append('type', type);
    fetch(ajaxurl + '?action=sm_record_research_interaction', { method: 'POST', body: fd });
};

window.smToggleFavorite = function(id, btn) {
    const fd = new FormData();
    fd.append('action', 'sm_toggle_favorite_research');
    fd.append('id', id);
    fd.append('nonce', '<?php echo wp_create_nonce("sm_research_action"); ?>');
    fetch(ajaxurl + '?action=sm_toggle_favorite_research', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(res => {
        if(res.success) {
            const icon = btn.querySelector('.dashicons');
            if (res.data === 'deleted' || res.data === false || !res.data) {
                icon.className = 'dashicons dashicons-star-empty';
                icon.style.color = '#94a3b8';
            } else {
                icon.className = 'dashicons dashicons-star-filled';
                icon.style.color = '#d69e2e';
            }
        }
    });
};

window.smDownloadResearch = function(id, url) {
    <?php if(!is_user_logged_in()): ?>
        if(confirm('يجب تسجيل الدخول لتحميل الملف الكامل. هل تود الانتقال لصفحة الدخول؟')) {
            window.location.href = '<?php echo home_url('/sm-login'); ?>';
        }
        return;
    <?php else: ?>
        smRecordInteraction(id, 'download');
        window.open(url, '_blank');
    <?php endif; ?>
};

document.addEventListener('DOMContentLoaded', () => smRefreshResearchList());
</script>

<style>
.sm-research-sidebar .sm-form-group { margin-bottom: 20px; }
.sm-research-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 35px; margin-bottom: 25px;
    transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.02); position: relative; overflow: hidden;
}
.sm-research-card:hover { border-color: var(--sm-primary-color); transform: translateY(-3px); box-shadow: 0 15px 40px rgba(0,0,0,0.06); }
.sm-research-card.featured { border-right: 6px solid #d69e2e; }
.sm-research-card.featured::after { content: "متميز"; position: absolute; top: 15px; left: -30px; background: #d69e2e; color: #fff; padding: 5px 35px; transform: rotate(-45deg); font-size: 10px; font-weight: 800; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }

@media (max-width: 992px) {
    .sm-research-header-new { flex-direction: column; text-align: center; gap: 25px; }
    .sm-research-layout { grid-template-columns: 1fr !important; }
    .sm-research-sidebar { position: static !important; }
}
</style>
