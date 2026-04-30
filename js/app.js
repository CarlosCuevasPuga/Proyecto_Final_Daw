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

function viewProfile() {
    if (currentUser) {
        alert(`Perfil: ${currentUser.name}\nEmail: ${currentUser.email}\nPuntos: ${currentUser.points}`);
        closeProfileMenu();
    }
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
        
        if (data.status === 'success') {
            container.innerHTML = '';
            data.data.forEach(route => {
                const card = document.createElement('div');
                const isPremium = route.is_premium == 1;
                card.className = `route-card ${isPremium ? 'premium' : ''}`;
                
                // Verificar si el usuario puede completar esta ruta
                const canCompleteRoute = !isPremium || (currentUser && currentUser.is_premium);
                const buttonClass = canCompleteRoute ? 'btn btn-outline' : 'btn btn-outline disabled';
                const buttonText = isPremium && !canCompleteRoute 
                    ? '🔒 Solo Premium' 
                    : 'Completar Ruta';
                
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
                    <button class="${buttonClass}" onclick="completeRoute(${route.id}, ${isPremium})" ${!canCompleteRoute ? 'disabled' : ''}>${buttonText}</button>
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
            // Obtener estado actual del usuario desde la BD
            const userStatusRes = await fetch(API_BASE + 'user.php?action=get_user_status', {
                method: 'POST',
                body: JSON.stringify({ user_id: currentUser.id }),
                headers: { 'Content-Type': 'application/json' }
            });
            const userStatusData = await userStatusRes.json();
            
            if (userStatusData.status === 'success') {
                // Actualizar currentUser con el estado actual
                currentUser = {
                    id: userStatusData.data.id,
                    name: userStatusData.data.name,
                    email: userStatusData.data.email,
                    points: userStatusData.data.points,
                    is_premium: userStatusData.data.is_premium
                };
                localStorage.setItem('papm_user', JSON.stringify(currentUser));
                
                // Verificar si es premium
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
        const res = await fetch(API_BASE + 'user.php?action=complete_route', {
            method: 'POST',
            body: JSON.stringify({ user_id: currentUser.id, route_id: routeId }),
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        
        alert(data.message);
        if (data.status === 'success') {
            currentUser.points = data.new_points;
            localStorage.setItem('papm_user', JSON.stringify(currentUser));
            updateUIForUser();
            loadRoutes();
        }
    } catch (err) {
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
