
// Botón principal del menú móvil
const mobileMenuButton = document.getElementById('mobile-menu-button');
const mobileMenu = document.getElementById('mobile-menu');

mobileMenuButton.addEventListener('click', () => {
  mobileMenu.classList.toggle('hidden');
});

// Cerrar menú al hacer clic fuera
document.addEventListener('click', (e) => {
  if (!mobileMenuButton.contains(e.target) && !mobileMenu.contains(e.target)) {
    mobileMenu.classList.add('hidden');
  }
});

// Protege si aún queda JS de submenús
const extintoresButton = document.getElementById('mobile-extintores-button');
const extintoresMenu = document.getElementById('mobile-extintores-menu');

if (extintoresButton && extintoresMenu) {
  extintoresButton.addEventListener('click', () => {
    extintoresMenu.classList.toggle('hidden');
    extintoresButton.querySelector('svg').classList.toggle('rotate-180');
  });
}
