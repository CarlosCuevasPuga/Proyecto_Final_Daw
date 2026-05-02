// js/app.js
const API_BASE = 'api/';

// State
let currentUser = null;

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    checkSession();
    loadRoutes();
    loadCoupons();
    
    // Auth Forms
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
    document.getElementById('registerForm').addEventListener('submit', handleRegister);
});

// Modals
function showModal(id) {
    document.getElementById(id).classList.add('show');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}

// Profile Dropdown
function toggleProfileMenu() {
    const profileMenu = document.getElementById('profileMenu');
    profileMenu.classList.toggle('show');
}

function logout() {
    localStorage.removeItem('papm_user');
    currentUser = null;
    updateUIForUser();
    closeProfileMenu();
    alert('Sesión cerrada correctamente');
    // Recargar rutas para que se actualice la interfaz
    loadRoutes();
}

function closeProfileMenu() {
    const profileMenu = document.getElementById('profileMenu');
    profileMenu.classList.remove('show');
}

// Cerrar dropdown al hacer click fuera
document.addEventListener('click', (e) => {
    const profileDropdown = document.querySelector('.profile-dropdown');
    const profileMenu = document.getElementById('profileMenu');
    if (profileDropdown && !profileDropdown.contains(e.target) && profileMenu) {
        profileMenu.classList.remove('show');
    }
});

// Auth
function checkSession() {
    const user = localStorage.getItem('papm_user');
    if (user) {
        currentUser = JSON.parse(user);
        updateUIForUser();
        // Verificar estado actual del usuario en la BD
        refreshUserStatus();
    }
}

