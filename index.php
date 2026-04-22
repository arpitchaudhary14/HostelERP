<?php include("header.php"); ?>
<section class="hero-modern">
    <div class="hero-shapes">
        <div class="hero-shape"></div>
        <div class="hero-shape"></div>
        <div class="hero-shape"></div>
    </div>
    <div class="container" style="position:relative; z-index:1;">
        <p class="mb-2" style="font-size:0.9rem; color:var(--accent-primary-light); letter-spacing:2px; text-transform:uppercase; animation: heroTextIn 0.8s ease-out;">
            🏠 Smart Hostel Management
        </p>
        <h1 style="font-size:3.6rem;">HostelERP</h1>
        <p class="lead mt-3" style="max-width:600px;">
            A complete digital platform for hostel administration — manage rooms, attendance, fees, complaints, and more with ease.
        </p>
        <div class="mt-4 hero-search" id="heroSearchWrap">
            <input
                type="text"
                id="globalSearch"
                placeholder="🔍 Search pages, features, rooms, complaints..."
                autocomplete="off"
            >
            <button type="button" class="hero-search-btn" id="searchBtn" title="Search" aria-label="Search">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
            <div class="hero-search-dropdown" id="searchDropdown"></div>
        </div>
        <div class="mt-4 hero-btns" style="display:flex; gap:16px; justify-content:center; flex-wrap:wrap;">
            <a href="login.php" class="btn-hero-primary">
                ✨ Get Started
            </a>
            <a href="register.php" class="btn-hero-outline">
                Create Account →
            </a>
        </div>
    </div>
</section>
<section style="padding:80px 0;">
    <div class="container">
        <h2 class="text-center mb-2 section-heading">
            Powerful Features
        </h2>
        <p class="text-center mb-5 section-subtext">Everything you need to manage your hostel efficiently</p>
        <div class="row g-4 reveal-stagger">
            <?php $link_prefix = isset($_SESSION['user_id']) ? '' : 'login.php'; ?>
            <div class="col-md-4 mb-4 reveal">
                <a href="<?= $link_prefix ?: 'student/my_room.php' ?>" style="text-decoration:none;">
                <div class="feature-card">
                    <div class="feature-icon-wrap">🏠</div>
                    <h5>Room Management</h5>
                    <p>Assign rooms, track occupancy, and manage hostel rooms efficiently.</p>
                </div>
                </a>
            </div>
            <div class="col-md-4 mb-4 reveal">
                <a href="<?= $link_prefix ?: 'dashboard.php' ?>" style="text-decoration:none;">
                <div class="feature-card">
                    <div class="feature-icon-wrap">📢</div>
                    <h5>Notices & Alerts</h5>
                    <p>Post announcements and keep students informed instantly with push alerts.</p>
                </div>
                </a>
            </div>
            <div class="col-md-4 mb-4 reveal">
                <a href="<?= $link_prefix ?: 'student/submit_complaint.php' ?>" style="text-decoration:none;">
                <div class="feature-card">
                    <div class="feature-icon-wrap">📝</div>
                    <h5>Complaint System</h5>
                    <p>Students can submit complaints and track their resolution in real time.</p>
                </div>
                </a>
            </div>
            <div class="col-md-4 mb-4 reveal">
                <a href="<?= $link_prefix ?: 'student/leave_request.php' ?>" style="text-decoration:none;">
                <div class="feature-card">
                    <div class="feature-icon-wrap">📅</div>
                    <h5>Leave Requests</h5>
                    <p>Submit and approve leave requests digitally with instant notifications.</p>
                </div>
                </a>
            </div>
            <div class="col-md-4 mb-4 reveal">
                <a href="<?= $link_prefix ?: 'student/fees.php' ?>" style="text-decoration:none;">
                <div class="feature-card">
                    <div class="feature-icon-wrap">💰</div>
                    <h5>Fees Management</h5>
                    <p>Track hostel fees, payment records, and generate receipts easily.</p>
                </div>
                </a>
            </div>
            <div class="col-md-4 mb-4 reveal">
                <a href="<?= $link_prefix ?: 'dashboard.php' ?>" style="text-decoration:none;">
                <div class="feature-card">
                    <div class="feature-icon-wrap">🤖</div>
                    <h5>AI Assistant (LEON)</h5>
                    <p>Automate tasks, apply for leaves, and get instant answers using natural language.</p>
                </div>
                </a>
            </div>
        </div>
    </div>
