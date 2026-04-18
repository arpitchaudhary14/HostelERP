(function () {
    'use strict';
    function initScrollReveal() {
        const reveals = document.querySelectorAll('.reveal');
        if (!reveals.length) return;
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        observer.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
        );
        reveals.forEach((el, i) => {
            el.style.setProperty('--reveal-index', i);
            observer.observe(el);
        });
    }
    function initPageTransition() {
        document.body.classList.add('page-fade-in');
    }
    document.addEventListener('DOMContentLoaded', () => {
        initPageTransition();
        initScrollReveal();
        ThemeToggle.init();
    });
})();
const ThemeToggle = {
    storageKey: 'hostelerp-theme',
    getPreference() {
        return localStorage.getItem(this.storageKey) || 'light';
    },
    setPreference(theme) {
        localStorage.setItem(this.storageKey, theme);
    },
    apply(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        document.querySelectorAll('.theme-toggle').forEach(btn => {
            const icon = btn.querySelector('.theme-icon');
            const label = btn.querySelector('.theme-label');
            if (icon) icon.textContent = theme === 'dark' ? '☀️' : '🌙';
            if (label) label.textContent = theme === 'dark' ? 'Light' : 'Dark';
        });
    },
    toggle() {
        const current = this.getPreference();
        const next = current === 'dark' ? 'light' : 'dark';
        this.setPreference(next);
        this.apply(next);
        document.querySelectorAll('.theme-toggle .theme-icon').forEach(icon => {
            icon.style.transform = 'rotate(360deg) scale(1.3)';
            setTimeout(() => {
                icon.style.transform = '';
            }, 500);
        });
    },
    init() {
        const theme = this.getPreference();
        this.apply(theme);
        document.querySelectorAll('.theme-toggle').forEach(btn => {
            btn.addEventListener('click', () => this.toggle());
        });
    }
};
const Validator = {
    validateField(input, rules = {}) {
        const wrapper = input.closest('.input-wrapper') || input.closest('.password-wrapper');
        if (!wrapper) return true;
        const isPassword = input.type === 'password' || input.dataset.noTrim === 'true';
        const val = isPassword ? input.value : input.value.trim();
        let valid = true;
        if (rules.required && val.length === 0) {
            valid = false;
        }
        if (valid && rules.minLen && val.length < rules.minLen) {
            valid = false;
        }
        if (valid && rules.maxLen && val.length > rules.maxLen) {
            valid = false;
        }
        if (valid && rules.pattern && !rules.pattern.test(val)) {
            valid = false;
        }
        if (valid && rules.match) {
            const matchEl = document.querySelector(rules.match);
            if (matchEl && matchEl.value !== input.value) {
                valid = false;
            }
        }
        if (!rules.required && val.length === 0) {
            wrapper.classList.remove('is-valid', 'is-invalid');
            return true;
        }
        wrapper.classList.toggle('is-valid', valid);
        wrapper.classList.toggle('is-invalid', !valid);
        return valid;
    },
    attachLiveValidation(input, rules = {}) {
        let typed = false;
        input.addEventListener('input', () => {
            typed = true;
            this.validateField(input, rules);
        });
        input.addEventListener('blur', () => {
            if (typed || input.value.length > 0) {
                this.validateField(input, rules);
            }
        });
    },
    getPasswordStrength(password) {
        let score = 0;
        if (password.length >= 6) score++;
        if (password.length >= 10) score++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
        if (/\d/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        if ((password.match(/[^A-Za-z0-9]/g) || []).length >= 2) score++;
        if (score <= 1) return { level: 'weak', label: 'Weak', cls: 'strength-weak' };
        if (score <= 2) return { level: 'fair', label: 'Fair', cls: 'strength-fair' };
        if (score <= 4) return { level: 'good', label: 'Good', cls: 'strength-good' };
        return { level: 'strong', label: 'Strong', cls: 'strength-strong' };
    },
    attachPasswordStrength(input, meterFill, textEl) {
        input.addEventListener('input', () => {
            const val = input.value;
            if (!val) {
                meterFill.className = 'strength-meter-fill';
                if (textEl) textEl.textContent = '';
                return;
            }
            const str = this.getPasswordStrength(val);
            meterFill.className = 'strength-meter-fill ' + str.cls;
            if (textEl) {
                textEl.textContent = str.label;
                const colors = {
                    weak: 'var(--accent-danger)',
                    fair: 'var(--accent-warning)',
                    good: 'var(--accent-info)',
                    strong: 'var(--accent-secondary)'
                };
                textEl.style.color = colors[str.level];
            }
        });
    }
};
function createEyeToggle(passwordInput, toggleBtn) {
    if (!passwordInput || !toggleBtn) return;
    const eyeOpenSVG = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`;
    const eyeClosedSVG = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`;
    toggleBtn.innerHTML = eyeOpenSVG;
    toggleBtn.addEventListener('click', () => {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        toggleBtn.classList.toggle('active', isPassword);
        toggleBtn.innerHTML = isPassword ? eyeClosedSVG : eyeOpenSVG;
        toggleBtn.style.transform = 'translateY(-50%) scale(0.7)';
        requestAnimationFrame(() => {
            setTimeout(() => {
                toggleBtn.style.transform = 'translateY(-50%) scale(1)';
            }, 80);
        });
        passwordInput.focus();
    });
}