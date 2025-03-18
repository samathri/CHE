document.addEventListener('DOMContentLoaded', function () {
   
    const isLargeScreen = () => window.innerWidth > 992;
  
    document.querySelectorAll('.dropdown').forEach((dropdown) => {
      dropdown.addEventListener('mouseenter', function () {
        if (isLargeScreen()) {
          const menu = this.querySelector('.dropdown-menu');
          menu.classList.add('show');
        }
      });
  
      dropdown.addEventListener('mouseleave', function () {
        if (isLargeScreen()) {
          const menu = this.querySelector('.dropdown-menu');
          menu.classList.remove('show');
        }
      });
    });
  
    
    document.querySelectorAll('.dropdown-toggle').forEach((toggle) => {
      toggle.addEventListener('click', function (e) {
        e.preventDefault();
        const menu = this.nextElementSibling;
  
        if (isLargeScreen()) return; 
  
        const isOpen = menu.classList.contains('show');
        document.querySelectorAll('.dropdown-menu').forEach((m) => m.classList.remove('show')); // Close others
        if (!isOpen) {
          menu.classList.add('show');
        }
      });
    });
  
   
    document.addEventListener('click', function (e) {
      if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach((menu) => menu.classList.remove('show'));
      }
    });
  });

 
const cartIcons = document.querySelectorAll('#cartIcon');
const cartSidebar = document.getElementById('cartSidebar');
const closeCartBtn = document.getElementById('closeCart');


function openCartSidebar() {
  cartSidebar.style.transform = 'translateX(0)';
}


function closeCartSidebar() {
  cartSidebar.style.transform = 'translateX(100%)';
}


cartIcons.forEach(cartIcon => {
  cartIcon.addEventListener('click', (event) => {
    event.preventDefault(); 
    openCartSidebar();
  });
});


closeCartBtn.addEventListener('click', closeCartSidebar);

window.addEventListener('scroll', () => {
  const cartIcon = document.querySelector('.cart-icon');
  console.log('Cart Icon Position:', cartIcon.getBoundingClientRect());
});



const menuToggleButton = document.getElementById("menuToggle");
if (menuToggleButton) {
  menuToggleButton.addEventListener("click", function () {
    const sidebar = document.getElementById("mobileSidebar");
    if (sidebar) sidebar.classList.add("active"); // Show the sidebar
  });
}


const closeSidebarButton = document.getElementById("closeMobileSidebar");
if (closeSidebarButton) {
  closeSidebarButton.addEventListener("click", function () {
    const sidebar = document.getElementById("mobileSidebar");
    if (sidebar) sidebar.classList.remove("active"); 
  });
}


const mobileMenuItems = document.querySelectorAll(".mobile-menu-item");

mobileMenuItems.forEach(menuItem => {
  const toggleIcon = menuItem.querySelector(".mobile-toggle-icon");
  const subMenu = menuItem.querySelector(".mobile-sub-menu");

  menuItem.addEventListener("click", function (e) {
    e.stopPropagation(); 
    if (subMenu) {
      subMenu.classList.toggle("show"); 
      if (toggleIcon) {
        toggleIcon.classList.toggle("bi-chevron-down");
        toggleIcon.classList.toggle("bi-chevron-up");
      }
    }
  });
});


const mobileSubmenuItems = document.querySelectorAll(".mobile-submenu-item");

mobileSubmenuItems.forEach(submenuItem => {
  const subSubMenu = submenuItem.querySelector(".mobile-sub-sub-menu");
  if (subSubMenu) {
    const toggleIcon = document.createElement("span");
    toggleIcon.classList.add("mobile-toggle-icon", "bi", "bi-plus");
    submenuItem.prepend(toggleIcon); 

    toggleIcon.addEventListener("click", function (e) {
      e.stopPropagation(); 
      subSubMenu.classList.toggle("show"); 
      toggleIcon.classList.toggle("bi-plus");
      toggleIcon.classList.toggle("bi-dash");
    });
  }
});
