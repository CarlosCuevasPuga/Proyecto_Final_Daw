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
 
// Icono personalizado para la ubicación del usuario
var userIcon = L.divIcon({
    className: '',
    html: `<div style="
        width: 18px; height: 18px;
        background: #4A90E2;
        border: 3px solid white;
        border-radius: 50%;
        box-shadow: 0 0 0 4px rgba(74,144,226,0.3), 0 2px 8px rgba(0,0,0,0.3);
    "></div>`,
    iconSize: [18, 18],
    iconAnchor: [9, 9]
});
 
// Icono personalizado para el destino
var destIcon = L.divIcon({
    className: '',
    html: `<div style="
        width: 28px; height: 28px;
        background: #E74C3C;
        border: 3px solid white;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    "></div>`,
    iconSize: [28, 28],
    iconAnchor: [14, 28]
});
 
// Marcador de ubicación del usuario (se actualiza con geolocalización)
var userMarker = null;
 
/**
 * Obtiene la ubicación actual del usuario y actualiza el mapa.
 * Si el usuario deniega el permiso, usa el centro de Motril como fallback.
 */
function getUserLocation(callback) {
    if (!navigator.geolocation) {
        console.warn("Geolocalización no soportada. Usando ubicación por defecto.");
        userLocation = L.latLng(FALLBACK_LAT, FALLBACK_LNG);
        showFallbackMarker();
        if (callback) callback(userLocation);
        return;
    }
 
    // Mostrar indicador de carga en el botón
    const btn = document.getElementById('locateBtn');
    if (btn) {
        btn.innerHTML = `<i class='bx bx-loader-alt bx-spin'></i>`;
        btn.disabled = true;
    }
 
    navigator.geolocation.getCurrentPosition(
        // Éxito
        function(position) {
            userLocation = L.latLng(position.coords.latitude, position.coords.longitude);
            map.setView(userLocation, 15);
 
            // Eliminar marcador anterior si existe
            if (userMarker) map.removeLayer(userMarker);
 
            userMarker = L.marker(userLocation, { icon: userIcon })
                .addTo(map)
                .bindPopup('<b>📍 Tu ubicación actual</b>')
                .openPopup();
 
            if (btn) {
                btn.innerHTML = `<i class='bx bxs-navigation'></i>`;
                btn.disabled = false;
            }
 
            if (callback) callback(userLocation);
        },
        // Error
        function(error) {
            console.warn("No se pudo obtener la ubicación:", error.message);
            userLocation = L.latLng(FALLBACK_LAT, FALLBACK_LNG);
            showFallbackMarker();
            showLocationError(error);
 
            if (btn) {
                btn.innerHTML = `<i class='bx bxs-navigation'></i>`;
                btn.disabled = false;
            }
 
            if (callback) callback(userLocation);
        },
        // Opciones
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 60000
        }
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
        1: "Permiso de ubicación denegado. Usando el centro de Motril como punto de inicio.",
        2: "No se pudo detectar tu posición. Usando el centro de Motril.",
        3: "Tiempo de espera agotado. Usando el centro de Motril."
    };
    const msg = messages[error.code] || "Error de ubicación desconocido.";
 
    const toast = document.getElementById('locationToast');
    if (toast) {
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
    }
}
 
/**
 * Traza la ruta desde la ubicación del usuario hasta el destino.
 */
function drawRoute(originLatLng, destinationLatLng, restaurantName, restaurantAddress) {
    // Limpiar ruta anterior
    if (currentRoutingControl) {
        map.removeControl(currentRoutingControl);
        currentRoutingControl = null;
    }
 
    // Marcador de destino
    L.marker(destinationLatLng, { icon: destIcon })
        .addTo(map)
        .bindPopup(`<b>🍽️ ${restaurantName}</b><br><small>${restaurantAddress || ''}</small>`)
        .openPopup();
 
    // Trazar ruta
    currentRoutingControl = L.Routing.control({
        waypoints: [originLatLng, destinationLatLng],
        routeWhileDragging: false,
        show: false,
        addWaypoints: false,
        fitSelectedRoutes: true,
        lineOptions: {
            styles: [{ color: '#4A90E2', weight: 5, opacity: 0.8 }]
        },
        createMarker: function() { return null; } // Usamos nuestros propios marcadores
    }).addTo(map);
}
 
/**
 * Carga las rutas activas del usuario y las muestra en el mapa.
 */
async function loadActiveRoutesOnMap() {
    if (!currentUser) return;
 
    try {
        const activeRes = await fetch(API_BASE + 'user.php?action=get_active_routes&user_id=' + currentUser.id);
        const activeData = await activeRes.json();
 
        // Limpiar ruta anterior si existe
        if (currentRoutingControl) {
            map.removeControl(currentRoutingControl);
            currentRoutingControl = null;
        }
 
        if (activeData.status === 'success' && activeData.data.length > 0) {
            const activeRouteId = activeData.data[0].id;
 
            const routesRes = await fetch(API_BASE + 'routes.php?action=list');
            const routesData = await routesRes.json();
 
            if (routesData.status === 'success') {
                const routeDetails = routesData.data.find(r => r.id == activeRouteId);
 
                if (routeDetails && routeDetails.restaurants && routeDetails.restaurants.length > 0) {
                    const restaurant = routeDetails.restaurants[0];
 
                    if (restaurant.lat && restaurant.lng) {
                        const destination = L.latLng(parseFloat(restaurant.lat), parseFloat(restaurant.lng));
 
                        // Obtener ubicación del usuario y luego trazar la ruta
                        getUserLocation(function(origin) {
                            drawRoute(origin, destination, restaurant.name, restaurant.address);
                        });
                    }
                }
            }
        } else {
            // Sin rutas activas: solo mostrar ubicación del usuario
            getUserLocation(null);
        }
    } catch (error) {
        console.error("Error cargando rutas en el mapa:", error);
        getUserLocation(null);
    }
}
 
// Inicialización: obtener ubicación al cargar la página
document.addEventListener('DOMContentLoaded', function () {
    // Si no hay usuario logueado, mostramos la ubicación igualmente
    getUserLocation(null);
});