</section>
<section class="steps-section about-section reveal">
    <div class="container">
        <h2 class="text-center mb-2 section-heading">How It Works</h2>
        <p class="text-center mb-5 section-subtext">Get started in three simple steps</p>
        <div class="row">
            <div class="col-md-4 step-connector reveal">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h5>Create Account</h5>
                    <p>Register as a student, warden, or admin with your institute credentials.</p>
                </div>
            </div>
            <div class="col-md-4 step-connector reveal">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h5>Access Dashboard</h5>
                    <p>Login to your personalized dashboard with role-based features and analytics.</p>
                </div>
            </div>
            <div class="col-md-4 step-connector reveal">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h5>Manage Everything</h5>
                    <p>Handle rooms, complaints, attendance, fees, and notices all in one place.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="about-section reveal">
    <div class="container">
        <h2 class="text-center mb-4">
            About HostelERP
        </h2>
        <p class="text-center">
            HostelERP is a modern hostel management platform designed to simplify
            hostel administration. It enables institutions to manage rooms, student
            records, complaints, attendance, fees, and notices through a centralized
            digital system. Built with security and scalability in mind, it serves
            students, wardens, and administrators with role-based access control.
        </p>
    </div>
</section>
<section style="padding:80px 0;" class="reveal">
    <div class="container">
        <h2 class="text-center mb-5 section-heading">
            Frequently Asked Questions
        </h2>
        <div class="accordion accordion-glass" id="faqAccordion" style="max-width:700px; margin:0 auto;">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        What is HostelERP?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show">
                    <div class="accordion-body">
                        HostelERP is a comprehensive hostel management system designed to digitize and streamline hostel operations including room allocation, attendance, fees, complaints, and leave management.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq2">
                        Who can use HostelERP?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        HostelERP supports three roles: Students can manage their profiles, request leaves, and track complaints. Wardens can manage students, rooms, and approve leaves. Administrators have full system access including reporting and settings.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq3">
                        Is HostelERP secure?
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        Yes, HostelERP implements password hashing, session management, role-based access control, Two-Factor Authentication (2FA), reCAPTCHA protection, and OAuth2 authentication (Google & Microsoft) to ensure maximum data security.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq4">
                        Can I access HostelERP on mobile?
                    </button>
                </h2>
                <div id="faq4" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        Absolutely! HostelERP is built with a fully responsive design that works beautifully on smartphones, tablets, and desktops.
                    </div>
                </div>
        </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#faq5">
                        Does HostelERP have an AI Assistant?
                    </button>
                </h2>
                <div id="faq5" class="accordion-collapse collapse">
                    <div class="accordion-body">
                        Yes! HostelERP comes with a smart AI assistant named LEON. LEON can instantly answer questions about hostel policies, fetch your personal data, and even perform actions like applying for leaves directly through chat.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="cta-section reveal">
    <div class="container">
        <h2 class="mb-3">Ready to Get Started?</h2>
        <p class="mb-4">Join thousands of students and administrators using HostelERP today.</p>
        <a href="register.php" class="btn-cta">
            🚀 Create Free Account
        </a>
    </div>
</section>
<div id="cookieBanner" class="cookie-glass">
    <div class="container d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <strong style="color:#fff;">We care about your privacy</strong><br>
            <span style="color:var(--text-muted-light); font-size:0.9rem;">This website uses cookies to improve functionality and analytics.</span>
        </div>
        <div class="mt-2" style="display:flex; gap:8px; flex-wrap:wrap;">
            <button class="btn-gradient" onclick="acceptAllCookies()" style="width:auto; padding:8px 20px; font-size:0.85rem;">
                Accept All
            </button>
            <button class="btn btn-outline-light btn-sm" onclick="rejectCookies()" style="border-radius:var(--radius-sm);">
                Reject All
            </button>
            <button class="btn btn-outline-info btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#cookieSettingsModal"
                style="border-radius:var(--radius-sm);">
                Cookie Settings
            </button>
        </div>
    </div>
