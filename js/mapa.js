// Coordenadas de respaldo (Centro de Motril)
var FALLBACK_LAT = 36.7450;
var FALLBACK_LNG = -3.5160;

// Inicializar mapa centrado en Motril por defecto
var map = L.map('map').setView([FALLBACK_LAT, FALLBACK_LNG], 14);

// Capa base (OpenStreetMap GRATIS)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

// Variable para guardar el control de rutas actual
var currentRoutingControl = null;

// Variable para guardar la ubicación del usuario
var userLocation = null;

// Marcadores activos en el mapa (para limpiarlos entre rutas)
var activeMarkers = [];

// ── Iconos personalizados ──────────────────────────────────────────────────────

var userIcon = L.divIcon({
    className: '',
    html: `<div style="
        width: 18px; height: 18px;
        background: #4A90E2;
        border: 3px solid white;
        border-radius: 50%;
        box-shadow: 0 0 0 5px rgba(74,144,226,0.25), 0 2px 8px rgba(0,0,0,0.3);
    "></div>`,
    iconSize: [18, 18],
    iconAnchor: [9, 9]
});

function makeStopIcon(label, isLast) {
    const color = isLast ? '#E74C3C' : '#F39C12';
    return L.divIcon({
        className: '',
        html: `<div style="
            position: relative;
            width: 32px; height: 32px;
            background: ${color};
            border: 3px solid white;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            box-shadow: 0 2px 8px rgba(0,0,0,0.35);
            display: flex; align-items: center; justify-content: center;
        ">
            <span style="transform: rotate(45deg); color: white; font-weight: 700; font-size: 13px;">${label}</span>
        </div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 32]
    });
}

// Marcador de ubicación del usuario
var userMarker = null;

// ── Geolocalización ───────────────────────────────────────────────────────────

/**
 * Obtiene la ubicación actual del usuario.
 * Si falla, usa el centro de Motril como fallback.
 * @param {Function|null} callback - Se llama con L.latLng cuando se obtiene la posición.
 */
function getUserLocation(callback) {
    if (!navigator.geolocation) {
        console.warn("Geolocalización no soportada. Usando ubicación por defecto.");
        userLocation = L.latLng(FALLBACK_LAT, FALLBACK_LNG);
        showFallbackMarker();
        if (callback) callback(userLocation);
        return;
    }

    const btn = document.getElementById('locateBtn');
    if (btn) {
        btn.innerHTML = `<i class='bx bx-loader-alt bx-spin'></i>`;
        btn.disabled = true;
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {
            userLocation = L.latLng(position.coords.latitude, position.coords.longitude);
            map.setView(userLocation, 15);

            if (userMarker) map.removeLayer(userMarker);
            userMarker = L.marker(userLocation, { icon: userIcon })
                .addTo(map)
                .bindPopup('<b>📍 Tu ubicación actual</b>');

            if (btn) { btn.innerHTML = `<i class='bx bxs-navigation'></i>`; btn.disabled = false; }
            if (callback) callback(userLocation);
        },
        function(error) {
            console.warn("No se pudo obtener la ubicación:", error.message);
            userLocation = L.latLng(FALLBACK_LAT, FALLBACK_LNG);
            showFallbackMarker();
            showLocationError(error);
            if (btn) { btn.innerHTML = `<i class='bx bxs-navigation'></i>`; btn.disabled = false; }
            if (callback) callback(userLocation);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
    );
}

function showFallbackMarker() {
    if (userMarker) map.removeLayer(userMarker);
    userMarker = L.marker([FALLBACK_LAT, FALLBACK_LNG], { icon: userIcon })
        .addTo(map)
        .bindPopup('<b>📍 Centro de Motril</b><br><small>Activa la ubicación para mayor precisión</small>')
        .openPopup();
}

function showLocationError(error) {
    const messages = {
        1: "Permiso de ubicación denegado. Usando el centro de Motril como inicio.",
        2: "No se pudo detectar tu posición. Usando el centro de Motril.",
        3: "Tiempo de espera agotado. Usando el centro de Motril."
    };
    const toast = document.getElementById('locationToast');
    if (toast) {
        toast.textContent = messages[error.code] || "Error de ubicación desconocido.";
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
    }
}

// ── Dibujo de ruta ────────────────────────────────────────────────────────────

/**
 * Limpia todos los marcadores de paradas y el control de routing anteriores.
 */
function clearRouteFromMap() {
    if (currentRoutingControl) {
        map.removeControl(currentRoutingControl);
        currentRoutingControl = null;
    }
    activeMarkers.forEach(m => map.removeLayer(m));
    activeMarkers = [];
}

/**
 * Dibuja en el mapa la ruta completa desde la ubicación del usuario
 * hasta todos los restaurantes de la ruta (en orden).
 * @param {L.LatLng} origin
 * @param {Array}    restaurants  - Array de objetos {name, address, lat, lng}
 */
function drawRoute(origin, restaurants) {
    clearRouteFromMap();

    const waypoints = [origin];

    restaurants.forEach(function(r, index) {
        const lat = parseFloat(r.lat);
        const lng = parseFloat(r.lng);
        if (isNaN(lat) || isNaN(lng)) return;

        const latlng = L.latLng(lat, lng);
        waypoints.push(latlng);

        const isLast = (index === restaurants.length - 1);
        const marker = L.marker(latlng, { icon: makeStopIcon(index + 1, isLast) })
            .addTo(map)
            .bindPopup(
                '<b>' + (isLast ? '🏁' : '🍽️') + ' Parada ' + (index + 1) + ': ' + r.name + '</b>' +
                '<br><small>' + (r.address || '') + '</small>'
            );
        activeMarkers.push(marker);
    });

    if (waypoints.length < 2) {
        console.warn("No hay suficientes puntos para trazar la ruta.");
        return;
    }

    currentRoutingControl = L.Routing.control({
        waypoints: waypoints,
        routeWhileDragging: false,
        show: false,
        addWaypoints: false,
        fitSelectedRoutes: true,
        lineOptions: {
            styles: [{ color: '#4A90E2', weight: 5, opacity: 0.85 }]
        },
        createMarker: function() { return null; }
    }).addTo(map);
}

// ── Carga de rutas activas ────────────────────────────────────────────────────

/**
 * Consulta la API, obtiene la ruta activa del usuario con sus restaurantes
 * y llama a drawRoute. Usa get_active_routes que ya devuelve los restaurantes,
 * sin necesidad de una segunda llamada a routes.php.
 */
async function loadActiveRoutesOnMap() {
    if (!currentUser) {
        getUserLocation(null);
        return;
    }

    try {
        const activeRes = await fetch(API_BASE + 'user.php?action=get_active_routes&user_id=' + currentUser.id);
        const activeData = await activeRes.json();

        clearRouteFromMap();

        if (activeData.status === 'success' && activeData.data.length > 0) {
            const activeRoute = activeData.data[0];
            const restaurants = activeRoute.restaurants || [];

            if (restaurants.length === 0) {
                console.warn("La ruta activa no tiene restaurantes vinculados. Revisa route_restaurants en la BD.");
                getUserLocation(null);
                return;
            }

            // Obtener ubicación y trazar la ruta completa con todas las paradas
            getUserLocation(function(origin) {
                drawRoute(origin, restaurants);

                const toast = document.getElementById('locationToast');
                if (toast) {
                    toast.textContent = '🗺️ Ruta: ' + activeRoute.name + ' · ' + restaurants.length + ' parada' + (restaurants.length > 1 ? 's' : '');
                    toast.classList.add('show');
                    setTimeout(function() { toast.classList.remove('show'); }, 4500);
                }
            });

        } else {
            // Sin ruta activa: solo mostrar ubicación
            getUserLocation(null);

            const toast = document.getElementById('locationToast');
            if (toast) {
                toast.textContent = "No tienes ninguna ruta activa. Inicia una desde la pantalla de inicio.";
                toast.classList.add('show');
                setTimeout(function() { toast.classList.remove('show'); }, 4500);
            }
        }
    } catch (error) {
        console.error("Error cargando rutas en el mapa:", error);
        getUserLocation(null);
    }
}

// ── Inicialización ────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function() {
    // Mostrar la ubicación del usuario al cargar.
    // loadActiveRoutesOnMap() se llamará desde app.js tras restaurar la sesión.
    getUserLocation(null);
});
