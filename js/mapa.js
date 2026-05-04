// Inicializar mapa
var map = L.map('map').setView([36.7450, -3.5160], 14);

// Capa base (OpenStreetMap GRATIS)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

// Variable para guardar el control de rutas actual
var currentRoutingControl = null;

// Añadir un marcador de inicio por defecto (Centro de Motril)
var startMarker = L.marker([36.7450, -3.5160]).addTo(map)
    .bindPopup('<b>Centro de Motril</b><br>Punto de inicio de rutas.')
    .openPopup();

async function loadActiveRoutesOnMap() {
    if (!currentUser) return;

    try {
        // Obtener rutas activas
        const activeRes = await fetch(API_BASE + 'user.php?action=get_active_routes&user_id=' + currentUser.id);
        const activeData = await activeRes.json();
        
        // Limpiar ruta anterior si existe
        if (currentRoutingControl) {
            map.removeControl(currentRoutingControl);
            currentRoutingControl = null;
        }

        if (activeData.status === 'success' && activeData.data.length > 0) {
            // Tomamos la primera ruta activa
            const activeRouteId = activeData.data[0].id;
            
            // Obtener detalles de todas las rutas
            const routesRes = await fetch(API_BASE + 'routes.php?action=list');
            const routesData = await routesRes.json();
            
            if (routesData.status === 'success') {
                const routeDetails = routesData.data.find(r => r.id == activeRouteId);
                
                if (routeDetails && routeDetails.restaurants && routeDetails.restaurants.length > 0) {
                    // Start in Motril center
                    const waypoints = [
                        L.latLng(36.7450, -3.5160)
                    ];
                    
                    // Add first restaurant of the route
                    const restaurant = routeDetails.restaurants[0];
                    if (restaurant.lat && restaurant.lng) {
                        waypoints.push(L.latLng(parseFloat(restaurant.lat), parseFloat(restaurant.lng)));
                        
                        // Opcional: añadir un marcador al restaurante
                        L.marker([parseFloat(restaurant.lat), parseFloat(restaurant.lng)])
                            .addTo(map)
                            .bindPopup(`<b>${restaurant.name}</b><br>${restaurant.address}`);
                    }

                    currentRoutingControl = L.Routing.control({
                        waypoints: waypoints,
                        routeWhileDragging: false,
                        show: false, // Ocultar el panel de texto para móvil
                        createMarker: function() { return null; } // Desactivar marcadores por defecto de routing
                    }).addTo(map);
                }
            }
        }
    } catch (error) {
        console.error("Error loading routes on map:", error);
    }
}