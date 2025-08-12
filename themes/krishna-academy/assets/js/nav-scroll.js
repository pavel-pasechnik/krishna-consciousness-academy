document.addEventListener('DOMContentLoaded', function () {
  const nav = document.querySelector('.header-wrapper');
  const mobile = document.querySelector('.wp-block-navigation__responsive-dialog');

  function updateNavScrollState() {
    requestAnimationFrame(() => {
      if (window.scrollY > 5) {
        nav.classList.add('scrolled');
        mobile.classList.add('scrolled');
      } else {
        nav.classList.remove('scrolled');
        mobile.classList.remove('scrolled');
      }
    });
  }

  // Check immediately upon loading
  updateNavScrollState();

  // We also listen for scrolling
  window.addEventListener('scroll', updateNavScrollState);
});
