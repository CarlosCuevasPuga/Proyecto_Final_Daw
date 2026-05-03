// Inicializar mapa
var map = L.map('map').setView([36.7450, -3.5160], 14);

// Capa base (OpenStreetMap GRATIS)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

// Ruta de ejemplo (Motril)
L.Routing.control({
    waypoints: [
        L.latLng(36.7450, -3.5160),
        L.latLng(36.7465, -3.5175),
        L.latLng(36.7440, -3.5185)
    ],
    routeWhileDragging: false
}).addTo(map);