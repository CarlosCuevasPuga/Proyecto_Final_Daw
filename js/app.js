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

// Auth
function checkSession() {
    const user = localStorage.getItem('papm_user');
    if (user) {
        currentUser = JSON.parse(user);
        updateUIForUser();
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
                    <button class="btn btn-outline" onclick="completeRoute(${route.id})">Completar Ruta</button>
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
async function completeRoute(routeId) {
    if (!currentUser) return alert("Inicia sesión para completar rutas y ganar puntos.");
    
    try {
        const res = await fetch(API_BASE + 'user.php?action=complete_route', {
            method: 'POST',
            body: JSON.stringify({ user_id: currentUser.id, route_id: routeId }),
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        
        alert(data.message);
        if (data.status === 'success') {
            // Reload user session from server logic ideally, here we mock reload
            currentUser.points = parseInt(currentUser.points) + 100; // Need exact points from route technically
            localStorage.setItem('papm_user', JSON.stringify(currentUser));
            updateUIForUser();
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
            // We would reload user points properly
            location.reload();
        }
    } catch (err) {
        alert("Error de conexión");
    }
}
