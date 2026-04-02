<?php if (!defined('ABSPATH')) exit; ?>
<div class="sm-research-page sm-public-page" dir="rtl">
    <!-- Header Section -->
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

<!-- NEW Scientific Submission Wizard Modal -->
<div id="sm-research-submit-modal" class="sm-modal-overlay" style="align-items: flex-start; padding: 20px; overflow-y: auto;">
    <div class="sm-modal-content" style="max-width: 1000px; padding: 0; overflow: visible; border-radius: 30px; margin: 40px auto; box-shadow: 0 30px 80px rgba(0,0,0,0.25);">
        <div class="sm-modal-header" style="background: #f8fafc; padding: 25px 40px; margin: 0; border-bottom: 1px solid #eee; position: sticky; top: 0; z-index: 100; border-radius: 30px 30px 0 0; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="width: 45px; height: 45px; background: var(--sm-primary-color); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #fff;">
                    <span class="dashicons dashicons-cloud-upload" style="font-size: 24px; width: 24px; height: 24px;"></span>
                </div>
                <div>
                    <h2 style="font-weight: 900; margin: 0; font-size: 1.4em; color: var(--sm-dark-color);">نظام التقديم العلمي المتطور</h2>
                    <p style="margin: 0; font-size: 11px; color: #94a3b8; font-weight: 600;">يرجى اتباع الخطوات لإتمام عملية تقديم المادة العلمية</p>
                </div>
            </div>
            <button class="sm-modal-close" onclick="document.getElementById('sm-research-submit-modal').style.display='none'">&times;</button>
        </div>

        <!-- System Overview Description -->
        <div style="background: #fff; padding: 25px 40px; border-bottom: 1px solid #f1f5f9;">
            <p style="margin: 0; color: #4a5568; font-size: 13.5px; line-height: 1.8; font-weight: 500;">يتيح نظام التقديم العلمي للباحثين والمتخصصين والأكاديميين تقديم أبحاثهم ودراساتهم وموادهم العلمية من خلال عملية منظمة ومراجعة مهنية. يضمن النظام دقة البيانات، والامتثال لمعايير النشر، ويسهل عملية المراجعة والنشر في بيئة متخصصة في علوم الصحة الرياضية والإصابات والتأهيل.</p>
        </div>

        <!-- Wizard Stepper UI -->
        <div class="sm-wizard-stepper" style="display: flex; justify-content: space-between; padding: 30px 60px; background: #fcfcfc; border-bottom: 1px solid #eee;">
            <div class="sm-step-item active" data-step="1"><span>1</span><label>الاتفاقية</label></div>
            <div class="sm-step-item" data-step="2"><span>2</span><label>بيانات الباحث</label></div>
            <div class="sm-step-item" data-step="3"><span>3</span><label>تفاصيل البحث</label></div>
            <div class="sm-step-item" data-step="4"><span>4</span><label>رفع الملفات</label></div>
            <div class="sm-step-item" data-step="5"><span>5</span><label>المراجعة</label></div>
        </div>

        <form id="sm-research-submission-form" style="padding: 0;">
            <?php wp_nonce_field('sm_research_action', 'nonce'); ?>

            <!-- STEP 1: Policy & Agreement -->
            <div class="sm-wizard-step" id="step-content-1">
                <div style="padding: 40px;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px; padding: 35px; margin-bottom: 30px; max-height: 400px; overflow-y: auto;">
                        <h4 style="margin: 0 0 20px 0; font-weight: 900; color: var(--sm-primary-color);">اتفاقية النشر العلمي والموافقة القانونية</h4>
                        <div style="color: #4a5568; line-height: 2; font-size: 15px; text-align: justify;">
                            بتقديم هذه المادة العلمية، أقر بأن هذا العمل أصيل أو أنني أملك كامل الحقوق القانونية لنشره. أقر بأن البحث قد يكون قد نُشر مسبقاً أو قُدم في مكان آخر، وأوافق تماماً على عرضه ونشره ضمن المنصة للوصول العام. أصرح بأن جميع المعلومات المقدمة دقيقة وأنني أتحمل المسؤولية الكاملة عن المحتوى والتأليف وحقوق الملكية الفكرية. أوافق على أن للمنصة الحق في مراجعة الطلب أو قبوله أو رفضه وفقاً لسياساتها ومعاييرها العلمية.
                        </div>
                    </div>
                    <label style="display: flex; align-items: center; gap: 15px; cursor: pointer; background: #fff5f5; padding: 20px; border-radius: 15px; border: 1px solid #feb2b2;">
                        <input type="checkbox" id="policy-agree-1" style="width: 22px; height: 22px; accent-color: var(--sm-primary-color);">
                        <strong style="color: var(--sm-dark-color); font-size: 15px;">أوافق على كافة الشروط والسياسات المذكورة أعلاه وأتحمل المسؤولية القانونية.</strong>
                    </label>
                </div>
            </div>

            <!-- STEP 2: Basic Information (Researcher Details) -->
            <div class="sm-wizard-step" id="step-content-2" style="display: none;">
                <div style="padding: 40px;">
                    <?php if(!is_user_logged_in()): ?>
                        <div style="background: #fffbeb; border: 1px solid #feebc8; border-radius: 15px; padding: 20px; margin-bottom: 30px; display: flex; align-items: center; gap: 15px;">
                            <span class="dashicons dashicons-warning" style="color: #d69e2e; font-size: 30px; width: 30px; height: 30px;"></span>
                            <div>
                                <strong style="color: #975a16; display: block; margin-bottom: 5px;">تنبيه: يجب تسجيل الدخول للمتابعة كعضو مسجل.</strong>
                                <p style="margin: 0; font-size: 12px; color: #b7791f;">يمكنك المتابعة كزائر عن طريق تعبئة البيانات أدناه، ولكن لن يتم ربط البحث بملفك الشخصي.</p>
                            </div>
                            <a href="<?php echo home_url('/sm-login'); ?>" class="sm-btn" style="width: auto; padding: 0 20px; height: 40px; font-size: 12px; background: #d69e2e;">تسجيل الدخول</a>
                        </div>
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div class="sm-form-group" style="grid-column: span 2;">
                            <div style="background: #f8fafc; padding: 25px; border-radius: 20px; border: 1px solid #edf2f7; position: relative;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                    <h5 style="margin: 0; font-weight: 800; color: var(--sm-dark-color);">بيانات المؤلفين</h5>
                                    <label style="font-size: 12px; color: var(--sm-primary-color); font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                        <input type="checkbox" id="not-the-author" onchange="smToggleMainAuthor(this)"> لست أنا المؤلف الرئيسي
                                    </label>
                                </div>
                                <div id="author-list-container">
                                    <div class="author-item" style="display: flex; gap: 10px; margin-bottom: 10px;">
                                        <input type="text" name="author_list[]" class="sm-input author-input" required <?php echo is_user_logged_in() ? 'readonly' : ''; ?> value="<?php echo is_user_logged_in() ? wp_get_current_user()->display_name : ''; ?>" placeholder="اسم المؤلف الكامل (ثلاثي على الأقل)..." style="height: 45px;">
                                        <button type="button" class="sm-btn sm-btn-outline" style="width: 45px; padding: 0; flex-shrink: 0; border-color: #eee; cursor: default;" disabled><span class="dashicons dashicons-admin-users"></span></button>
                                    </div>
                                </div>
                                <button type="button" onclick="smAddAuthorField()" class="sm-btn sm-btn-outline" style="width: 100%; height: 40px; font-size: 12px; border-radius: 10px; margin-top: 5px; border-style: dashed;">+ إضافة مؤلف مشارك</button>
                            </div>
                        </div>

                        <?php if(!is_user_logged_in()): ?>
                            <div class="sm-form-group"><input type="email" name="guest_email" class="sm-input" required placeholder="البريد الإلكتروني للتواصل..." style="height: 48px;"></div>
                            <div class="sm-form-group"><input type="text" name="guest_phone" class="sm-input" required placeholder="رقم الهاتف..." style="height: 48px;"></div>
                            <div class="sm-form-group" style="grid-column: span 2;"><input type="text" name="guest_country" class="sm-input" required placeholder="الدولة..." style="height: 48px;"></div>
                        <?php endif; ?>

                        <div class="sm-form-group">
                            <select name="university" class="sm-select" required style="height: 48px;">
                                <option value="" disabled selected>الجامعة / المؤسسة العلمية...</option>
                                <?php foreach(SM_Settings::get_universities() as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                            </select>
                        </div>
                        <div class="sm-form-group">
                            <select name="specialization" class="sm-select" required style="height: 48px;">
                                <option value="" disabled selected>التخصص الدقيق...</option>
                                <?php foreach(SM_Settings::get_specializations() as $k => $v) echo "<option value='$k'>$v</option>"; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Research Details -->
            <div class="sm-wizard-step" id="step-content-3" style="display: none;">
                <div style="padding: 40px;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
                        <div class="sm-form-group" style="grid-column: span 2;">
                            <input type="text" name="title" id="research_title_input" class="sm-input" required placeholder="عنوان البحث / الدراسة (30 حرفاً على الأقل)..." style="font-weight: 700; height: 50px;">
                        </div>
                        <div class="sm-form-group">
                            <select name="research_type" class="sm-select" required style="height: 48px;">
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
                            <input type="number" name="publication_year" class="sm-input" placeholder="سنة النشر..." value="<?php echo date('Y'); ?>" style="height: 48px;">
                        </div>
                        <div class="sm-form-group"><input type="text" name="methodology" class="sm-input" placeholder="منهجية البحث (مثلاً: وصفي، تجريبي)..." style="height: 48px;"></div>
                        <div class="sm-form-group"><input type="text" name="sample_size" class="sm-input" placeholder="عينة الدراسة..." style="height: 48px;"></div>
                        <div class="sm-form-group"><input type="text" name="doi" class="sm-input" placeholder="DOI (معرف الكائن الرقمي) إن وجد..." style="height: 48px;"></div>
                        <div class="sm-form-group"><input type="text" name="supervisor" class="sm-input" placeholder="اسم المشرف (للرسائل العلمية)..." style="height: 48px;"></div>
                    </div>

                    <div class="sm-form-group" style="margin-bottom: 25px;">
                        <textarea name="abstract" id="research_abstract_input" class="sm-textarea" rows="8" required placeholder="الملخص العلمي (Abstract) - (500 حرف على الأقل)..." style="border-radius: 15px; font-size: 14px; line-height: 1.8;"></textarea>
                    </div>

                    <!-- Smart Keywords System -->
                    <div style="background: #f8fafc; padding: 25px; border-radius: 20px; border: 1px solid #edf2f7;">
                        <h5 style="margin: 0 0 15px 0; font-weight: 800; color: var(--sm-dark-color); font-size: 14px;">الكلمات المفتاحية الذكية (Smart Keywords)</h5>
                        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                            <input type="text" id="keyword-manual-input" class="sm-input" placeholder="أضف كلمة مفتاحية واضغط Enter..." style="height: 45px; border-radius: 12px;">
                            <button type="button" onclick="smExtractKeywordsFromText()" class="sm-btn sm-btn-outline" style="width: auto; padding: 0 15px; height: 45px; border-radius: 12px; font-size: 11px; border-color: #cbd5e0; background: #fff;">
                                <span class="dashicons dashicons-image-filter" style="margin-left: 5px;"></span> اقتراح ذكي
                            </button>
                        </div>
                        <div id="keywords-chips-container" style="display: flex; flex-wrap: wrap; gap: 10px;">
                            <!-- Keyword chips will appear here -->
                        </div>
                        <input type="hidden" name="keywords" id="final-keywords-input">
                    </div>
                </div>
            </div>

            <!-- STEP 4: File Upload -->
            <div class="sm-wizard-step" id="step-content-4" style="display: none;">
                <div style="padding: 60px 40px;">
                    <div id="sm-drag-drop-zone" style="border: 3px dashed #cbd5e0; border-radius: 30px; padding: 80px 40px; text-align: center; transition: 0.3s; background: #fcfcfc; cursor: pointer;">
                        <div style="width: 80px; height: 80px; background: rgba(246, 48, 73, 0.08); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 25px;">
                            <span class="dashicons dashicons-upload" style="font-size: 40px; width: 40px; height: 40px; color: var(--sm-primary-color);"></span>
                        </div>
                        <h3 style="font-weight: 900; margin: 0 0 10px 0; color: var(--sm-dark-color);">اسحب وأفلت ملف البحث هنا</h3>
                        <p style="color: #64748b; font-size: 14px; margin-bottom: 25px;">أو انقر لاختيار الملف من جهازك (بصيغة PDF فقط، حد أقصى 10 ميجابايت)</p>

                        <input type="file" name="research_file" id="research-file-hidden" accept=".pdf" style="display: none;">
                        <div id="selected-file-name" style="display: none; background: #fff; border: 1px solid var(--sm-primary-color); padding: 12px 25px; border-radius: 12px; display: inline-flex; align-items: center; gap: 10px; color: var(--sm-primary-color); font-weight: 700;">
                            <span class="dashicons dashicons-pdf"></span>
                            <span id="file-label">لم يتم اختيار ملف</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 5: Review & Confirmation -->
            <div class="sm-wizard-step" id="step-content-5" style="display: none;">
                <div style="padding: 40px;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 24px; padding: 35px;">
                        <h4 style="margin: 0 0 25px 0; font-weight: 900; border-bottom: 2px solid #edf2f7; padding-bottom: 15px; color: var(--sm-dark-color);">مراجعة نهائية للبيانات</h4>

                        <div id="submission-preview-area" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; font-size: 14px;">
                            <!-- Preview will be populated via JS -->
                        </div>

                        <div style="margin-top: 40px; padding-top: 25px; border-top: 2px solid #edf2f7;">
                            <label style="display: flex; align-items: center; gap: 15px; cursor: pointer;">
                                <input type="checkbox" id="policy-agree-final" style="width: 20px; height: 20px; accent-color: var(--sm-primary-color);">
                                <span style="font-weight: 800; color: var(--sm-dark-color);">أؤكد موافقتي النهائية على اتفاقية النشر وصحة كافة البيانات المدخلة.</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="sm-wizard-footer" style="padding: 30px 40px; background: #f8fafc; border-top: 1px solid #eee; border-radius: 0 0 30px 30px; display: flex; justify-content: space-between; align-items: center;">
                <button type="button" id="prev-step-btn" onclick="smWizardPrev()" class="sm-btn sm-btn-outline" style="width: auto; padding: 0 30px; height: 50px; visibility: hidden;">الخطوة السابقة</button>
                <div id="step-indicators-mini" style="font-size: 13px; font-weight: 800; color: #94a3b8;">خطوة <span id="current-step-num">1</span> من 5</div>
                <button type="button" id="next-step-btn" onclick="smWizardNext()" class="sm-btn" style="width: auto; padding: 0 45px; height: 50px; font-weight: 800;">المتابعة للخطوة التالية</button>
                <button type="submit" id="submit-research-btn" class="sm-btn" style="width: auto; padding: 0 45px; height: 50px; font-weight: 900; display: none; background: #38a169;">تأكيد وإرسال المادة العلمية</button>
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
let currentWizardStep = 1;
const totalSteps = 5;

function updateWizardUI() {
    // Update Stepper
    document.querySelectorAll('.sm-step-item').forEach(item => {
        const stepNum = parseInt(item.dataset.step);
        item.classList.remove('active', 'completed');
        if (stepNum === currentWizardStep) item.classList.add('active');
        if (stepNum < currentWizardStep) item.classList.add('completed');
    });

    // Update Steps Visibility
    document.querySelectorAll('.sm-wizard-step').forEach(step => step.style.display = 'none');
    document.getElementById('step-content-' + currentWizardStep).style.display = 'block';

    // Update Buttons
    document.getElementById('current-step-num').innerText = currentWizardStep;
    document.getElementById('prev-step-btn').style.visibility = (currentWizardStep === 1) ? 'hidden' : 'visible';

    if (currentWizardStep === totalSteps) {
        document.getElementById('next-step-btn').style.display = 'none';
        document.getElementById('submit-research-btn').style.display = 'block';
        smPopulateFinalReview();
    } else {
        document.getElementById('next-step-btn').style.display = 'block';
        document.getElementById('submit-research-btn').style.display = 'none';
        document.getElementById('next-step-btn').innerText = (currentWizardStep === 1) ? 'أوافق، البدء في تعبئة البيانات' : 'المتابعة للخطوة التالية';
    }
}

function smWizardNext() {
    // Validation
    if (currentWizardStep === 1 && !document.getElementById('policy-agree-1').checked) {
        alert('يجب الموافقة على الاتفاقية العلمية للمتابعة.');
        return;
    }

    if (currentWizardStep === 3) {
        const title = document.getElementById('research_title_input').value;
        const abstract = document.getElementById('research_abstract_input').value;
        if (title.length < 30) { alert('عنوان البحث يجب أن يكون 30 حرفاً على الأقل.'); return; }
        if (abstract.length < 500) { alert('الملخص العلمي يجب أن يكون 500 حرفاً على الأقل.'); return; }
    }

    if (currentWizardStep === 4 && !document.getElementById('research-file-hidden').files.length) {
        alert('يرجى رفع ملف البحث بصيغة PDF للمتابعة.');
        return;
    }

    if (currentWizardStep < totalSteps) {
        currentWizardStep++;
        updateWizardUI();
    }
}

function smWizardPrev() {
    if (currentWizardStep > 1) {
        currentWizardStep--;
        updateWizardUI();
    }
}

function smPopulateFinalReview() {
    const form = document.getElementById('sm-research-submission-form');
    const area = document.getElementById('submission-preview-area');
    const fd = new FormData(form);

    let html = '';
    const labels = {
        'title': 'عنوان البحث',
        'research_type': 'نوع المادة',
        'publication_year': 'سنة النشر',
        'university': 'الجامعة',
        'specialization': 'التخصص',
        'methodology': 'المنهجية',
        'guest_email': 'البريد الإلكتروني',
        'doi': 'معرف DOI'
    };

    for (let [key, value] of fd.entries()) {
        if (labels[key] && value) {
            html += `<div><label style="color:#94a3b8; font-size:11px; display:block;">${labels[key]}:</label><strong>${value}</strong></div>`;
        }
    }
    area.innerHTML = html;
}

// Drag & Drop Handling
const dropZone = document.getElementById('sm-drag-drop-zone');
const fileInput = document.getElementById('research-file-hidden');

dropZone.onclick = () => fileInput.click();
fileInput.onchange = () => smHandleFileSelect(fileInput.files[0]);

dropZone.ondragover = (e) => { e.preventDefault(); dropZone.style.borderColor = 'var(--sm-primary-color)'; };
dropZone.ondragleave = () => { dropZone.style.borderColor = '#cbd5e0'; };
dropZone.ondrop = (e) => {
    e.preventDefault();
    dropZone.style.borderColor = '#cbd5e0';
    if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files;
        smHandleFileSelect(e.dataTransfer.files[0]);
    }
};

