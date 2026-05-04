// js/admin.js

const adminState = {
    restaurants: [],
    routes: [],
    users: []
};

function initAdminPage() {
    const adminContent = document.getElementById('adminContent');
    const adminAccessDenied = document.getElementById('adminAccessDenied');
    const adminUserLabel = document.getElementById('adminUserLabel');

    if (!currentUser || !currentUser.is_admin) {
        if (adminContent) adminContent.style.display = 'none';
        if (adminAccessDenied) adminAccessDenied.style.display = 'block';
        if (adminUserLabel) adminUserLabel.textContent = currentUser ? `${currentUser.name || currentUser.email} (no es administrador)` : 'No has iniciado sesión';
        return;
    }

    if (adminContent) adminContent.style.display = 'block';
    if (adminAccessDenied) adminAccessDenied.style.display = 'none';
    if (adminUserLabel) adminUserLabel.textContent = `${currentUser.name || currentUser.email} (Administrador)`;
    loadRestaurantOptions();
    loadAdminRoutes();
    loadAdminUsers();
    showAdminSection('routes');
}

function showAdminSection(section) {
    const routeTab = document.getElementById('tabRoutes');
    const userTab = document.getElementById('tabUsers');
    const routesSection = document.getElementById('adminRoutesSection');
    const usersSection = document.getElementById('adminUsersSection');

    if (routeTab && userTab && routesSection && usersSection) {
        routeTab.classList.toggle('active', section === 'routes');
        userTab.classList.toggle('active', section === 'users');
        routesSection.classList.toggle('show', section === 'routes');
        usersSection.classList.toggle('show', section === 'users');
    }
}

function showAdminMessage(message, type = 'success') {
    const alert = document.getElementById('adminAlert');
    if (!alert) return;
    alert.textContent = message;
    alert.className = `admin-alert ${type}`;
    alert.style.display = 'block';
    setTimeout(() => {
        alert.style.display = 'none';
    }, 4000);
}

async function loadRestaurantOptions() {
    try {
        const res = await fetch(API_BASE + 'routes.php?action=restaurants');
        const data = await res.json();
        if (data.status === 'success') {
            adminState.restaurants = data.data;
            renderRestaurantSelection();
        }
    } catch (err) {
        console.error('Error cargando restaurantes:', err);
    }
}

function renderRestaurantSelection(selected = {}) {
    const container = document.getElementById('restaurantSelection');
    if (!container) return;
    container.innerHTML = '';

    adminState.restaurants.forEach((restaurant) => {
        const isChecked = !!selected[restaurant.id];
        const row = document.createElement('div');
        row.className = 'restaurant-item';
        row.innerHTML = `
            <label>
                <input type="checkbox" class="restaurant-checkbox" data-id="${restaurant.id}" ${isChecked ? 'checked' : ''}> ${restaurant.name}
            </label>
        `;
        container.appendChild(row);
    });
}

async function loadAdminRoutes() {
    try {
        const res = await fetch(API_BASE + 'routes.php?action=list');
        const data = await res.json();
        if (data.status === 'success') {
            adminState.routes = data.data;
            renderRouteTable();
        }
    } catch (err) {
        console.error('Error cargando rutas administrativas:', err);
    }
}

