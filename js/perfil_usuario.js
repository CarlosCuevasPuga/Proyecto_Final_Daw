document.addEventListener('DOMContentLoaded', () => {
    renderProfile();
});

function renderProfile() {
    const profileContent = document.getElementById('profileContent');
    const loginPrompt = document.getElementById('profileLoginPrompt');
    const storedUser = localStorage.getItem('papm_user');
    const user = typeof currentUser !== 'undefined' && currentUser ? currentUser : storedUser ? JSON.parse(storedUser) : null;

    if (!profileContent || !loginPrompt) return;

    if (!user) {
        profileContent.innerHTML = '';
        loginPrompt.style.display = 'block';
        return;
    }

    loginPrompt.style.display = 'none';
    profileContent.innerHTML = `
        <div class="profile-section">
            <h1>Mi perfil</h1>
            <div class="profile-row">
                <div class="profile-row-inner">
                    <span class="profile-label">Nombre</span>
                    <span class="profile-value">${escapeHtml(user.name)}</span>
                </div>
            </div>
            <div class="profile-row">
                <div class="profile-row-inner">
                    <span class="profile-label">Email</span>
                    <span class="profile-value">${escapeHtml(user.email)}</span>
                </div>
            </div>
            <div class="profile-row">
                <div class="profile-row-inner">
                    <span class="profile-label">Puntos</span>
                    <span class="profile-value">${escapeHtml(String(user.points || 0))}</span>
                </div>
            </div>
            <div class="profile-row">
                <div class="profile-row-inner">
                    <span class="profile-label">Estado</span>
                    <span class="profile-value">${user.is_premium ? 'Premium' : 'Usuario gratuito'}</span>
                </div>
            </div>
            <div class="profile-actions">
                <a href="./index.html" class="btn btn-outline">Ir a inicio</a>
            </div>
        </div>
    `;
}

function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