function smHandleFileSelect(file) {
    if (!file) return;
    if (file.type !== 'application/pdf') { alert('عذراً، يجب أن يكون الملف بصيغة PDF فقط.'); fileInput.value = ''; return; }
    document.getElementById('selected-file-name').style.display = 'inline-flex';
    document.getElementById('file-label').innerText = file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
}

// Keywords System
let selectedKeywords = [];
function smAddKeyword(word) {
    word = word.trim();
    if (!word || selectedKeywords.includes(word)) return;
    selectedKeywords.push(word);
    smRenderKeywordChips();
}

function smRenderKeywordChips() {
    const container = document.getElementById('keywords-chips-container');
    container.innerHTML = selectedKeywords.map((w, idx) => `
        <div class="sm-keyword-chip">
            ${w} <span onclick="smRemoveKeyword(${idx})">&times;</span>
        </div>
    `).join('');
    document.getElementById('final-keywords-input').value = selectedKeywords.join(', ');
}

function smRemoveKeyword(idx) {
    selectedKeywords.splice(idx, 1);
    smRenderKeywordChips();
}

document.getElementById('keyword-manual-input').onkeydown = function(e) {
    if (e.key === 'Enter') { e.preventDefault(); smAddKeyword(this.value); this.value = ''; }
};

function smExtractKeywordsFromText() {
    const text = document.getElementById('research_title_input').value + ' ' + document.getElementById('research_abstract_input').value;
    // Simple extraction logic for demo/utility
    const commonWords = ['من', 'في', 'على', 'إلى', 'عن', 'هذا', 'كان', 'تم', 'دراسة', 'بحث', 'تأثير', 'دور'];
    const words = text.split(/[\s,،.]+/);
    const uniqueWords = [...new Set(words.filter(w => w.length > 4 && !commonWords.includes(w)))];
    uniqueWords.slice(0, 5).forEach(smAddKeyword);
}

