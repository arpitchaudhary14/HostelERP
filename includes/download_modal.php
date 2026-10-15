<?php
$modal_base_url = (strpos($_SERVER['REQUEST_URI'] ?? '', '/WebTechProject') !== false || strpos($_SERVER['SCRIPT_NAME'] ?? '', '/WebTechProject') !== false) ? '/WebTechProject' : '';
?>
<!-- Download Modal -->
<div class="modal fade" id="downloadAppModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content glass-card-light border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
      
      <!-- Modal Header -->
      <div class="modal-header border-0 pb-0 pt-4 px-4">
        <div class="d-flex align-items-center gap-2">
          <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background: rgba(108, 99, 255, 0.15); color: var(--accent-primary, #6c63ff); width: 42px; height: 42px;">
            <i class="bi bi-laptop fs-4"></i>
          </div>
          <div>
            <div class="d-flex align-items-center gap-2">
              <h5 class="modal-title fw-bold mb-0" id="downloadModalLabel" style="color: var(--inner-heading, #1f2937);">
                HostelERP Desktop
              </h5>
              <span class="badge rounded-pill" style="background: rgba(108, 99, 255, 0.2); color: var(--accent-primary, #6c63ff); font-size: 0.75rem; font-weight: 600;">
                v2.0 Stable
              </span>
            </div>
            <small class="text-muted">Official Native Application for Students &amp; Staff</small>
          </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body p-4">

        <!-- OS / Device Auto-Detect Badge -->
        <div id="detected-os-badge" class="alert alert-info py-2 px-3 mb-3 rounded-pill text-center mx-auto" style="display: none; max-width: 480px; font-size: 0.88rem;">
          <span id="detected-os-text">Detecting your system...</span>
        </div>

        <!-- ================= Mobile Device Showcase Card (Shown only on Phones) ================= -->
        <div id="mobile-device-card" class="card border-0 shadow-sm p-4 text-center mb-3" style="display: none; background: linear-gradient(135deg, rgba(108, 99, 255, 0.08), rgba(14, 165, 233, 0.08)); border: 1.5px solid rgba(108, 99, 255, 0.25) !important; border-radius: 18px;">
          <div class="mb-2">
            <span class="fs-1 d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 64px; height: 64px; background: rgba(108, 99, 255, 0.15);">📱</span>
          </div>
          <h5 class="fw-bold mb-1" style="color: var(--inner-heading);">Mobile Device Detected (<span id="mobile-os-title">Android</span>)</h5>
          <div class="d-inline-block mx-auto mb-3">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold" style="font-size: 0.82rem;">
              <i class="bi bi-phone-fill me-1"></i> Optimized Mobile Web Experience
            </span>
          </div>
          <p class="text-muted small mb-3" style="line-height: 1.6; max-width: 480px; margin-left: auto; margin-right: auto;">
            HostelERP runs instantly on any smartphone without needing any Play Store or App Store install. You are already using the fully-featured, responsive Web Portal! The packages below (.exe, .dmg, .deb) are built specifically for PC &amp; Laptop computers.
          </p>
          <div class="d-flex flex-column gap-2 justify-content-center" style="max-width: 380px; margin: 0 auto;">
            <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">
              <i class="bi bi-check2-circle me-1"></i> Continue Using Web App
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2" id="btn-toggle-desktop" onclick="toggleDesktopOnMobile()">
              <i class="bi bi-laptop me-1"></i> <span id="toggle-desktop-text">Looking for PC / Laptop Installers? Show Options</span>
            </button>
          </div>
        </div>

        <!-- ================= Mobile Download Warning/Confirmation Box ================= -->
        <div id="mobile-download-confirm-box" class="alert alert-warning shadow-sm border-warning p-3 mb-3 text-start" style="display: none; border-radius: 14px;">
          <div class="d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-4 text-warning flex-shrink-0 mt-1"></i>
            <div class="flex-grow-1">
              <h6 class="fw-bold mb-1 text-dark">Desktop Package Notice</h6>
              <p class="small text-muted mb-2" id="mobile-confirm-message">
                You are on a mobile device. This package is built for PC / Laptops and cannot be installed directly on your phone.
              </p>
              <div class="d-flex flex-wrap gap-2">
                <a href="#" id="mobile-confirm-download-btn" class="btn btn-sm btn-warning text-dark fw-bold px-3 rounded-pill" onclick="onMobileConfirmDownload()">
                  <i class="bi bi-download me-1"></i> Download for PC Anyway
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary px-3 rounded-pill" onclick="cancelMobileDownload()">
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Custom In-Modal Alert Box (for 32-bit notice etc.) -->
        <div id="modal-alert-box" class="alert alert-warning alert-dismissible fade show mb-3" style="display: none; border-radius: 12px; font-size: 0.88rem;" role="alert">
          <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
          <span id="modal-alert-text"></span>
          <button type="button" class="btn-close" onclick="closeModalAlert()" aria-label="Close"></button>
        </div>

        <!-- ================= Desktop Options Wrapper ================= -->
        <div id="desktop-options-wrapper">

          <!-- OS Segmented Selector Tabs -->
          <div class="d-flex justify-content-center gap-2 mb-4">
            <div class="btn-group p-1 rounded-pill shadow-sm" role="group" aria-label="Select OS" style="background: rgba(125, 125, 125, 0.1); backdrop-filter: blur(8px);">
              <button type="button" class="btn btn-sm rounded-pill px-3 py-2 fw-semibold os-tab-btn" id="tab-btn-windows" onclick="selectOS('windows')">
                <i class="bi bi-windows me-1 text-primary"></i> Windows
              </button>
              <button type="button" class="btn btn-sm rounded-pill px-3 py-2 fw-semibold os-tab-btn" id="tab-btn-macos" onclick="selectOS('macos')">
                <i class="bi bi-apple me-1 text-dark"></i> macOS
              </button>
              <button type="button" class="btn btn-sm rounded-pill px-3 py-2 fw-semibold os-tab-btn" id="tab-btn-linux" onclick="selectOS('linux')">
                <i class="bi bi-ubuntu me-1 text-warning"></i> Linux
              </button>
            </div>
          </div>

          <!-- ================= Windows Container ================= -->
          <div id="options-windows" class="os-options-container" style="display: none;">
            <div class="row g-3">
              
              <!-- Standard Installer -->
              <div class="col-12">
                <div class="card border-0 shadow-sm p-3 text-start" style="background: rgba(108, 99, 255, 0.05); border-left: 4px solid var(--accent-primary, #6c63ff) !important; border-radius: 14px;">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                      <i class="bi bi-box-seam-fill text-primary fs-5"></i>
                      <h6 class="fw-bold mb-0" style="color: var(--inner-heading);">Standard Windows Installer (.exe)</h6>
                    </div>
                    <span class="badge bg-success">Recommended</span>
                  </div>
                  <p class="text-muted small mb-3">
                    Installs HostelERP with automatic updates, desktop shortcut, and protocol handler. Compatible with Windows 10 &amp; 11 (64-bit). Runs natively on Intel/AMD and via high-speed Prism emulation on Windows ARM64.
                  </p>
                  <div class="d-flex flex-wrap gap-2 align-items-center">
                    <a href="<?= $modal_base_url ?>/download.php?file=HostelERP-Setup-2.0.0.exe" class="btn btn-primary px-3 rounded-pill fw-semibold download-trigger" onclick="handleDownload(event, 'Windows Setup (x64)', this.href)">
                      <i class="bi bi-download me-1"></i> Download Setup (.exe)
                      <span class="badge bg-white text-primary ms-1" style="font-size:0.75rem;">211 MB</span>
                    </a>
                    <button type="button" class="btn btn-outline-secondary px-3 rounded-pill fw-semibold" onclick="showDiscontinuedNotice('Windows 32-bit (ia32)')">
                      <i class="bi bi-slash-circle me-1"></i> ia32 (32-bit)
                      <span class="badge bg-secondary ms-1" style="font-size:0.7rem;">Discontinued</span>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Portable Options -->
              <div class="col-12">
                <div class="card border-0 shadow-sm p-3 text-start" style="background: rgba(125, 125, 125, 0.04); border-radius: 14px;">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-folder2-open text-secondary fs-5"></i>
                    <h6 class="fw-bold mb-0" style="color: var(--inner-heading);">Portable Editions (No Setup Required)</h6>
                  </div>
                  <p class="text-muted small mb-3">Run immediately from a USB flash drive or folder without administrator privileges.</p>
                  <div class="d-flex flex-wrap gap-2">
                    <a href="<?= $modal_base_url ?>/download.php?file=HostelERP-2.0.0.exe" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-2 fw-semibold download-trigger" onclick="handleDownload(event, 'Portable .exe', this.href)">
                      <i class="bi bi-play-circle me-1"></i> Standalone App (.exe) <span class="badge bg-primary-subtle text-primary ms-1">135 MB</span>
                    </a>
                    <a href="<?= $modal_base_url ?>/download.php?file=HostelERP-2.0.0-win.zip" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-semibold download-trigger" onclick="handleDownload(event, 'Portable Zip x64', this.href)">
                      <i class="bi bi-file-earmark-zip me-1"></i> Portable Zip (x64) <span class="badge bg-secondary-subtle text-secondary ms-1">99 MB</span>
                    </a>
                    <a href="<?= $modal_base_url ?>/download.php?file=HostelERP-2.0.0-arm64-win.zip" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-2 fw-semibold download-trigger" onclick="handleDownload(event, 'Portable Zip ARM64', this.href)">
                      <i class="bi bi-cpu me-1"></i> Native ARM64 (.zip) <span class="badge bg-dark-subtle text-dark ms-1">99 MB</span>
                    </a>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- ================= macOS Container ================= -->
          <div id="options-macos" class="os-options-container" style="display: none;">
            <div class="row g-3">
              <div class="col-12">
                <div class="card border-0 shadow-sm p-3 text-start" style="background: rgba(108, 99, 255, 0.05); border-left: 4px solid #333 !important; border-radius: 14px;">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-apple fs-5"></i>
                    <h6 class="fw-bold mb-0" style="color: var(--inner-heading);">macOS Disk Image (.dmg)</h6>
                  </div>
                  <p class="text-muted small mb-3">
                    Drag and drop installation into Applications. Compatible with macOS 10.15 (Catalina) through macOS 15 (Sequoia).
                  </p>

                  <div class="row g-2">
                    <!-- Apple Silicon (Recommended) -->
                    <div class="col-md-6">
                      <div class="p-3 rounded-3 border h-100 d-flex flex-column justify-content-between" style="background: rgba(255,255,255,0.4);">
                        <div>
                          <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong style="color: var(--inner-heading);"><i class="bi bi-cpu-fill text-success me-1"></i> Apple Silicon</strong>
                            <span class="badge bg-success" style="font-size: 0.72rem;">Recommended</span>
                          </div>
                          <p class="text-muted small mb-2">M1, M2, M3, M4 Macs (2020 to Present: MacBook Air/Pro, iMac, Mac mini, Studio).</p>
                        </div>
                        <a href="<?= $modal_base_url ?>/download.php?file=HostelERP-2.0.0-arm64.dmg" class="btn btn-dark w-100 rounded-pill fw-semibold download-trigger" onclick="handleDownload(event, 'macOS Apple Silicon .dmg', this.href)">
                          <i class="bi bi-download me-1"></i> Download .dmg <span class="badge bg-light text-dark ms-1">90 MB</span>
                        </a>
                      </div>
                    </div>

                    <!-- Intel Processor -->
                    <div class="col-md-6">
                      <div class="p-3 rounded-3 border h-100 d-flex flex-column justify-content-between" style="background: rgba(255,255,255,0.4);">
                        <div>
                          <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong style="color: var(--inner-heading);"><i class="bi bi-laptop me-1"></i> Intel Processor</strong>
                            <span class="badge bg-secondary" style="font-size: 0.72rem;">x64</span>
                          </div>
                          <p class="text-muted small mb-2">For older Intel-based Macs manufactured before late 2020.</p>
                        </div>
                        <a href="<?= $modal_base_url ?>/download.php?file=HostelERP-2.0.0.dmg" class="btn btn-outline-dark w-100 rounded-pill fw-semibold download-trigger" onclick="handleDownload(event, 'macOS Intel .dmg', this.href)">
                          <i class="bi bi-download me-1"></i> Download .dmg <span class="badge bg-secondary text-white ms-1">95 MB</span>
                        </a>
                      </div>
                    </div>
                  </div>

                  <div class="mt-3 text-muted" style="font-size: 0.8rem;">
                    <i class="bi bi-info-circle me-1"></i> Not sure? Check <strong>Apple Menu  &gt; About This Mac</strong> to see your processor type.
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ================= Linux Container ================= -->
          <div id="options-linux" class="os-options-container" style="display: none;">
            <div class="row g-3">
              
              <!-- Debian / Ubuntu -->
              <div class="col-12">
                <div class="card border-0 shadow-sm p-3 text-start" style="background: rgba(233, 84, 32, 0.05); border-left: 4px solid #e95420 !important; border-radius: 14px;">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center gap-2">
                      <i class="bi bi-ubuntu fs-5 text-warning"></i>
                      <h6 class="fw-bold mb-0" style="color: var(--inner-heading);">Debian / Ubuntu Package (.deb)</h6>
                    </div>
                    <span class="badge bg-warning text-dark">Native Package</span>
                  </div>
                  <p class="text-muted small mb-3">
                    For Ubuntu, Debian, Linux Mint, Pop!_OS, and Kali. Install with <code>sudo apt install ./hostelerp-desktop_*.deb</code>.
                  </p>
                  <div class="d-flex flex-wrap gap-2">
                    <a href="<?= $modal_base_url ?>/download.php?file=hostelerp-desktop_2.0.0_amd64.deb" class="btn btn-warning text-dark fw-bold px-3 rounded-pill download-trigger" onclick="handleDownload(event, 'Linux .deb x64', this.href)">
                      <i class="bi bi-download me-1"></i> x64 (AMD64) .deb <span class="badge bg-dark text-white ms-1">69 MB</span>
                    </a>
                    <a href="<?= $modal_base_url ?>/download.php?file=hostelerp-desktop_2.0.0_arm64.deb" class="btn btn-outline-dark px-3 rounded-pill fw-semibold download-trigger" onclick="handleDownload(event, 'Linux .deb arm64', this.href)">
                      <i class="bi bi-download me-1"></i> ARM64 .deb <span class="badge bg-secondary ms-1">65 MB</span>
                    </a>
                  </div>
                </div>
              </div>

              <!-- Universal AppImage -->
              <div class="col-12">
                <div class="card border-0 shadow-sm p-3 text-start" style="background: rgba(125, 125, 125, 0.04); border-radius: 14px;">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-box fs-5 text-secondary"></i>
                    <h6 class="fw-bold mb-0" style="color: var(--inner-heading);">Universal Portable (.AppImage)</h6>
                  </div>
                  <p class="text-muted small mb-3">
                    Runs directly on any distribution (Fedora, Arch Linux, openSUSE, Debian, Ubuntu). Make executable with <code>chmod +x</code> and run.
                  </p>
                  <div class="d-flex flex-wrap gap-2">
                    <a href="<?= $modal_base_url ?>/download.php?file=HostelERP-2.0.0.AppImage" class="btn btn-outline-secondary px-3 rounded-pill fw-semibold download-trigger" onclick="handleDownload(event, 'Linux AppImage x64', this.href)">
                      <i class="bi bi-play-circle me-1"></i> x64 (AMD64) .AppImage <span class="badge bg-secondary ms-1">99 MB</span>
                    </a>
                    <a href="<?= $modal_base_url ?>/download.php?file=HostelERP-2.0.0-arm64.AppImage" class="btn btn-outline-secondary px-3 rounded-pill fw-semibold download-trigger" onclick="handleDownload(event, 'Linux AppImage arm64', this.href)">
                      <i class="bi bi-play-circle me-1"></i> ARM64 .AppImage <span class="badge bg-secondary ms-1">99 MB</span>
                    </a>
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div> <!-- /#desktop-options-wrapper -->

      </div>

      <!-- Modal Footer -->
      <div class="modal-footer border-0 pt-0 pb-4 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
          <i class="bi bi-shield-check text-success me-1"></i> Safe &amp; verified SHA-256 releases
        </div>
        <div class="d-flex gap-2">
          <a href="https://github.com/arpitchaudhary14/HostelERP/releases/tag/v2.0.0" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-github me-1"></i> v2.0 Release Notes &amp; Hashes
          </a>
          <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Download Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
  <div id="downloadToast" class="toast align-items-center text-white bg-primary border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 12px;">
    <div class="d-flex">
      <div class="toast-body">
        <i class="bi bi-arrow-down-circle-fill me-2 fs-6"></i>
        <span id="downloadToastText">Your download has started! Check your downloads bar.</span>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<script>
let isUserOnMobile = false;
let pendingMobileDownloadUrl = "";

function selectOS(osName) {
  // Update tabs
  document.querySelectorAll('.os-tab-btn').forEach(btn => {
    btn.classList.remove('btn-primary', 'btn-dark', 'btn-warning', 'text-white');
    btn.classList.add('text-secondary');
    btn.style.background = 'transparent';
  });

  const activeBtn = document.getElementById('tab-btn-' + osName);
  if (activeBtn) {
    if (osName === 'windows') {
      activeBtn.classList.add('btn-primary', 'text-white');
    } else if (osName === 'macos') {
      activeBtn.classList.add('btn-dark', 'text-white');
    } else if (osName === 'linux') {
      activeBtn.classList.add('btn-warning', 'text-dark');
    }
  }

  // Toggle containers
  document.querySelectorAll('.os-options-container').forEach(el => el.style.display = 'none');
  const targetContainer = document.getElementById('options-' + osName);
  if (targetContainer) {
    targetContainer.style.display = 'block';
  }
}

function showDiscontinuedNotice(archName) {
  const alertBox = document.getElementById('modal-alert-box');
  const alertText = document.getElementById('modal-alert-text');
  alertText.innerHTML = '<strong>' + archName + ' Notice:</strong> 32-bit architecture is no longer supported by HostelERP v2.0. For 32-bit devices, please continue using the full HostelERP web application directly in your browser. <a href="<?= $modal_base_url ?>/download.php?file=HostelERP-Setup-2.0.0-ia32.exe" target="_blank" class="alert-link text-decoration-underline ms-1">View 404 response</a>';
  alertBox.style.display = 'block';
  alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeModalAlert() {
  document.getElementById('modal-alert-box').style.display = 'none';
}

function handleDownload(event, pkgName, downloadUrl) {
  // If user is on a mobile phone (Android / iOS), intercept and warn them
  if (isUserOnMobile) {
    event.preventDefault();
    pendingMobileDownloadUrl = downloadUrl;
    const confirmBox = document.getElementById('mobile-download-confirm-box');
    const confirmMsg = document.getElementById('mobile-confirm-message');
    const confirmBtn = document.getElementById('mobile-confirm-download-btn');
    if (confirmBox && confirmMsg && confirmBtn) {
      confirmMsg.innerHTML = 'You are on a mobile device (' + (isAndroidDevice ? 'Android' : (isIOSDevice ? 'iOS' : 'Phone')) + '). <strong>' + pkgName + '</strong> is an installer for Desktop PC / Laptops and cannot run directly on your phone. Would you like to download it anyway to transfer to your computer?';
      confirmBtn.href = downloadUrl;
      confirmBox.style.display = 'block';
      confirmBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    return false;
  }

  // On PC / Laptop, proceed directly
  showDownloadToast(pkgName);
  return true;
}

function onMobileConfirmDownload() {
  document.getElementById('mobile-download-confirm-box').style.display = 'none';
  showDownloadToast('Desktop Package');
}

function cancelMobileDownload() {
  document.getElementById('mobile-download-confirm-box').style.display = 'none';
  pendingMobileDownloadUrl = "";
}

function toggleDesktopOnMobile() {
  const desktopWrap = document.getElementById('desktop-options-wrapper');
  const toggleBtnText = document.getElementById('toggle-desktop-text');
  if (desktopWrap.style.display === 'none' || desktopWrap.style.display === '') {
    desktopWrap.style.display = 'block';
    toggleBtnText.textContent = 'Hide PC / Laptop Installers ▲';
    selectOS('windows');
  } else {
    desktopWrap.style.display = 'none';
    toggleBtnText.textContent = 'Looking for PC / Laptop Installers? Show Options ▼';
  }
}

function showDownloadToast(pkgName) {
  const toastEl = document.getElementById('downloadToast');
  const toastText = document.getElementById('downloadToastText');
  if (toastEl && window.bootstrap) {
    toastText.textContent = 'Downloading ' + pkgName + '... Check your browser downloads!';
    const toast = new bootstrap.Toast(toastEl, { delay: 6000 });
    toast.show();
  }
}

let isAndroidDevice = false;
let isIOSDevice = false;

document.addEventListener("DOMContentLoaded", function () {
  const ua = (navigator.userAgent || navigator.vendor || window.opera || '').toLowerCase();
  const badge = document.getElementById("detected-os-badge");
  const badgeText = document.getElementById("detected-os-text");

  // Check mobile devices FIRST to avoid Android false-matching Linux and iOS false-matching macOS
  isAndroidDevice = /android/i.test(ua);
  isIOSDevice = /iphone|ipad|ipod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
  isUserOnMobile = isAndroidDevice || isIOSDevice || /mobile|tablet|phone/i.test(ua);

  const mobileCard = document.getElementById('mobile-device-card');
  const mobileTitle = document.getElementById('mobile-os-title');
  const desktopWrapper = document.getElementById('desktop-options-wrapper');

  if (isUserOnMobile) {
    // Mobile Mode (Android / iOS)
    const mobileOSName = isAndroidDevice ? 'Android' : (isIOSDevice ? 'iOS' : 'Mobile Phone');
    if (mobileTitle) mobileTitle.textContent = mobileOSName;
    if (mobileCard) mobileCard.style.display = 'block';
    if (desktopWrapper) desktopWrapper.style.display = 'none'; // collapse desktop installers by default on mobile

    if (badge && badgeText) {
      badgeText.innerHTML = '📱 Detected: <strong>' + mobileOSName + '</strong> (Mobile App in Development)';
      badge.className = 'alert alert-warning py-2 px-3 mb-3 rounded-pill text-center mx-auto';
      badge.style.display = 'inline-block';
    }
  } else {
    // Desktop PC / Laptop Mode
    if (mobileCard) mobileCard.style.display = 'none';
    if (desktopWrapper) desktopWrapper.style.display = 'block';

    let detectedOS = "windows"; // default
    let displayOS = "Windows";

    if (ua.includes("mac") || ua.includes("darwin")) {
      detectedOS = "macos";
      displayOS = "macOS";
    } else if (ua.includes("linux") || ua.includes("x11")) {
      detectedOS = "linux";
      displayOS = "Linux";
    } else if (ua.includes("win")) {
      detectedOS = "windows";
      displayOS = "Windows";
    }

    if (badge && badgeText) {
      badgeText.innerHTML = '✨ Detected System: <strong>' + displayOS + '</strong> (Auto-selected)';
      badge.className = 'alert alert-info py-2 px-3 mb-3 rounded-pill text-center mx-auto';
      badge.style.display = 'inline-block';
    }

    selectOS(detectedOS);
  }
});
</script>
