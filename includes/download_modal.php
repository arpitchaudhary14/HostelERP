<!-- Download Modal -->
<div class="modal fade" id="downloadAppModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content glass-card-light border-0 shadow-lg" style="border-radius: 20px;">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold" id="downloadModalLabel">
          <i class="bi bi-laptop text-primary me-2"></i>Download HostelERP for Desktop
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4 text-center">
        
        <p class="text-muted mb-4">Choose your operating system to download the official native client.</p>

        <!-- OS Dropdown Selection -->
        <div class="mb-4 text-start mx-auto" style="max-width: 300px;">
            <label class="form-label fw-bold">Select Operating System</label>
            <select class="form-select form-select-lg rounded-pill" id="osSelect" onchange="showOSOptions()">
                <option value="" selected disabled>Choose your OS...</option>
                <option value="windows">🪟 Windows</option>
                <option value="macos">🍎 macOS</option>
                <option value="linux">🐧 Linux</option>
            </select>
        </div>

        <!-- Dynamic OS Recommendation Alert -->
        <div id="detected-os-badge" class="alert alert-info d-inline-block px-4 py-2 mb-4 rounded-pill" style="display: none;">
          <i class="bi bi-stars me-2"></i>Detecting your OS...
        </div>

        <!-- Windows Options -->
        <div id="options-windows" class="os-options-container" style="display: none;">
            <div class="row g-3 justify-content-center">
                <div class="col-md-10">
                    <div class="card border-primary glass-card-light shadow-sm p-4 text-start">
                        <h5 class="fw-bold text-primary mb-3"><i class="bi bi-windows me-2"></i>Windows Downloads</h5>
                        
                        <div class="mb-3">
                            <h6 class="fw-bold">Standard Installer (.exe)</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="#" id="link-win-x64-exe" class="btn btn-sm btn-primary">x64 (Intel/AMD)</a>
                                <a href="#" id="link-win-ia32-exe" class="btn btn-sm btn-outline-primary">ia32 (32-bit)</a>
                                <a href="#" id="link-win-arm64-exe" class="btn btn-sm btn-outline-primary">ARM64</a>
                            </div>
                        </div>
                        <div>
                            <h6 class="fw-bold">Portable Version (.zip)</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="#" id="link-win-x64-zip" class="btn btn-sm btn-dark">x64</a>
                                <a href="#" id="link-win-ia32-zip" class="btn btn-sm btn-outline-dark">ia32</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- macOS Options -->
        <div id="options-macos" class="os-options-container" style="display: none;">
            <div class="row g-3 justify-content-center">
                <div class="col-md-10">
                    <div class="card border-secondary glass-card-light shadow-sm p-4 text-start">
                        <h5 class="fw-bold mb-3" style="color:var(--inner-heading);"><i class="bi bi-apple me-2"></i>macOS Downloads</h5>
                        
                        <div class="mb-3">
                            <h6 class="fw-bold">Disk Image (.dmg) - Recommended</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="#" id="link-mac-arm64-dmg" class="btn btn-sm btn-dark">Apple Silicon (M1/M2/M3)</a>
                                <a href="#" id="link-mac-x64-dmg" class="btn btn-sm btn-outline-dark">Intel Chip (Older Macs)</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Linux Options -->
        <div id="options-linux" class="os-options-container" style="display: none;">
            <div class="row g-3 justify-content-center">
                <div class="col-md-10">
                    <div class="card border-warning glass-card-light shadow-sm p-4 text-start">
                        <h5 class="fw-bold mb-3" style="color:var(--inner-heading);"><i class="bi bi-ubuntu me-2"></i>Linux Downloads</h5>
                        
                        <div class="mb-3">
                            <h6 class="fw-bold">Debian/Ubuntu (.deb)</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="#" id="link-linux-x64-deb" class="btn btn-sm btn-warning text-dark fw-bold">x64 (AMD64)</a>
                                <a href="#" id="link-linux-arm64-deb" class="btn btn-sm btn-outline-dark">arm64</a>
                            </div>
                        </div>

                        <div>
                            <h6 class="fw-bold">Universal Portable (.AppImage)</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="#" id="link-linux-x64-appimage" class="btn btn-sm btn-secondary">x64 (AMD64)</a>
                                <a href="#" id="link-linux-arm64-appimage" class="btn btn-sm btn-outline-secondary">arm64</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- OS Detection & Dropdown Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
  const ua = navigator.userAgent.toLowerCase();
  const badge = document.getElementById("detected-os-badge");
  const osSelect = document.getElementById("osSelect");

  let detectedOS = "";
  if (ua.includes("win")) {
    detectedOS = "windows";
    badge.innerHTML = '✨ Detected OS: <strong>Windows</strong> - Auto-selected!';
  } else if (ua.includes("mac")) {
    detectedOS = "macos";
    badge.innerHTML = '✨ Detected OS: <strong>macOS</strong> - Auto-selected!';
  } else if (ua.includes("linux")) {
    detectedOS = "linux";
    badge.innerHTML = '✨ Detected OS: <strong>Linux</strong> - Auto-selected!';
  }

  if (detectedOS) {
      badge.style.display = "inline-block";
      osSelect.value = detectedOS;
      showOSOptions();
  }

  // Hardcoded Release Links for v1.0.9 to prevent GitHub API Rate Limiting (60 req/hr)
  const repo = "https://github.com/arpitchaudhary14/HostelERP/releases/download/v1.0.9";
  
  // Windows
  document.getElementById('link-win-x64-exe').href = repo + "/HostelERP-Setup-1.0.9.exe";
  document.getElementById('link-win-ia32-exe').href = repo + "/HostelERP-Setup-1.0.9-ia32.exe";
  document.getElementById('link-win-arm64-exe').href = repo + "/HostelERP-Setup-1.0.9-arm64.exe";
  
  document.getElementById('link-win-x64-zip').href = repo + "/HostelERP-1.0.9-win.zip";
  document.getElementById('link-win-ia32-zip').href = repo + "/HostelERP-1.0.9-win.zip"; // Assuming unified or fallback
  
  // macOS
  document.getElementById('link-mac-x64-dmg').href = repo + "/HostelERP-1.0.9.dmg";
  document.getElementById('link-mac-arm64-dmg').href = repo + "/HostelERP-1.0.9-arm64.dmg";
  
  // Linux
  document.getElementById('link-linux-x64-deb').href = repo + "/HostelERP_1.0.9_amd64.deb";
  document.getElementById('link-linux-arm64-deb').href = repo + "/HostelERP_1.0.9_arm64.deb";
  
  document.getElementById('link-linux-x64-appimage').href = repo + "/HostelERP-1.0.9.AppImage";
  document.getElementById('link-linux-arm64-appimage').href = repo + "/HostelERP-1.0.9-arm64.AppImage";
});

function showOSOptions() {
    document.querySelectorAll('.os-options-container').forEach(el => el.style.display = 'none');
    const selected = document.getElementById("osSelect").value;
    if (selected) document.getElementById("options-" + selected).style.display = 'block';
}
</script>