// Reuse existing logic from previous implementation
window.smOpenResearchSubmission = function() {
    document.getElementById('sm-research-submit-modal').style.display = 'flex';
    currentWizardStep = 1;
    updateWizardUI();
};

window.smRefreshResearchList = function() {
    const container = document.getElementById('sm-research-list-container');
    const form = document.getElementById('sm-research-filter-form');
    const fd = new FormData(form);
    const params = new URLSearchParams(fd).toString();
    fetch(ajaxurl + '?action=sm_get_researches_html&' + params).then(r => r.json()).then(res => { if(res.success) container.innerHTML = res.data.html; });
};

window.smToggleMainAuthor = function(cb) {
    const mainInput = document.querySelector('#author-list-container .author-item:first-child .author-input');
    if (cb.checked) { mainInput.readOnly = false; mainInput.value = ''; mainInput.placeholder = 'اسم المؤلف الرئيسي الكامل (ثلاثي)...'; }
    else { mainInput.readOnly = true; mainInput.value = '<?php echo is_user_logged_in() ? wp_get_current_user()->display_name : ""; ?>'; }
};

window.smAddAuthorField = function() {
    const container = document.getElementById('author-list-container');
    const div = document.createElement('div');
    div.className = 'author-item';
    div.style.cssText = 'display: flex; gap: 10px; margin-bottom: 10px; animation: smFadeIn 0.3s ease;';
    div.innerHTML = `
        <input type="text" name="author_list[]" class="sm-input author-input" required placeholder="اسم المؤلف المشارك الكامل..." style="height: 45px;">
        <button type="button" onclick="this.parentElement.remove()" class="sm-btn sm-btn-outline" style="width: 45px; padding: 0; flex-shrink: 0; border-color: #feb2b2; color: #e53e3e !important;"><span class="dashicons dashicons-trash"></span></button>
    `;
    container.appendChild(div);
};

