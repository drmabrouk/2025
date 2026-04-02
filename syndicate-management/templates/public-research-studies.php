<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-research-page sm-public-page" dir="rtl">
    <!-- Hero Section with Submission Action -->
    <div class="sm-research-hero" style="background: linear-gradient(135deg, var(--sm-dark-color) 0%, #1a365d 100%); padding: 60px 20px; text-align: center; border-radius: 40px; color: #fff; margin: 20px auto 40px; max-width: 1200px; box-shadow: 0 15px 35px rgba(0,0,0,0.2);">
        <div style="max-width: 800px; margin: 0 auto;">
            <div style="display:inline-flex; align-items:center; justify-content:center; width:60px; height:60px; background:rgba(255,255,255,0.1); border-radius:20px; margin-bottom:20px; backdrop-filter:blur(10px);">
                <span class="dashicons dashicons-book-alt" style="font-size:32px; width:32px; height:32px; color:#fff;"></span>
            </div>
            <h1 style="font-weight: 900; font-size: 2.8em; margin: 0; color:#fff;">مركز الأبحاث والدراسات</h1>
            <p style="color: rgba(255,255,255,0.8); font-size: 1.1em; margin-top: 15px; font-weight: 500;">محرك بحث علمي متخصص في علوم الإصابات الرياضية والتأهيل</p>

            <div style="margin-top: 35px; display: flex; gap: 15px; justify-content: center;">
                <button onclick="smOpenResearchSubmission()" class="sm-btn" style="background:var(--sm-primary-color); height:55px; padding:0 35px; border-radius:15px; font-weight:800; font-size:16px;">
                    <span class="dashicons dashicons-cloud-upload" style="margin-left:8px;"></span> تقديم بحث جديد
                </button>
                <a href="#search-anchor" class="sm-btn sm-btn-outline" style="background:rgba(255,255,255,0.1); color:#fff !important; border:1px solid rgba(255,255,255,0.3); height:55px; padding:0 35px; border-radius:15px; font-weight:800; font-size:16px; text-decoration:none !important; display:flex; align-items:center;">
                    استكشاف الأبحاث
                </a>
            </div>
        </div>
    </div>

    <div class="sm-research-layout" id="search-anchor" style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 300px 1fr; gap: 30px; padding: 0 20px 60px 20px;">
        <!-- Sidebar Filters -->
        <aside class="sm-research-sidebar">
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 25px; position: sticky; top: 100px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                <h4 style="margin: 0 0 20px 0; font-weight: 900; color: var(--sm-dark-color); border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; font-size: 16px;">تصفية النتائج</h4>

                <form id="sm-research-filter-form">
                    <div class="sm-form-group">
                        <label class="sm-label" style="font-size:12px;">البحث بالكلمات المفتاحية:</label>
                        <input type="text" name="search" placeholder="عنوان البحث، المؤلف..." class="sm-input" oninput="smRefreshResearchList()" style="font-size:13px; border-radius:10px;">
                    </div>

                    <div class="sm-form-group">
                        <label class="sm-label" style="font-size:12px;">نوع البحث:</label>
                        <select name="research_type" class="sm-select" onchange="smRefreshResearchList()" style="font-size:13px; border-radius:10px;">
                            <option value="">كافة الأنواع</option>
                            <option value="journal_article">مقال علمي محكم</option>
                            <option value="master_thesis">رسالة ماجستير</option>
                            <option value="phd_dissertation">أطروحة دكتوراه</option>
                            <option value="case_study">دراسة حالة</option>
                            <option value="book_chapter">فصل في كتاب</option>
                        </select>
                    </div>

                    <div class="sm-form-group">
                        <label class="sm-label" style="font-size:12px;">الجامعة / المؤسسة:</label>
                        <select name="university" class="sm-select" onchange="smRefreshResearchList()" style="font-size:13px; border-radius:10px;">
                            <option value="">كافة الجامعات</option>
                            <?php foreach(SM_Settings::get_universities() as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                        </select>
                    </div>

                    <div class="sm-form-group">
                        <label class="sm-label" style="font-size:12px;">التخصص:</label>
                        <select name="specialization" class="sm-select" onchange="smRefreshResearchList()" style="font-size:13px; border-radius:10px;">
                            <option value="">كافة التخصصات</option>
                            <?php foreach(SM_Settings::get_specializations() as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                        </select>
                    </div>

                    <button type="reset" onclick="setTimeout(smRefreshResearchList, 10)" class="sm-btn sm-btn-outline" style="width: 100%; font-size: 12px; height: 40px; border-radius: 10px;">إعادة تعيين الفلاتر</button>
                </form>
            </div>

            <div style="margin-top: 20px; background: #f8fafc; border-radius: 20px; padding: 25px; border: 1px solid #e2e8f0;">
                <h5 style="margin: 0 0 10px 0; font-weight: 800; color: var(--sm-dark-color); font-size: 14px;">تحتاج مساعدة؟</h5>
                <p style="font-size: 12px; color: #64748b; margin: 0; line-height: 1.6;">إذا واجهت مشكلة في البحث أو تقديم مادة علمية، يرجى التواصل مع الدعم الفني.</p>
                <a href="mailto:support@irseg.org" class="sm-read-more" style="margin-top: 10px; display: block;">support@irseg.org</a>
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
    <div class="sm-modal-content" style="max-width: 800px; padding: 0; overflow: visible; border-radius: 25px; margin: auto;">
        <div class="sm-modal-header" style="background: #f8fafc; padding: 25px 35px; margin: 0; border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 10;">
            <h2 style="font-weight: 900; margin: 0; font-size: 1.5em; color: var(--sm-dark-color);">تقديم مادة علمية جديدة</h2>
            <button class="sm-modal-close" onclick="document.getElementById('sm-research-submit-modal').style.display='none'">&times;</button>
        </div>
        <form id="sm-research-submission-form" style="padding: 35px;">
            <?php wp_nonce_field('sm_research_action', 'nonce'); ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
                <div class="sm-form-group" style="grid-column: span 2;">
                    <label class="sm-label">عنوان البحث / الدراسة:</label>
                    <input type="text" name="title" class="sm-input" required placeholder="أدخل العنوان الكامل للدراسة..." style="font-weight: 700;">
                </div>

                <div class="sm-form-group">
                    <label class="sm-label">نوع البحث:</label>
                    <select name="research_type" class="sm-select" required>
                        <option value="journal_article">مقال علمي محكم</option>
                        <option value="master_thesis">رسالة ماجستير</option>
                        <option value="phd_dissertation">أطروحة دكتوراه</option>
                        <option value="case_study">دراسة حالة</option>
                        <option value="book_chapter">فصل في كتاب</option>
                    </select>
                </div>

                <div class="sm-form-group">
                    <label class="sm-label">المؤلف (أو المؤلفون):</label>
                    <input type="text" name="authors" class="sm-input" required placeholder="اسم الباحث الرئيسي..." value="<?php echo is_user_logged_in() ? wp_get_current_user()->display_name : ''; ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px; background: #f8fafc; padding: 20px; border-radius: 15px; border: 1px solid #edf2f7;">
                <div class="sm-form-group" style="margin-bottom: 0;">
                    <label class="sm-label" style="font-size:12px;">الجامعة:</label>
                    <select name="university" class="sm-select">
                        <?php foreach(SM_Settings::get_universities() as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                    </select>
                </div>
                <div class="sm-form-group" style="margin-bottom: 0;">
                    <label class="sm-label" style="font-size:12px;">القسم العلمي:</label>
                    <select name="department" class="sm-select">
                        <?php foreach(SM_Settings::get_departments() as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                    </select>
                </div>
                <div class="sm-form-group" style="margin-bottom: 0;">
                    <label class="sm-label" style="font-size:12px;">التخصص الدقيق:</label>
                    <select name="specialization" class="sm-select">
                        <?php foreach(SM_Settings::get_specializations() as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                    </select>
                </div>
            </div>

            <div class="sm-form-group" style="margin-bottom: 25px;">
                <label class="sm-label">الملخص (Abstract):</label>
                <textarea name="abstract" class="sm-textarea" rows="5" required placeholder="موجز عن أهداف ومنهجية ونتائج البحث..."></textarea>
            </div>

            <div class="sm-form-group" style="margin-bottom: 35px; background: #fff5f5; padding: 20px; border-radius: 15px; border: 1px dashed var(--sm-primary-color);">
                <label class="sm-label" style="color: var(--sm-primary-color); font-weight: 800;">رفع ملف البحث (PDF فقط):</label>
                <input type="file" name="research_file" accept=".pdf" class="sm-input" required style="padding: 10px; background: #fff; border-color: #feb2b2;">
                <p style="font-size: 11px; color: #c53030; margin-top: 8px; font-weight: 600;">* يرجى التأكد من عدم وجود بيانات سرية أو غير مصرح بنشرها في الملف.</p>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="sm-btn" style="width: auto; padding: 0 60px; height: 55px; font-size: 1.1em; font-weight: 900; border-radius: 15px;">إرسال المادة العلمية الآن</button>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
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
            btn.innerText = 'إرسال المادة العلمية الآن';
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
};

window.smDownloadResearch = function(url) {
    <?php if(!is_user_logged_in()): ?>
        if(confirm('يجب تسجيل الدخول لتحميل الملف الكامل. هل تود الانتقال لصفحة الدخول؟')) {
            window.location.href = '<?php echo home_url('/sm-login'); ?>';
        }
        return;
    <?php else: ?>
        window.open(url, '_blank');
    <?php endif; ?>
};

document.addEventListener('DOMContentLoaded', () => smRefreshResearchList());
</script>

<style>
.sm-research-sidebar .sm-form-group { margin-bottom: 15px; }
.sm-research-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 30px; margin-bottom: 20px;
    transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.02); position: relative; overflow: hidden;
}
.sm-research-card:hover { border-color: var(--sm-primary-color); transform: translateY(-3px); box-shadow: 0 15px 30px rgba(0,0,0,0.05); }
.sm-research-card.featured { border-right: 5px solid #d69e2e; }
.sm-research-card.featured::after { content: "متميز"; position: absolute; top: 15px; left: -30px; background: #d69e2e; color: #fff; padding: 5px 35px; transform: rotate(-45deg); font-size: 10px; font-weight: 800; }

@media (max-width: 992px) {
    .sm-research-layout { grid-template-columns: 1fr !important; }
    .sm-research-sidebar { order: -1; }
    .sm-research-sidebar > div { position: static !important; }
}
</style>
