// Solar Energy Platform — Main Script
window.SolarCalculatorApp = window.SolarCalculatorApp || {};

SolarCalculatorApp.formatWatts = function(w) { return w >= 1000 ? (w/1000).toFixed(2) + ' kW' : w + ' W'; };