document.getElementById('sm-research-submission-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    if (!document.getElementById('policy-agree-final').checked) { alert('يجب تأكيد الموافقة على الاتفاقية في الخطوة الأخيرة.'); return; }

    const btn = document.getElementById('submit-research-btn');
    btn.disabled = true; btn.innerText = 'جاري الإرسال النهائي...';

    const fd = new FormData(this);
    fd.append('action', 'sm_submit_research');

    fetch(ajaxurl + '?action=sm_submit_research', { method: 'POST', body: fd }).then(r => r.json()).then(res => {
        if(res.success) { alert(res.data); location.reload(); }
        else { alert(res.data.message || 'خطأ غير معروف'); btn.disabled = false; btn.innerText = 'تأكيد وإرسال المادة العلمية'; }
    });
});

window.smToggleResearchAbstract = function(id) {
    const content = document.getElementById('research-abstract-' + id);
    const icon = document.getElementById('research-icon-' + id);
    const isHidden = content.style.display === 'none';
    content.style.display = isHidden ? 'block' : 'none';
    icon.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
};

window.smPreviewResearch = function(id, url, title) {
    document.getElementById('preview-title').innerText = title;
    document.getElementById('preview-frame-container').innerHTML = `<iframe src="${url}" width="100%" height="100%" style="border:none;"></iframe>`;
    document.getElementById('sm-research-preview-modal').style.display = 'flex';
    smRecordInteraction(id, 'view');
};

