// js/weather.js

// Mock API Call for Weather to avoid exposing real API keys in the code right now
// Ideally, you'd fetch from OpenWeather API: https://api.openweathermap.org/data/2.5/weather?q=Motril&appid=YOUR_API_KEY&units=metric&lang=es

document.addEventListener('DOMContentLoaded', () => {
    fetchWeather();
});

function fetchWeather() {
    const widget = document.getElementById('weatherWidget');
    
    // Simulate API delay
    setTimeout(() => {
        const mockTemp = 24; // Degrees Celsius
        const mockDesc = 'Soleado';
        
        widget.innerHTML = `
            <i class='bx bxs-sun' style='color: #fdcb6e;'></i> 
            <span>${mockTemp}°C, ${mockDesc} en Motril</span>
        `;
    }, 1000);
}
