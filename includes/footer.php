  <footer>
    <p>&copy; 2026 Drinashop. Tous droits réservés. Paiement à la livraison uniquement.</p>
  </footer>

  <script src="/src/cart.js"></script>
  <script src="/src/parallax.js"></script>
  <script>
      function toggleMobileMenu() {
          const navLinks = document.getElementById('nav-links');
          navLinks.classList.toggle('open');
      }
      
      // Initialize parallax if present
      if (typeof initParallax === 'function') {
          initParallax();
      }
  </script>
</body>
</html>
