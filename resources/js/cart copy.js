class ShoppingCart {
    constructor() {
        this.items = this.getCartFromStoreStorage();
        this.SHIPPING_COST = 20000;
        this.init();
    }

    init() {
        if(window.location.pathname.includes('cart')) {
            this.updateCartUi();
        }
        this.updateCartCount();
        this.attachEventListeners();
    }

    getCartFromStoreStorage() {
        try {
            return JSON.parse(localStorage.getItem('shopping_cart'));
        } catch (error) {
            console.error('Error getting cart from localStorage:', error);
            return [];
        }
    }

    saveCartToStoreStorage() {
        try {
            localStorage.setItem('shopping_cart', JSON.stringify(this.items));
            this.updateCartCount();
        } catch (error) {
            console.error(error);
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
            this.updateCartUi();
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
            this.updateCartUi();
            this.showNotification('Item removed from cart');
        } catch (error) {
            console.error('Error removing item from cart:', error);
            this.showNotification('Error removing item from cart. Please try again.', 'error');
        }
    }

    updateQuantity(productId, quantity) {
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
            this.updateCartUi();
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

    calculateTotal() {
        return this.items.reduce((sum, item) => sum + parseFloat(item.price) * item.quantity, 0);
    }
}

export default ShoppingCart;
