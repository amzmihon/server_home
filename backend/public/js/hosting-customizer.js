/**
 * Hosting Package Customizer
 * Handles package selection, feature customization, and price calculation
 */

class HostingPackageCustomizer {
    constructor(config = {}) {
        this.apiBase = config.apiBase || '/api';
        this.token = config.token || this.getAuthToken();
        this.package = null;
        this.customization = {};
        this.listeners = {};
    }

    /**
     * Get authentication token from meta or localStorage
     */
    getAuthToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.content;
        return localStorage.getItem('auth_token');
    }

    /**
     * Fetch all packages
     */
    async loadPackages() {
        try {
            const response = await fetch(`${this.apiBase}/packages`, {
                headers: this.getHeaders(),
            });
            return await response.json();
        } catch (error) {
            console.error('Failed to load packages:', error);
            this.emit('error', error);
            throw error;
        }
    }

    /**
     * Load specific package details
     */
    async loadPackage(packageId) {
        try {
            const response = await fetch(`${this.apiBase}/packages/${packageId}`, {
                headers: this.getHeaders(),
            });
            const data = await response.json();
            this.package = data.data;
            this.emit('packageLoaded', this.package);
            return this.package;
        } catch (error) {
            console.error('Failed to load package:', error);
            this.emit('error', error);
            throw error;
        }
    }

    /**
     * Update feature customization
     */
    setCustomization(featureId, value) {
        this.customization[featureId] = value;
        this.emit('customizationChanged', this.customization);
    }

    /**
     * Calculate total price with current customization
     */
    async calculatePrice() {
        try {
            const response = await fetch(`${this.apiBase}/checkout/calculate-price`, {
                method: 'POST',
                headers: this.getHeaders(),
                body: JSON.stringify({
                    package_id: this.package.id,
                    customization: this.formatCustomization(),
                }),
            });
            const data = await response.json();
            this.emit('priceUpdated', data.data);
            return data.data;
        } catch (error) {
            console.error('Failed to calculate price:', error);
            this.emit('error', error);
            throw error;
        }
    }

    /**
     * Validate customization against limits
     */
    async validateCustomization() {
        try {
            const response = await fetch(
                `${this.apiBase}/packages/${this.package.id}/validate-customization`,
                {
                    method: 'POST',
                    headers: this.getHeaders(),
                    body: JSON.stringify({
                        customization: this.formatCustomization(),
                    }),
                }
            );
            const data = await response.json();
            if (!data.success) {
                this.emit('validationError', data.errors);
            }
            return data;
        } catch (error) {
            console.error('Validation failed:', error);
            this.emit('error', error);
            throw error;
        }
    }

    /**
     * Process checkout
     */
    async checkout(billingCycle, customFields = {}) {
        try {
            const response = await fetch(`${this.apiBase}/checkout/process`, {
                method: 'POST',
                headers: this.getHeaders(),
                body: JSON.stringify({
                    package_id: this.package.id,
                    customization: this.formatCustomization(),
                    billing_cycle: billingCycle,
                    custom_fields: customFields,
                }),
            });
            const data = await response.json();
            if (data.success) {
                this.emit('checkoutSuccess', data.data);
                return data.data;
            } else {
                this.emit('checkoutError', data.message);
            }
            return data;
        } catch (error) {
            console.error('Checkout failed:', error);
            this.emit('error', error);
            throw error;
        }
    }

    /**
     * Format customization array for API
     */
    formatCustomization() {
        return Object.entries(this.customization).map(([featureId, value]) => ({
            feature_id: parseInt(featureId),
            value: value,
        }));
    }

    /**
     * Get request headers
     */
    getHeaders() {
        return {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${this.token}`,
            'X-Requested-With': 'XMLHttpRequest',
        };
    }

    /**
     * Event listener management
     */
    on(event, callback) {
        if (!this.listeners[event]) {
            this.listeners[event] = [];
        }
        this.listeners[event].push(callback);
    }

    emit(event, data) {
        if (this.listeners[event]) {
            this.listeners[event].forEach((callback) => callback(data));
        }
    }
}

/**
 * Payment processor
 */
class PaymentProcessor {
    constructor(config = {}) {
        this.apiBase = config.apiBase || '/api';
        this.token = config.token || this.getAuthToken();
        this.stripePublicKey = config.stripePublicKey;
        this.stripe = null;
    }

    /**
     * Initialize Stripe
     */
    initializeStripe() {
        if (this.stripePublicKey && typeof Stripe !== 'undefined') {
            this.stripe = Stripe(this.stripePublicKey);
        }
    }

    /**
     * Process Stripe payment
     */
    async processStripePayment(orderId, paymentMethodId) {
        try {
            const response = await fetch(
                `${this.apiBase}/payment/${orderId}/stripe`,
                {
                    method: 'POST',
                    headers: this.getHeaders(),
                    body: JSON.stringify({
                        payment_method: paymentMethodId,
                    }),
                }
            );
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Payment processing failed:', error);
            throw error;
        }
    }

    /**
     * Process bKash payment
     */
    async processBkashPayment(orderId) {
        try {
            const response = await fetch(
                `${this.apiBase}/payment/${orderId}/bkash`,
                {
                    method: 'POST',
                    headers: this.getHeaders(),
                }
            );
            const data = await response.json();
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            }
            return data;
        } catch (error) {
            console.error('bKash payment initialization failed:', error);
            throw error;
        }
    }

    /**
     * Get request headers
     */
    getHeaders() {
        return {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${this.token}`,
            'X-Requested-With': 'XMLHttpRequest',
        };
    }

    /**
     * Get authentication token
     */
    getAuthToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.content;
        return localStorage.getItem('auth_token');
    }
}

// Export for use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        HostingPackageCustomizer,
        PaymentProcessor,
    };
}
