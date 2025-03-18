//=================== Home & Header section ===================//

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
        document.querySelectorAll('.dropdown-menu').forEach((m) => m.classList.remove('show')); 
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

  
document.getElementById('cartIcon').addEventListener('click', function() {
  document.getElementById('cartSidebar').classList.add('open');
});

document.getElementById('closeCart').addEventListener('click', function() {
  document.getElementById('cartSidebar').classList.remove('open');
});









//========================    bangles & bracelets, birthstone jewelry, earings, faqs, necklace and rings =========================//
document.addEventListener("DOMContentLoaded", function () {
    const itemsGrid = document.getElementById("items-grid");
    const items = [
      { name: "Item 1", price: "$99", img: "item1.jpg" },
      { name: "Item 2", price: "$120", img: "item2.jpg" },
      
    ];
  
    items.forEach((item) => {
      const card = document.createElement("div");
      card.classList.add("col-sm-6", "col-md-4", "mb-4");
      card.innerHTML = `
        <div class="card">
          <img src="${item.img}" class="card-img-top" alt="${item.name}">
          <div class="card-body">
            <p class="card-text">${item.name} - ${item.price}</p>
          </div>
        </div>`;
      itemsGrid.appendChild(card);
    });
  });
  







  //===========================   detail page  =========================//

  document.addEventListener("DOMContentLoaded", () => {
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.getElementById('mainImage');

    thumbnails.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            mainImage.src = thumb.src;
        });
    });
});






//==============================   My Address   ==========================//

document.querySelector('form').addEventListener('submit', function(e) {
  e.preventDefault();
  alert('Address updated successfully!');
});

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
      document.querySelectorAll('.dropdown-menu').forEach((m) => m.classList.remove('show')); 
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


document.getElementById('cartIcon').addEventListener('click', function() {
document.getElementById('cartSidebar').classList.add('open');
});

document.getElementById('closeCart').addEventListener('click', function() {
document.getElementById('cartSidebar').classList.remove('open');
});


//chat bot

const orders = [
  { orderId: "12345", email: "user@example.com", details: "Necklace order (Delivered)" },
  { orderId: "67890", email: "test@example.com", details: "Rings order (In Transit)" },
];


function handleMessage() {
  const chatbox = document.getElementById("chatbox");
  const userInput = document.getElementById("userInput");
  const userMessage = userInput.value.trim();

  if (userMessage === "") return;

  
  chatbox.innerHTML += `<div class="message user">${userMessage}</div>`;

  
  if (/track order/i.test(userMessage)) {
    chatbox.innerHTML += `<div class="message bot">Got it! Opening the form to track your order.</div>`;
    new bootstrap.Modal(document.getElementById("orderModal")).show(); // Show modal
  } else {
    chatbox.innerHTML += `<div class="message bot">I didn't understand that. Type "Track Order" to get started.</div>`;
  }

  userInput.value = "";
  chatbox.scrollTop = chatbox.scrollHeight;
}


function submitOrder() {
  const orderId = document.getElementById("orderId").value.trim();
  const email = document.getElementById("email").value.trim();
  const chatbox = document.getElementById("chatbox");

  if (orderId && email) {
    const order = orders.find((o) => o.orderId === orderId && o.email === email);

    if (order) {
      chatbox.innerHTML += `<div class="message bot">Order Details: ${order.details}</div>`;
    } else {
      chatbox.innerHTML += `<div class="message bot">No order found for the provided details. Please check and try again.</div>`;
    }

    document.getElementById("orderForm").reset();
    bootstrap.Modal.getInstance(document.getElementById("orderModal")).hide();
  } else {
    alert("Please fill in both fields.");
  }

  chatbox.scrollTop = chatbox.scrollHeight;
}





//======================================= Checkout page 1 ========================================//


document.querySelector('.btn-dark').addEventListener('click', () => {
  alert('Proceeding to the next step!');
});


