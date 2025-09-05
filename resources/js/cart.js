class ShoppingCart {
    constructor() {
        this.items = this.getCartFromStorage();
        this.SHIPPING_COST = 20000;
        this.init();
    }

    init() {
        if(window.location.pathname.includes('cart')) {
            this.updateCartUI();
        }
        this.updateCartCount();
        this.attachEventListeners();
    }

    getCartFromStorage() {
        try {
            return JSON.parse(localStorage.getItem('shopping_cart')) || [];
        } catch {
            console.error('Error reading cart from localStorage');
            return [];
        }
    }

    saveCartToStorage() {
        try {
            localStorage.setItem('shopping_cart', JSON.stringify(this.items));
            this.updateCartCount();
        } catch (error) {
            console.error("Error saving cart to localStorage", error);
            this.showNotification('Error saving cart data. Please try again.', 'error');    
        }
    }

    updateCartCount() {
        const cartCount = document.getElementById('cart-count');
        if (!cartCount) return;

        const totalItems = this.items.reduce((sum, item) => sum + item.quantity, 0);
        cartCount.textContent = totalItems;
        cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
    }

    addItem(product) {
        if (!product?.id) return;

        try {
            const existingItem = this.items.find(item => item.id === parseInt(product.id));

            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                this.items.push({ id: parseInt(product.id), name: product.name, price: parseFloat(product.price), image: product.image, category: product.category_name, quantity: 1 });
            }
            this.saveCartToStorage();
            this.updateCartUI();
            this.showNotification('Item added to cart');
        } catch (error) {
            console.error('Error adding item to cart:', error);
            this.showNotification('Error adding item to cart. Please try again.', 'error');
        }
    }

    removeItem(productId) {
        try {
            this.items = this.items.filter(item => item.id !== parseInt(productId));
            this.saveCartToStorage();
            this.updateCartUI();
            this.showNotification('Item removed from cart');
        } catch (error) {
            console.error('Error removing item from cart:', error);
            this.showNotification('Error removing item from cart. Please try again.', 'error');
        }
    }

    updateQuantity(productId, changeAmount) {
        try {
            const item = this.items.find(item => item.id === parseInt(productId));
            if (!item) return;

            const newQuantity = item.quantity + changeAmount;
            if (newQuantity < 1) {
                this.removeItem(productId);
                return;
            }
            item.quantity = newQuantity;
            this.saveCartToStorage();
            this.updateCartUI();
        } catch (error) {
            console.error('Error updating quantity:', error);
            this.showNotification('Error updating item quantity. Please try again.', 'error');
        }
    }

    formatPrice(price) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price);
    }

    calculateSubtotal() {
        return this.items.reduce((total, item) => total + parseFloat(item.price) * item.quantity, 0);
    }

    updateOrderSummary(subtotal, shipping) {
        const elements = {
            subtotal: document.querySelector('[data-summary="subtotal"]'),
            shipping: document.querySelector('[data-summary="shipping"]'),
            total: document.querySelector('[data-summary="total"]'),
            checkout: document.querySelector('[data-action="checkout"]'),
        }

        if (elements.subtotal) elements.subtotal.textContent = this.formatPrice(subtotal);
        if (elements.shipping) elements.shipping.textContent = this.formatPrice(shipping);
        if (elements.total) elements.total.textContent = this.formatPrice(subtotal + shipping);

        if (elements.checkout) {
            const isDisabled = subtotal === 0;
            elements.checkout.disabled = isDisabled;
            elements.checkout.className = isDisabled
                ? 'mt-6 w-full bg-gray-300 cursor-not-allowed text-white py-3 px-4 rounded-lg'
                : 'mt-6 w-full bg-emerald-600 hover:bg-emerald-700 text-white py-3 px-4 rounded-lg';
        }
    }

    createCartItemElement(item) {
        const div = document.createElement('div');
        div.className = 'p-6 border-b border-gray-200 flex items-center';
        div.innerHTML = `
            <div class="flex items-center">
                <img src="${item.image}" alt="${item.name}"
                    class="w-16 h-16 object-cover rounded mr-4">
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-medium text-gray-900">${item.name}</h3>
                    <p class="mt-1 text-sm text-gray-500">${item.category}</p>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <button type="button" class="quantity-btn p-1 rounded-md bg-gray-200 hover:bg-gray-300" data-action="decrease" data-product-id="${item.id}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                            </button>
                            <span class="mx-2">${item.quantity}</span>
                            <button type="button" class="quantity-btn p-1 rounded-md bg-gray-200 hover:bg-gray-300" data-action="increase" data-product-id="${item.id}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                    </div>
                    <span class="font-medium text-gray-900">${this.formatPrice(item.price * item.quantity)}</span>
                    </div>
                    </div>
                    <button type="button" class="remove-item ml-4 text-gray-400 hover:text-red-500" data-action="remove" data-product-id="${item.id}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3" />
                        </svg>
                    </button>
                    </div>
        `;
        this.attachItemEventListeners(div, item.id);
        return div;
    }

    updateCartUI() {
       const cartContainer = document.getElementById('cart-items');
       if (!cartContainer) return;

       cartContainer.innerHTML = '';

       if (this.items.length === 0) {
        cartContainer.innerHTML = `
            <div class="text-center py-6 text-gray-500">
                <p class="mb-4">Your cart is empty.</p>
                <a href="/" class="text-emerald-600 hover:text-emerald-700">Continue Shopping</a>
            </div>
        `;
        this.updateOrderSummary(0, 0);
        return;
       }

       const cartContent = document.createElement('div');
       cartContent.className = 'cart-content';

       this.items.forEach(item => {
        cartContent.appendChild(this.createCartItemElement(item));
       });

       if (document.querySelector('[data-logged-in="true"]')) {
        const shippingForm = this.createShippingForm();
        shippingForm.classList.add('hidden');
        cartContent.appendChild(shippingForm);
       }

       cartContainer.appendChild(cartContent);

       const subtotal = this.calculateSubtotal();
       this.updateOrderSummary(subtotal, this.SHIPPING_COST);
    }

    attachItemEventListeners(element, productId) {
        element.addEventListener('click', (e) => {
            const target = e.target.closest('[data-action]');
            if (!target) return;

            e.preventDefault();
            const action = target.dataset.action;
            
            switch (action) {
                case 'decrease':
                    this.updateQuantity(productId, -1);
                    break;
                case 'increase':
                    this.updateQuantity(productId, 1);
                    break;
                case 'remove':
                    this.removeItem(productId);
                    break;
            }
        });
    }

    attachEventListeners() {
        document.addEventListener('click', (e) => {
            const addToCartButton = e.target.closest('.add-to-cart');
            if (!addToCartButton) return;

            e.preventDefault();
            const productCard = addToCartButton.closest('.product-card');
            if (!productCard) return;

            const product = {
                id: productCard.dataset.id,
                name: productCard.dataset.name,
                price: productCard.dataset.price,
                image: productCard.dataset.image,
                category_name: productCard.dataset.category
            };
            this.addItem(product);
        });

        const checkoutButton = document.getElementById('checkout-button');
        if (checkoutButton) {
            checkoutButton.addEventListener('click', (e) => {
                e.preventDefault();
                if (this.items.length === 0) return;

                const shippingForm = document.getElementById('shippingForm');
                if (!shippingForm) return;

                shippingForm.classList.remove('hidden');
                checkoutButton.classList.add('hidden');
                const payButton = document.getElementById('payButton');
                if (payButton) payButton.classList.remove('hidden');
            });
        }

        const payButton = document.getElementById('payButton');
        if (payButton) {
            payButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.processPayment();
            });
        }
    }

    async processPayment() {
        const form = document.getElementById('checkoutForm');
        if (!form || !form.checkValidity()) {
            form?.reportValidity();
            return;
        }

        const formdata = new FormData(form);
        const payButton = document.getElementById('payButton');
        if (payButton) {
            payButton.disabled = true;
            payButton.textContent = 'Processing...';
        }
        try {
            const response = await fetch('/checkout/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({
                    name: formdata.get('name'),
                    phone: formdata.get('phone'),
                    shipping_address: formdata.get('shipping_address'),
                    notes: formdata.get('notes'),
                    cart: this.items
                })
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'Failed to process payment');
            }

            const data = await response.json();
            if (!data.status === 'success' || !data.snap_token) {
                throw new Error(data.message || 'Failed to process payment');
            }

            this.handlePayment(data.snap_token, data.order_id, payButton);
        } catch (error) {
            console.error('Payment error:', error);
            this.showNotification(error.message || 'Error processing payment. Please try again.', 'error');
            if (payButton) {
                payButton.disabled = false;
                payButton.textContent = 'Pay Now';
            }
        }
    }
    
    handlePayment(snapToken, orderId, payButton) {
        window.snap.pay(snapToken, {
            onSuccess: async(result) => {
                await this.updateTransactionStatus(orderId, result, 'paid');
                this.items = [];
                this.saveCartToStorage();
                window.location.href = '/orders';
            },
            onPending: async(result) => {
                await this.updateTransactionStatus(orderId, result, 'pending');
                this.items = [];
                this.saveCartToStorage();
                window.location.href = '/orders';
            },
            onError: async(result) => {
                await this.updateTransactionStatus(orderId, result, 'cancelled');
                this.showNotification('Payment failed. Please try again.', 'error');
                if (payButton) {
                    payButton.disabled = false;
                    payButton.textContent = 'Pay Now';
                }
            },
            onClose: () => {
                if (confirm('Are you sure you want to leave the payment page? Your transaction may not be completed.')) {
                    window.location.href = '/orders';
                } else if (payButton) {
                    payButton.disabled = false;
                    payButton.textContent = 'Pay Now';
                }
            }
        });
    }

    async updateTransactionStatus(orderId, result, status) {
        try {
            const response = await fetch('/payments/update-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({ order_id: orderId, transaction_id: result.transaction_id, payment_type: result.payment_type, status })
            });
            if (!response.ok) {
                throw new Error('Failed to update transaction status');
            }
            const data = await response.json();
            if (data.status !== 'success') {
                throw new Error(data.message || 'Failed to update transaction status');
            }
        } catch (error) {
            console.error('Error updating transaction status:', error);
            this.showNotification('Error updating transaction status. Please contact support.', 'error');
        }
    }

    createShippingForm() {
        const div = document.createElement('div');
        div.id = 'shippingForm';
        div.className = 'p-6 border-t border-gray-200 mt-4';

        const userData = document.getElementById('user-data');
        const name = userData?.dataset.name || '';
        const phone = userData?.dataset.phone || '';
        const address = userData?.dataset.address || '';

        div.innerHTML = `
        <h2 class="text-lg font-medium text-gray-900">Shipping Information</h2>
        <form id="checkoutForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" value="${name}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" required />
            </div>
            <div>   
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" value="${phone}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" required />
            </div>
            <div>   
                <label class="block text-sm font-medium text-gray-700">Address</label>
                <textarea name="shipping_address" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" required>${address}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Note</label>
                <textarea name="notes" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200" placeholder="Add a note..."></textarea>
            </div>
        </form>
        `;
        return div;
    }

    showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
}

export default ShoppingCart;
