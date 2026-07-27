document.addEventListener("DOMContentLoaded", () => {
  const slides = document.querySelectorAll(".parallax-slide");
  const sliderContainer = document.querySelector(".parallax-slider");
  if (!sliderContainer || slides.length === 0) return;

  let currentSlide = 0;
  const slideCount = slides.length;
  let autoplayInterval;

  function goToSlide(index) {
    slides[currentSlide].classList.remove("active");
    currentSlide = (index + slideCount) % slideCount;
    slides[currentSlide].classList.add("active");
  }

  function nextSlide() {
    goToSlide(currentSlide + 1);
  }

  function prevSlide() {
    goToSlide(currentSlide - 1);
  }

  // Bind controls if exist
  const nextBtn = document.querySelector(".slider-next");
  const prevBtn = document.querySelector(".slider-prev");

  if (nextBtn) nextBtn.addEventListener("click", () => { nextSlide(); resetAutoplay(); });
  if (prevBtn) prevBtn.addEventListener("click", () => { prevSlide(); resetAutoplay(); });

  function startAutoplay() {
    autoplayInterval = setInterval(nextSlide, 5000); // 5 seconds per slide
  }

  function resetAutoplay() {
    clearInterval(autoplayInterval);
    startAutoplay();
  }

  startAutoplay();

  // Premium 3D Mouse Parallax effect
  const parallaxContainer = document.getElementById("header-parallax-slider");
  if (parallaxContainer) {
    parallaxContainer.addEventListener("mousemove", (e) => {
      const rect = parallaxContainer.getBoundingClientRect();
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      
      const mouseX = e.clientX - rect.left - centerX;
      const mouseY = e.clientY - rect.top - centerY;

      const floatingItems = document.querySelectorAll(".floating-item");
      floatingItems.forEach((item, index) => {
        const speed = parseFloat(item.getAttribute("data-speed")) || 1;
        
        // 3D Translation
        const xOffset = (mouseX / 30) * speed;
        const yOffset = (mouseY / 30) * speed;
        const zOffset = speed * 20; // Faster elements pop out more
        
        // 3D Rotation
        const rotateX = (mouseY / rect.height) * -15 * speed;
        const rotateY = (mouseX / rect.width) * 15 * speed;

        item.style.transform = `perspective(1000px) translate3d(${xOffset}px, ${yOffset}px, ${zOffset}px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
      });
    });

    parallaxContainer.addEventListener("mouseleave", () => {
      const floatingItems = document.querySelectorAll(".floating-item");
      floatingItems.forEach(item => {
        item.style.transform = `perspective(1000px) translate3d(0px, 0px, 0px) rotateX(0deg) rotateY(0deg)`;
      });
    });
  }
});