window.smToggleFavoritesOnly = function(btn) {
    const form = document.getElementById('sm-research-filter-form');
    const input = form.querySelector('input[name="show_favorites"]');
    const icon = btn.querySelector('.dashicons');
    if (input.value === "1") { input.value = "0"; icon.style.color = "#94a3b8"; btn.style.borderColor = "#e2e8f0"; }
    else { input.value = "1"; icon.style.color = "#d69e2e"; btn.style.borderColor = "#d69e2e"; }
    smRefreshResearchList();
};

document.addEventListener('DOMContentLoaded', () => smRefreshResearchList());
</script>

<style>
.sm-wizard-stepper { counter-reset: step; }
.sm-step-item { display: flex; flex-direction: column; align-items: center; gap: 10px; flex: 1; position: relative; }
.sm-step-item span { width: 35px; height: 35px; border-radius: 50%; background: #eee; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-weight: 800; border: 2px solid #fff; box-shadow: 0 0 0 2px #eee; z-index: 2; transition: 0.3s; }
.sm-step-item label { font-size: 11px; font-weight: 800; color: #94a3b8; }
.sm-step-item.active span { background: var(--sm-primary-color); color: #fff; box-shadow: 0 0 0 2px var(--sm-primary-color); }
.sm-step-item.active label { color: var(--sm-primary-color); }
.sm-step-item.completed span { background: #38a169; color: #fff; box-shadow: 0 0 0 2px #38a169; }
.sm-step-item:not(:last-child)::after { content: ''; position: absolute; top: 17px; left: -50%; width: 100%; height: 2px; background: #eee; z-index: 1; }
.sm-step-item.completed::after { background: #38a169; }

.sm-keyword-chip { background: #EBF8FF; color: #2B6CB0; padding: 6px 15px; border-radius: 20px; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 8px; border: 1px solid #BEE3F8; }
.sm-keyword-chip span { cursor: pointer; color: #A0AEC0; font-size: 16px; }
.sm-keyword-chip span:hover { color: #E53E3E; }

.sm-research-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 35px; margin-bottom: 25px; transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.02); position: relative; overflow: hidden; }
.sm-research-card:hover { border-color: var(--sm-primary-color); transform: translateY(-3px); box-shadow: 0 15px 40px rgba(0,0,0,0.06); }
.sm-research-card.featured { border-right: 6px solid #d69e2e; }
.sm-research-card.featured::after { content: "متميز"; position: absolute; top: 15px; left: -30px; background: #d69e2e; color: #fff; padding: 5px 35px; transform: rotate(-45deg); font-size: 10px; font-weight: 800; }

@media (max-width: 992px) {
    .sm-research-header-new { flex-direction: column; text-align: center; gap: 25px; }
    .sm-research-layout { grid-template-columns: 1fr !important; }
    .sm-wizard-stepper { padding: 30px 20px; }
    .sm-step-item label { display: none; }
}
</style>
