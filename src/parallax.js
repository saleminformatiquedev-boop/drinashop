export function initParallax() {
  // Fallback for browsers that don't support scroll-driven animations
  if (!CSS.supports('(animation-timeline: view()) and (animation-range: entry)')) {
    const wrappers = document.querySelectorAll('.parallax-wrapper');

    wrappers.forEach(wrapper => {
      const layers = wrapper.querySelectorAll('.parallax-layer');
      
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            window.addEventListener('scroll', onScroll);
          } else {
            window.removeEventListener('scroll', onScroll);
          }
        });
      }, { threshold: 0 });

      observer.observe(wrapper);

      function onScroll() {
        const scrollY = window.scrollY;
        const wrapperRect = wrapper.getBoundingClientRect();
        const wrapperTop = wrapperRect.top + scrollY;
        const wrapperHeight = wrapperRect.height;
        const windowHeight = window.innerHeight;

        if (scrollY >= wrapperTop - windowHeight && scrollY <= wrapperTop + wrapperHeight) {
          const scrollPercent = (scrollY - (wrapperTop - windowHeight)) / (wrapperHeight + windowHeight);
          
          layers.forEach((layer, index) => {
            const speed = parseFloat(layer.getAttribute('data-speed')) || (0.2 * (index + 1));
            const translateY = (1 - scrollPercent) * 200 * speed;
            layer.style.transform = `translateY(${translateY}px)`;
          });
        }
      }
      
      // Initial trigger
      onScroll();
    });
  }
}
