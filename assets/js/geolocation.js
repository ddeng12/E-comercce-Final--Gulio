// Geolocation utilities for Gulio app

class GeolocationService {
    constructor() {
        this.currentPosition = null;
        this.watchId = null;
        this.callbacks = [];
    }

    // Get current position
    async getCurrentPosition() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Geolocation not supported'));
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.currentPosition = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        timestamp: position.timestamp
                    };
                    resolve(this.currentPosition);
                },
                (error) => {
                    console.error('Geolocation error:', error);
                    reject(error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000 // 5 minutes
                }
            );
        });
    }

    // Watch position changes
    watchPosition(callback) {
        if (!navigator.geolocation) {
            console.error('Geolocation not supported');
            return;
        }

        this.callbacks.push(callback);

        if (this.watchId === null) {
            this.watchId = navigator.geolocation.watchPosition(
                (position) => {
                    this.currentPosition = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        accuracy: position.coords.accuracy,
                        timestamp: position.timestamp
                    };
                    
                    this.callbacks.forEach(cb => cb(this.currentPosition));
                },
                (error) => {
                    console.error('Geolocation watch error:', error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000 // 1 minute
                }
            );
        }
    }

    // Stop watching position
    stopWatching() {
        if (this.watchId !== null) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }
        this.callbacks = [];
    }

    // Calculate distance between two points
    calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // Earth's radius in kilometers
        const dLat = this.toRadians(lat2 - lat1);
        const dLng = this.toRadians(lng2 - lng1);
        
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(this.toRadians(lat1)) * Math.cos(this.toRadians(lat2)) *
                  Math.sin(dLng / 2) * Math.sin(dLng / 2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    // Convert degrees to radians
    toRadians(degrees) {
        return degrees * (Math.PI / 180);
    }

    // Get address from coordinates (mock implementation)
    async getAddressFromCoordinates(lat, lng) {
        // In a real app, this would use a geocoding service like Google Maps API
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve('Current Location');
            }, 1000);
        });
    }

    // Check if location is in Accra (mock implementation)
    isInAccra(lat, lng) {
        // Accra bounds (approximate)
        const accraBounds = {
            north: 5.7,
            south: 5.4,
            east: -0.1,
            west: -0.3
        };
        
        return lat >= accraBounds.south && 
               lat <= accraBounds.north && 
               lng >= accraBounds.west && 
               lng <= accraBounds.east;
    }

    // Get nearby vendors based on location
    async getNearbyVendors(lat, lng, radius = 5) {
        // This would typically make an API call
        // For now, return mock data
        const vendors = [
            {
                id: 1,
                name: "Kofi's Cuts",
                lat: lat + 0.001,
                lng: lng + 0.001,
                distance: this.calculateDistance(lat, lng, lat + 0.001, lng + 0.001)
            },
            {
                id: 2,
                name: "Mama Ayo Tailors",
                lat: lat - 0.002,
                lng: lng + 0.001,
                distance: this.calculateDistance(lat, lng, lat - 0.002, lng + 0.001)
            }
        ];

        return vendors.filter(vendor => vendor.distance <= radius);
    }
}

// Initialize geolocation service
window.geolocationService = new GeolocationService();

// Utility functions
function formatDistance(distance) {
    if (distance < 1) {
        return Math.round(distance * 1000) + 'm';
    }
    return Math.round(distance * 10) / 10 + 'km';
}

function formatCoordinates(lat, lng) {
    return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GeolocationService;
}

