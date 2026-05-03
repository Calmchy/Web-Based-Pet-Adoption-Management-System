// ############ Toggle password visibility ############
document.querySelectorAll('.toggle-pw').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        input.type = input.type === 'password' ? 'text' : 'password';
        btn.textContent = input.type === 'password' ? '👁' : '🙈';
    });
});

// ############ Profile image live preview ############
document.getElementById('profile_image').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        alert('Image must be 2MB or less.');
        this.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
    reader.readAsDataURL(file);
});

// ############ Password strength meter ############
const pwInput    = document.getElementById('password');
const strengthEl = document.getElementById('pwStrength');
const colors     = ['#ef4444', '#f97316', '#eab308', '#22c55e'];
const labels     = ['Weak', 'Fair', 'Good', 'Strong'];

pwInput.addEventListener('input', () => {
    const v = pwInput.value;
    let score = 0;
    if (v.length >= 8)              score++;
    if (/[A-Z]/.test(v))            score++;
    if (/[0-9]/.test(v))            score++;
    if (/[^A-Za-z0-9]/.test(v))     score++;
    if (v.length === 0) {
        strengthEl.style.width = '0';
        strengthEl.title = '';
    } else {
        strengthEl.style.width  = ((score / 4) * 100) + '%';
        strengthEl.style.background = colors[score - 1] || '#ef4444';
        strengthEl.title = labels[score - 1] || '';
    }
});