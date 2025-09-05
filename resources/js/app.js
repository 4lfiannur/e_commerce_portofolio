import './bootstrap';
import './search';
import 'flowbite';
import shoppingCart from './cart';

document.addEventListener('DOMContentLoaded', () => {
    try {
        window.cart = new shoppingCart();
        console.log('Shopping cart initialized');
    } catch (error) {
        console.log('Error initializing shopping cart:', error);
    } 
});
