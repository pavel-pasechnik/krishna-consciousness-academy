document.addEventListener('DOMContentLoaded', function () {
  const nav = document.querySelector('.nav-container');

  function updateNavScrollState() {
    requestAnimationFrame(() => {
      if (window.scrollY > 5) {
        nav.classList.add('scrolled');
      } else {
        nav.classList.remove('scrolled');
      }
    });
  }

  // Check immediately upon loading
  updateNavScrollState();

  // We also listen for scrolling
  window.addEventListener('scroll', updateNavScrollState);
});