const countries = [
  "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia",
  "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus",
  "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil",
  "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cabo Verde", "Cambodia", "Cameroon", "Canada",
  "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo (Congo-Brazzaville)",
  "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czechia (Czech Republic)", "Denmark", "Djibouti", "Dominica",
  "Dominican Republic", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia",
  "Eswatini (fmr. \"Swaziland\")", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia", "Georgia",
  "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti",
  "Holy See", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel",
  "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Kuwait", "Kyrgyzstan",
  "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg",
  "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania",
  "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco",
  "Mozambique", "Myanmar (formerly Burma)", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand",
  "Nicaragua", "Niger", "Nigeria", "North Korea", "North Macedonia", "Norway", "Oman", "Pakistan", "Palau",
  "Palestine State", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal",
  "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines",
  "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone",
  "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Korea", "South Sudan",
  "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria", "Tajikistan", "Tanzania",
  "Thailand", "Timor-Leste", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan",
  "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States of America",
  "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"
];


const countryDropdown = document.querySelector('.form-select');
countries.forEach(country => {
  const option = document.createElement('option');
  option.value = country;
  option.textContent = country;
  countryDropdown.appendChild(option);
});






//========================================== Checkout Page 2 ====================================//

document.getElementById('payment-form').addEventListener('submit', function (event) {
  event.preventDefault();
  alert('Payment Submitted!');
});
document.querySelectorAll('.decrease-quantity').forEach(button => {
  button.addEventListener('click', function () {
      const input = this.nextElementSibling;
      if (input.value > 1) {
          input.value--;
      }
  });
});

document.querySelectorAll('.increase-quantity').forEach(button => {
  button.addEventListener('click', function () {
      const input = this.previousElementSibling;
      input.value++;
  });
});








//============================ Sale Page ==============================//

document.addEventListener("DOMContentLoaded", function () {
  const itemsGrid = document.getElementById("items-grid");
  const items = [
    { name: "Item 1", price: "$99", img: "item1.jpg" },
    { name: "Item 2", price: "$120", img: "item2.jpg" },
   
  ];

  items.forEach((item) => {
    const card = document.createElement("div");
    card.classList.add("col-sm-6", "col-md-4", "mb-4");
    card.innerHTML = `
      <div class="card">
        <img src="${item.img}" class="card-img-top" alt="${item.name}">
        <div class="card-body">
          <p class="card-text">${item.name} - ${item.price}</p>
        </div>
      </div>`;
    itemsGrid.appendChild(card);
  });
});








//================================ Header section  ====================================//


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

document.getElementById('product-images').addEventListener('change', function (event) {
  const previewContainer = document.getElementById('image-preview');
  previewContainer.innerHTML = ""; 

  Array.from(event.target.files).forEach((file, index) => {
      const reader = new FileReader();

      reader.onload = function (e) {
          const previewDiv = document.createElement('div');
          previewDiv.className = "image-item me-3 mb-3";

          previewDiv.innerHTML = `
              <div style="position: relative; display: inline-block;">
                  <img src="${e.target.result}" alt="Uploaded Image" class="rounded border" style="width: 80px; height: 80px; object-fit: cover;">
                  <button type="button" class="btn btn-danger btn-sm delete-btn" style="position: absolute; top: 0; right: 0;">×</button>
                  <button type="button" class="btn btn-warning btn-sm replace-btn" style="position: absolute; bottom: 0; right: 0;">Replace</button>
              </div>
          `;
          previewContainer.appendChild(previewDiv);

          
          previewDiv.querySelector('.delete-btn').addEventListener('click', () => {
              previewDiv.remove();
          });

          
          previewDiv.querySelector('.replace-btn').addEventListener('click', () => {
              const replaceInput = document.createElement('input');
              replaceInput.type = 'file';
              replaceInput.accept = 'image/*';

              replaceInput.addEventListener('change', function () {
                  const newFile = this.files[0];
                  const newReader = new FileReader();
                  newReader.onload = function (newEvent) {
                      previewDiv.querySelector('img').src = newEvent.target.result;
                  };
                  newReader.readAsDataURL(newFile);
              });

              replaceInput.click();
          });
      };

      reader.readAsDataURL(file);
  });
});





