document.addEventListener('DOMContentLoaded', () => {
    fetchWeather();
});

async function fetchWeather() {
    const widget = document.getElementById('weatherWidget');
    
    // Coordenadas de Motril
    const lat = 36.7506;
    const lon = -3.5204;
    const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`;

    try {
        const response = await fetch(url);
        const data = await response.json();
        
        const temp = Math.round(data.current_weather.temperature);
        const code = data.current_weather.weathercode;

        // Mapeo simple de códigos de clima a texto
        const desc = getWeatherDescription(code);

        widget.innerHTML = `
            <i class='bx bxs-cloud' style='color: #fdcb6e;'></i>
            <span>${temp}°C, ${desc} en Motril</span>
        `;
    } catch (error) {
        console.error("Error al obtener el clima:", error);
        widget.innerHTML = `<span>Clima no disponible</span>`;
    }
}

// Función auxiliar para traducir los códigos de Open-Meteo
function getWeatherDescription(code) {
    const codes = {
        0: 'Despejado',
        1: 'Principalmente despejado',
        2: 'Parcialmente nublado',
        3: 'Nublado',
        45: 'Niebla',
        61: 'Lluvia débil',
        95: 'Tormenta'
    };
    return codes[code] || 'Despejado';
}