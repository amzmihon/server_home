// Universal Basket System
class UniversalBasket {
    constructor() {
        this.items = JSON.parse(localStorage.getItem('hostingBasket') || '[]');
        this.init();
    }

    init() {
        this.createBasketUI();
        this.updateBasketCount();
        this.updateItemStates();
    }

    createBasketUI() {
        // Create basket button in navigation
        const nav = document.querySelector('nav .hidden.md\\:flex.items-center, nav .md\\:hidden.flex.items-center');
        if (nav && !document.getElementById('basket-btn')) {
            const basketBtn = document.createElement('div');
            basketBtn.innerHTML = `
                <button id="basket-btn" class="relative bg-green-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-green-700 transition-colors mr-3">
                    🛒 Basket
                    <span id="basket-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                </button>
            `;
            nav.insertBefore(basketBtn, nav.firstChild);
            
            document.getElementById('basket-btn').addEventListener('click', () => this.showBasket());
        }
    }

    addItem(item) {
        const existingIndex = this.items.findIndex(i => i.id === item.id);
        if (existingIndex === -1) {
            this.items.push(item);
            this.saveBasket();
            this.updateBasketCount();
            this.updateItemState(item.id, true);
            return true;
        }
        return false;
    }

    removeItem(itemId) {
        this.items = this.items.filter(item => item.id !== itemId);
        this.saveBasket();
        this.updateBasketCount();
        this.updateItemState(itemId, false);
    }

    isInBasket(itemId) {
        return this.items.some(item => item.id === itemId);
    }

    saveBasket() {
        localStorage.setItem('hostingBasket', JSON.stringify(this.items));
    }

    updateBasketCount() {
        const countEl = document.getElementById('basket-count');
        if (countEl) {
            countEl.textContent = this.items.length;
            countEl.style.display = this.items.length > 0 ? 'flex' : 'none';
        }
    }

    updateItemState(itemId, inBasket) {
        const elements = document.querySelectorAll(`[data-item-id="${itemId}"]`);
        elements.forEach(el => {
            if (inBasket) {
                el.classList.add('in-basket');
                el.style.boxShadow = '0 0 15px rgba(34, 197, 94, 0.5)';
                el.style.borderColor = '#22c55e';
            } else {
                el.classList.remove('in-basket');
                el.style.boxShadow = '';
                el.style.borderColor = '';
            }
        });
    }

    updateItemStates() {
        this.items.forEach(item => this.updateItemState(item.id, true));
    }

    showBasket() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
        modal.innerHTML = `
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-96 overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold">Your Basket</h3>
                        <button class="text-gray-500 hover:text-gray-700" onclick="this.closest('.fixed').remove()">✕</button>
                    </div>
                    <div id="basket-items">
                        ${this.items.length === 0 ? '<p class="text-gray-500 text-center py-8">Your basket is empty</p>' : 
                          this.items.map(item => `
                            <div class="flex justify-between items-center p-3 border-b">
                                <div>
                                    <h4 class="font-medium">${item.name}</h4>
                                    <p class="text-sm text-gray-600">${item.type} - ${item.region}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-bold text-blue-600">${item.price}</span>
                                    <button onclick="basket.removeItem('${item.id}')" class="text-red-500 hover:text-red-700">🗑️</button>
                                </div>
                            </div>
                          `).join('')}
                    </div>
                    ${this.items.length > 0 ? `
                        <div class="mt-4 pt-4 border-t">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-lg font-bold">Total: ${this.calculateTotal()}</span>
                            </div>
                            <button onclick="basket.proceedToPayment()" class="w-full bg-blue-600 text-white py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                                Proceed to Payment
                            </button>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
    }

    calculateTotal() {
        return 'Contact for pricing';
    }

    proceedToPayment() {
        alert('Redirecting to payment gateway...\n\nItems in basket:\n' + 
              this.items.map(item => `• ${item.name} (${item.price})`).join('\n'));
    }

    makeClickable(element, itemData) {
        element.style.cursor = 'pointer';
        element.dataset.itemId = itemData.id;
        
        element.addEventListener('click', (e) => {
            e.preventDefault();
            if (this.isInBasket(itemData.id)) {
                this.removeItem(itemData.id);
            } else {
                this.addItem(itemData);
            }
        });
    }
}

const basket = new UniversalBasket();