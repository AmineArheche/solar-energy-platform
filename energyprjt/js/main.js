// Solar Energy Platform — Main Script
window.SolarCalculatorApp = window.SolarCalculatorApp || {};

SolarCalculatorApp.formatWatts = function(w) { return w >= 1000 ? (w/1000).toFixed(2) + ' kW' : w + ' W'; };

SolarCalculatorApp.calculateCO2 = function(kwh) { return Math.round(kwh * 0.70); }; // kg CO2

SolarCalculatorApp.calculateRoofArea = function(panelCount) { return (panelCount * 1.95).toFixed(1); }; // m²

SolarCalculatorApp.isValidEmail = function(email) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email); };

SolarCalculatorApp.isValidPhone = function(phone) { return /^(?:\+212|0)[5-7][0-9]{8}$/.test(phone.replace(/\s+/g, '')); };