function renderRouteTable() {
    const tbody = document.getElementById('routesTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (!adminState.routes.length) {
        tbody.innerHTML = `<tr><td colspan="6"><div class="admin-empty-state">No hay rutas creadas aún.</div></td></tr>`;
        return;
    }

    adminState.routes.forEach((route) => {
        const tr = document.createElement('tr');
        const restaurants = route.restaurants ? route.restaurants.map((r) => r.name).join(', ') : '-';
        tr.innerHTML = `
            <td>${route.id}</td>
            <td>${route.name}</td>
            <td>${route.reward_points}</td>
            <td>${route.is_premium ? 'Sí' : 'No'}</td>
            <td>${restaurants}</td>
            <td>
                <button class="btn btn-outline" onclick="openRouteModal(${route.id})">Editar</button>
                <button class="btn btn-danger" onclick="deleteAdminRoute(${route.id})">Eliminar</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function openRouteModal(routeId = null) {
    const title = document.getElementById('routeModalTitle');
    const form = document.getElementById('routeForm');
    const routeIdField = document.getElementById('routeId');
    const nameField = document.getElementById('routeName');
    const descField = document.getElementById('routeDescription');
    const pointsField = document.getElementById('routePoints');
    const premiumField = document.getElementById('routePremium');

    if (!form || !routeIdField || !nameField || !descField || !pointsField || !premiumField) return;

    if (!routeId) {
        title.textContent = 'Nueva Ruta';
        routeIdField.value = '';
        nameField.value = '';
        descField.value = '';
        pointsField.value = 100;
        premiumField.checked = false;
        renderRestaurantSelection();
    } else {
        const route = adminState.routes.find((item) => item.id === routeId);
        if (!route) return;
        title.textContent = 'Editar Ruta';
        routeIdField.value = route.id;
        nameField.value = route.name;
        descField.value = route.description;
        pointsField.value = route.reward_points;
        premiumField.checked = route.is_premium == 1;
        const selected = {};
        if (route.restaurants && route.restaurants.length) {
            route.restaurants.forEach((restaurant) => {
                selected[restaurant.id] = true;
            });
        }
        renderRestaurantSelection(selected);
    }

    showModal('routeModal');
}

function getSelectedRestaurants() {
    const selected = [];
    const checkboxes = document.querySelectorAll('.restaurant-checkbox');
    checkboxes.forEach((checkbox) => {
        if (checkbox.checked) {
            const id = checkbox.getAttribute('data-id');
            selected.push({ id: parseInt(id, 10) });
        }
    });
    return selected;
}

async function saveRoute(event) {
    event.preventDefault();
    const routeId = document.getElementById('routeId').value;
    const name = document.getElementById('routeName').value.trim();
    const description = document.getElementById('routeDescription').value.trim();
    const rewardPoints = parseInt(document.getElementById('routePoints').value, 10);
    const isPremium = document.getElementById('routePremium').checked;
    const restaurants = getSelectedRestaurants();

    if (!name || !description) {
        showAdminMessage('Debes completar el nombre y la descripción de la ruta.', 'error');
        return;
    }
    if (!restaurants.length) {
        showAdminMessage('Selecciona al menos un restaurante para la ruta.', 'error');
        return;
    }

    try {
        const res = await fetch(API_BASE + 'routes.php?action=save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: routeId || null,
                name,
                description,
                reward_points: rewardPoints,
                is_premium: isPremium ? 1 : 0,
                restaurants
            })
        });
        const data = await res.json();
        if (data.status === 'success') {
            showAdminMessage(data.message, 'success');
            closeModal('routeModal');
            loadAdminRoutes();
        } else {
            showAdminMessage(data.message || 'Error guardando la ruta.', 'error');
        }
    } catch (err) {
        console.error('Error guardando ruta:', err);
        showAdminMessage('Error de conexión al guardar la ruta.', 'error');
    }
}

async function deleteAdminRoute(routeId) {
    if (!confirm('¿Eliminar esta ruta? Esta acción es irreversible.')) return;
    try {
        const res = await fetch(API_BASE + `routes.php?action=delete&id=${routeId}`);
        const data = await res.json();
        if (data.status === 'success') {
            showAdminMessage(data.message, 'success');
            loadAdminRoutes();
        } else {
            showAdminMessage(data.message || 'No se pudo eliminar la ruta.', 'error');
        }
    } catch (err) {
        console.error('Error eliminando ruta:', err);
        showAdminMessage('Error de conexión al eliminar la ruta.', 'error');
    }
}

async function loadAdminUsers() {
    try {
        const res = await fetch(API_BASE + 'user.php?action=list_users');
        const data = await res.json();
        if (data.status === 'success') {
            adminState.users = data.data;
            renderUserTable();
        }
    } catch (err) {
        console.error('Error cargando usuarios:', err);
    }
}

function renderUserTable() {
    const tbody = document.getElementById('usersTableBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (!adminState.users.length) {
        tbody.innerHTML = `<tr><td colspan="6"><div class="admin-empty-state">No hay usuarios registrados.</div></td></tr>`;
        return;
    }

    adminState.users.forEach((user) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${user.id}</td>
            <td>${user.name}</td>
            <td>${user.email}</td>
            <td>${user.points}</td>
            <td>
                <label class="admin-toggle">
                    <input type="checkbox" ${user.is_premium ? 'checked' : ''} onchange="toggleUserPremium(${user.id}, this.checked)">
                    Premium
                </label>
                <label class="admin-toggle">
                    <input type="checkbox" ${user.is_admin ? 'checked' : ''} onchange="toggleUserAdmin(${user.id}, this.checked)">
                    Admin
                </label>
            </td>
            <td>
                <button class="btn btn-danger" onclick="deleteAdminUser(${user.id})">Eliminar</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

async function toggleUserPremium(userId, value) {
    await updateAdminUser(userId, { is_premium: value ? 1 : 0 });
}

async function toggleUserAdmin(userId, value) {
    await updateAdminUser(userId, { is_admin: value ? 1 : 0 });
}

async function updateAdminUser(userId, updateData) {
    try {
        const payload = { user_id: userId, ...updateData };
        const res = await fetch(API_BASE + 'user.php?action=update_user', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === 'success') {
            showAdminMessage(data.message, 'success');
            loadAdminUsers();
            if (currentUser && currentUser.id === userId) {
                refreshUserStatus();
            }
        } else {
            showAdminMessage(data.message || 'Error actualizando usuario.', 'error');
        }
    } catch (err) {
        console.error('Error actualizando usuario:', err);
        showAdminMessage('Error de conexión al actualizar usuario.', 'error');
    }
}

async function deleteAdminUser(userId) {
    if (!confirm('¿Eliminar este usuario? Esta acción eliminará todo su historial.')) return;
    try {
        const res = await fetch(API_BASE + 'user.php?action=delete_user', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        });
        const data = await res.json();
        if (data.status === 'success') {
            showAdminMessage(data.message, 'success');
            loadAdminUsers();
        } else {
            showAdminMessage(data.message || 'Error eliminando usuario.', 'error');
        }
    } catch (err) {
        console.error('Error eliminando usuario:', err);
        showAdminMessage('Error de conexión al eliminar usuario.', 'error');
    }
}

// Expose helpers to HTML
window.openRouteModal = openRouteModal;
window.deleteAdminRoute = deleteAdminRoute;
window.toggleUserPremium = toggleUserPremium;
window.toggleUserAdmin = toggleUserAdmin;
window.deleteAdminUser = deleteAdminUser;

// Initialize admin page after app.js
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.pathname.includes('admin.html')) {
        document.getElementById('tabRoutes').addEventListener('click', () => showAdminSection('routes'));
        document.getElementById('tabUsers').addEventListener('click', () => showAdminSection('users'));
        document.getElementById('openRouteModalBtn').addEventListener('click', () => openRouteModal());
        document.getElementById('routeForm').addEventListener('submit', saveRoute);
        initAdminPage();
    }
});