async function refreshUserStatus() {
    // Actualizar el estado del usuario desde la BD (especialmente el is_premium)
    if (!currentUser) return;
    
    try {
        const res = await fetch(API_BASE + 'user.php?action=get_user_status', {
            method: 'POST',
            body: JSON.stringify({ user_id: currentUser.id }),
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        
        if (data.status === 'success') {
            currentUser = {
                id: data.data.id,
                name: data.data.name,
                email: data.data.email,
                points: data.data.points,
                is_premium: data.data.is_premium
            };
            localStorage.setItem('papm_user', JSON.stringify(currentUser));
            updateUIForUser();
            if (typeof renderProfile === 'function') {
                renderProfile();
            }
            // Recargar las rutas para que se actualice el estado de premium
            loadRoutes();
        }
    } catch (err) {
        console.error('Error refreshing user status:', err);
    }
}

function updateUIForUser() {
    if (currentUser) {
        document.getElementById('authButtons').style.display = 'none';
        document.getElementById('userStatus').style.display = 'flex';
        document.getElementById('userPoints').innerText = currentUser.points;
    } else {
        document.getElementById('authButtons').style.display = 'block';
        document.getElementById('userStatus').style.display = 'none';
    }
    if (typeof renderProfile === 'function') {
        renderProfile();
    }
}

async function handleLogin(e) {
    e.preventDefault();
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;

    try {
        const res = await fetch(API_BASE + 'auth.php?action=login', {
            method: 'POST',
            body: JSON.stringify({ email, password }),
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        
        if (data.status === 'success') {
            localStorage.setItem('papm_user', JSON.stringify(data.user));
            currentUser = data.user;
            updateUIForUser();
            closeModal('loginModal');
            alert('¡Bienvenido de nuevo!');
        } else {
            alert(data.message || 'Error en el login');
        }
    } catch (err) {
        console.error(err);
        alert('Error de conexión');
    }
}

async function handleRegister(e) {
    e.preventDefault();
    const name = document.getElementById('regName').value;
    const email = document.getElementById('regEmail').value;
    const password = document.getElementById('regPassword').value;

    try {
        const res = await fetch(API_BASE + 'auth.php?action=register', {
            method: 'POST',
            body: JSON.stringify({ name, email, password }),
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        
        if (data.status === 'success') {
            closeModal('registerModal');
            alert('Registro exitoso. Ahora puedes iniciar sesión.');
            showModal('loginModal');
        } else {
            alert(data.message || 'Error en el registro');
        }
    } catch (err) {
        console.error(err);
        alert('Error de conexión');
    }
}

// Data Fetching
async function loadRoutes() {
    const container = document.getElementById('routesContainer');
    try {
        const res = await fetch(API_BASE + 'routes.php?action=list');
        const data = await res.json();
        
        let activeRoutes = [];
        let completedRoutes = [];
        if (currentUser) {
            try {
                const activeRes = await fetch(API_BASE + 'user.php?action=get_active_routes&user_id=' + currentUser.id);
                const activeData = await activeRes.json();
                if (activeData.status === 'success') {
                    const seenActive = new Set();
                    activeRoutes = activeData.data.filter(route => {
                        if (seenActive.has(route.id)) return false;
                        seenActive.add(route.id);
                        return true;
                    }).map(route => route.id);
                }
                const completedRes = await fetch(API_BASE + 'user.php?action=get_completed_routes&user_id=' + currentUser.id);
                const completedData = await completedRes.json();
                if (completedData.status === 'success') {
                    completedRoutes = completedData.data.map(routeId => Number(routeId));
                }
            } catch (err) {
                console.error('Error obteniendo estado de rutas:', err);
            }
        }

        if (data.status === 'success') {
            const seenRoutes = new Set();
            const uniqueRoutes = data.data.filter(route => {
                if (seenRoutes.has(route.id)) return false;
                seenRoutes.add(route.id);
                return true;
            });

            container.innerHTML = '';
            uniqueRoutes.forEach(route => {
                const card = document.createElement('div');
                const isPremium = route.is_premium == 1;
                card.className = `route-card ${isPremium ? 'premium' : ''}`;
                
                const canStartRoute = !isPremium || (currentUser && currentUser.is_premium);
                let buttonClass = canStartRoute ? 'btn btn-outline' : 'btn btn-outline disabled';
                let buttonText = isPremium && !canStartRoute ? '🔒 Solo Premium' : 'Iniciar Ruta';
                let buttonDisabled = !canStartRoute;
                let buttonOnclick = `completeRoute(${route.id}, ${isPremium})`;
                let activeBadge = '';

                if (currentUser) {
                    if (completedRoutes.includes(route.id)) {
                        buttonClass = 'btn btn-success disabled';
                        buttonText = '✓ Completada';
                        buttonDisabled = true;
                        buttonOnclick = '';
                    } else if (activeRoutes.includes(route.id)) {
                        activeBadge = '<div class="route-status">En Progreso</div>';
                        buttonClass = 'btn btn-danger';
                        buttonText = 'Cancelar Progreso';
                        buttonDisabled = false;
                        buttonOnclick = `cancelRoute(${route.id})`;
                    }
                }

                card.innerHTML = `
                    <div class="route-header">
                        <div class="route-title">
                            ${isPremium ? "<i class='bx bxs-crown' style='color: var(--secondary-color)'></i>" : ""}
                            ${route.name}
                        </div>
                        <div class="route-points">
                            <i class='bx bxs-star'></i> ${route.reward_points}
                        </div>
                    </div>
                    <div class="route-desc">${route.description}</div>
                    <div class="route-meta">
                        ${activeBadge}
                        <button class="${buttonClass}" ${buttonOnclick ? `onclick="${buttonOnclick}"` : ''} ${buttonDisabled ? 'disabled' : ''}>${buttonText}</button>
                    </div>
                `;
                container.appendChild(card);
            });
        }
    } catch (err) {
        container.innerHTML = '<p>Error cargando rutas.</p>';
    }
}

async function loadCoupons() {
    const container = document.getElementById('rewardsContainer');
    try {
        const res = await fetch(API_BASE + 'user.php?action=get_coupons');
        const data = await res.json();
        
        if (data.status === 'success') {
            container.innerHTML = '';
            data.data.forEach(coupon => {
                const card = document.createElement('div');
                card.className = 'route-card'; // Reusing style
                
                card.innerHTML = `
                    <div class="route-header">
                        <div class="route-title">${coupon.title}</div>
                        <div class="route-points" style="color: var(--primary-color)">
                            <i class='bx bxs-purchase-tag'></i> -${coupon.points_cost} pts
                        </div>
                    </div>
                    <div class="route-desc">${coupon.description}</div>
                    <button class="btn btn-primary" onclick="buyCoupon(${coupon.id})">Canjear</button>
                `;
                container.appendChild(card);
            });
        }
    } catch (err) {
        container.innerHTML = '<p>Error cargando cupones.</p>';
    }
}

// Interactions
async function completeRoute(routeId, isPremium = false) {
    if (!currentUser) return alert("Inicia sesión para completar rutas y ganar puntos.");
    
    // Si es una ruta premium, verificar primero el estado actual del usuario
    if (isPremium) {
        try {
            const userStatusRes = await fetch(API_BASE + 'user.php?action=get_user_status', {
                method: 'POST',
                body: JSON.stringify({ user_id: currentUser.id }),
                headers: { 'Content-Type': 'application/json' }
            });
            const userStatusData = await userStatusRes.json();
            
            if (userStatusData.status === 'success') {
                currentUser = {
                    id: userStatusData.data.id,
                    name: userStatusData.data.name,
                    email: userStatusData.data.email,
                    points: userStatusData.data.points,
                    is_premium: userStatusData.data.is_premium
                };
                localStorage.setItem('papm_user', JSON.stringify(currentUser));
                
                if (!currentUser.is_premium) {
                    return alert("Esta es una ruta premium. Debes tener una suscripción premium para completarla.");
                }
            }
        } catch (err) {
            console.error('Error verificando estado premium:', err);
            return alert("Error al verificar el estado de tu cuenta.");
        }
    }
    
    try {
        const res = await fetch(API_BASE + 'user.php?action=start_route', {
            method: 'POST',
            body: JSON.stringify({ user_id: currentUser.id, route_id: routeId }),
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        
        alert(data.message);
        if (data.status === 'success') {
            loadRoutes();
            if (window.location.pathname.includes('mapa.html')) {
                loadActiveRoutesOnMap();
            }
        }
    } catch (err) {
        alert("Error de conexión");
    }
}

async function cancelRoute(routeId) {
    if (!currentUser) return alert("Inicia sesión para cancelar la ruta.");
    if (!confirm('¿Estás seguro de que quieres cancelar esta ruta?')) return;

    try {
        const res = await fetch(API_BASE + 'user.php?action=cancel_route', {
            method: 'POST',
            body: JSON.stringify({ user_id: currentUser.id, route_id: routeId }),
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();

        alert(data.message);
        if (data.status === 'success') {
            loadRoutes();
            if (window.location.pathname.includes('mapa.html')) {
                loadActiveRoutesOnMap();
            }
        }
    } catch (err) {
        console.error(err);
        alert("Error de conexión");
    }
}

async function buyCoupon(couponId) {
    if (!currentUser) return alert("Inicia sesión para canjear cupones.");
    
    try {
        const res = await fetch(API_BASE + 'user.php?action=buy_coupon', {
            method: 'POST',
            body: JSON.stringify({ user_id: currentUser.id, coupon_id: couponId }),
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        
        alert(data.message);
        if (data.status === 'success') {
            currentUser.points = data.new_points;
            localStorage.setItem('papm_user', JSON.stringify(currentUser));
            updateUIForUser();
        }
    } catch (err) {
        alert("Error de conexión");
    }
}