</div>
<div class="modal fade modal-glass" id="cookieSettingsModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Cookie Settings</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<div class="mb-3">
<strong>Essential Cookies</strong>
<p style="color:var(--text-muted-light);">
Required for login and security.
</p>
<div class="form-check form-switch">
<input class="form-check-input" type="checkbox" checked disabled>
<label class="form-check-label" style="color:var(--text-muted-light);">Always On</label>
</div>
</div>
<div class="mb-3">
<strong>Analytics Cookies</strong>
<p style="color:var(--text-muted-light);">
Help us understand website traffic.
</p>
<div class="form-check form-switch">
<input class="form-check-input" type="checkbox" id="analyticsCookies">
</div>
</div>
<div class="mb-3">
<strong>Advertising Cookies</strong>
<p style="color:var(--text-muted-light);">
Used for marketing and promotions.
</p>
<div class="form-check form-switch">
<input class="form-check-input" type="checkbox" id="adsCookies">
</div>
</div>
</div>
<div class="modal-footer">
<button class="btn-gradient" onclick="saveCookieSettings()" style="width:auto; padding:8px 20px;">
Confirm
</button>
<button class="btn btn-sm" onclick="acceptAllCookies()" style="background:var(--accent-secondary); color:#000; border:none; border-radius:var(--radius-sm); padding:8px 16px;">
Accept All
</button>
<button class="btn btn-outline-light btn-sm" onclick="rejectCookies()" style="border-radius:var(--radius-sm);">
Reject All
</button>
</div>
</div>
</div>
</div>
<script>
(function() {
    if(!localStorage.getItem("cookieChoice")){
        setTimeout(function() {
            document.getElementById("cookieBanner").classList.add("show");
        }, 800);
    }
})();
function acceptAllCookies(){
    localStorage.setItem("cookieChoice","all");
    document.getElementById("cookieBanner").classList.remove("show");
}
function rejectCookies(){
    localStorage.setItem("cookieChoice","rejected");
    document.getElementById("cookieBanner").classList.remove("show");
}
function saveCookieSettings(){
    let analytics=document.getElementById("analyticsCookies").checked;
    let ads=document.getElementById("adsCookies").checked;
    let settings={
        analytics:analytics,
        ads:ads
    };
    localStorage.setItem("cookieChoice",JSON.stringify(settings));
    document.getElementById("cookieBanner").classList.remove("show");
    let modal=bootstrap.Modal.getInstance(document.getElementById("cookieSettingsModal"));
    modal.hide();
}
const searchRoutes = [
    { keywords: ["login", "sign in", "signin"], label: "Login", desc: "Sign in to your account", icon: "🔑", url: "login.php" },
    { keywords: ["register", "sign up", "signup", "create account"], label: "Register", desc: "Create a new account", icon: "📝", url: "register.php" },
    { keywords: ["contact", "support", "help"], label: "Contact Us", desc: "Get in touch with support", icon: "📞", url: "contact.php" },
    { keywords: ["leave", "leave request", "apply leave"], label: "Leave Request", desc: "Apply for hostel leave", icon: "📅", url: "student/leave_request.php" },
    { keywords: ["complaint", "submit complaint", "grievance", "issue"], label: "Submit Complaint", desc: "File a new complaint", icon: "📋", url: "student/submit_complaint.php" },
    { keywords: ["attendance", "present", "absent"], label: "Attendance", desc: "View attendance records", icon: "✅", url: "student/attendance.php" },
    { keywords: ["fees", "payment", "hostel fees", "pay"], label: "Fees", desc: "View and manage hostel fees", icon: "💰", url: "student/fees.php" },
    { keywords: ["profile", "my profile", "account", "settings"], label: "Profile", desc: "View and edit your profile", icon: "👤", url: "profile.php" },
    { keywords: ["room", "my room", "room details", "hostel room"], label: "My Room", desc: "View room assignment details", icon: "🏠", url: "student/my_room.php" },
    { keywords: ["dashboard", "home", "overview", "panel"], label: "Dashboard", desc: "Your main dashboard", icon: "📊", url: "dashboard.php" },
    { keywords: ["notice", "notices", "announcement", "alert"], label: "Notices", desc: "View hostel notices & alerts", icon: "📢", url: "student/notices.php" },
    { keywords: ["feedback", "review", "rate"], label: "Submit Feedback", desc: "Share your feedback", icon: "💬", url: "student/feedback.php" },
    { keywords: ["visitor", "visitors", "guest"], label: "Visitors", desc: "View visitor information", icon: "🧑‍🤝‍🧑", url: "student/visitors.php" },
    { keywords: ["parcel", "parcels", "delivery", "package"], label: "Parcels", desc: "Track your parcels", icon: "📦", url: "student/parcels.php" },
    { keywords: ["mess", "mess menu", "food", "canteen", "menu"], label: "Mess Menu", desc: "View weekly mess menu", icon: "🍽️", url: "student/mess_menu.php" },
    { keywords: ["notification", "notifications", "bell"], label: "Notifications", desc: "View your notifications", icon: "🔔", url: "student/notifications.php" },
    { keywords: ["document", "documents", "upload", "certificate"], label: "Documents", desc: "Manage your documents", icon: "📄", url: "student/documents.php" },
    { keywords: ["forgot", "forgot password", "reset password", "password"], label: "Forgot Password", desc: "Reset your password", icon: "🔒", url: "forgot_password.php" },
    { keywords: ["chatbot", "leon", "ai", "chat", "bot", "assistant"], label: "LEON Chatbot", desc: "Ask the AI assistant", icon: "🤖", url: "chatbot_ui.php" }
];
const searchInput  = document.getElementById("globalSearch");
const searchDrop   = document.getElementById("searchDropdown");
const searchBtn    = document.getElementById("searchBtn");
let activeIdx = -1;
function filterRoutes(q) {
    if (!q) return [];
    return searchRoutes.filter(r =>
        r.keywords.some(k => k.includes(q)) ||
        r.label.toLowerCase().includes(q) ||
        r.desc.toLowerCase().includes(q)
    );
}
function renderDropdown(results, q) {
    if (!q) { searchDrop.innerHTML = ""; searchDrop.classList.remove("open"); activeIdx = -1; return; }
    if (results.length === 0) {
        searchDrop.innerHTML = '<div class="search-no-result">No results for "<strong>' + q.replace(/</g,"&lt;") + '</strong>"</div>';
        searchDrop.classList.add("open");
        activeIdx = -1;
        return;
    }
    searchDrop.innerHTML = results.map((r, i) =>
        '<a href="' + r.url + '" class="search-item' + (i === activeIdx ? ' active' : '') + '" data-idx="' + i + '">' +
            '<span class="search-item-icon">' + r.icon + '</span>' +
            '<span class="search-item-text"><span class="search-item-label">' + r.label + '</span><span class="search-item-desc">' + r.desc + '</span></span>' +
            '<span class="search-item-arrow">→</span>' +
        '</a>'
    ).join("");
    searchDrop.classList.add("open");
}
function doSearch() {
    let q = searchInput.value.toLowerCase().trim();
    let results = filterRoutes(q);
    activeIdx = -1;
    renderDropdown(results, q);
}
searchInput.addEventListener("input", doSearch);
searchInput.addEventListener("focus", doSearch);
searchInput.addEventListener("keydown", function(e) {
    let items = searchDrop.querySelectorAll(".search-item");
    if (items.length === 0 && e.key === "Enter") {
        doSearch();
        return;
    }
    if (e.key === "ArrowDown") {
        e.preventDefault();
        activeIdx = (activeIdx + 1) % items.length;
        items.forEach((it, i) => it.classList.toggle("active", i === activeIdx));
        items[activeIdx]?.scrollIntoView({ block: "nearest" });
    } else if (e.key === "ArrowUp") {
        e.preventDefault();
        activeIdx = (activeIdx - 1 + items.length) % items.length;
        items.forEach((it, i) => it.classList.toggle("active", i === activeIdx));
        items[activeIdx]?.scrollIntoView({ block: "nearest" });
    } else if (e.key === "Enter") {
        e.preventDefault();
        if (activeIdx >= 0 && items[activeIdx]) {
            window.location.href = items[activeIdx].getAttribute("href");
        } else if (items.length > 0) {
            window.location.href = items[0].getAttribute("href");
        }
    } else if (e.key === "Escape") {
        searchDrop.innerHTML = "";
        searchDrop.classList.remove("open");
        activeIdx = -1;
    }
});
searchBtn.addEventListener("click", function() {
    let q = searchInput.value.toLowerCase().trim();
    let results = filterRoutes(q);
    if (results.length > 0) {
        window.location.href = results[0].url;
    } else {
        doSearch();
        searchInput.focus();
    }
});
document.addEventListener("click", function(e) {
    if (!document.getElementById("heroSearchWrap").contains(e.target)) {
        searchDrop.innerHTML = "";
        searchDrop.classList.remove("open");
        activeIdx = -1;
    }
});
</script>
<?php include("footer.php"); ?>