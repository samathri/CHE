// Simulated order data
const orders = [
  { orderId: "12345", email: "user@example.com", details: "Necklace order (Delivered)" },
  { orderId: "67890", email: "test@example.com", details: "Rings order (In Transit)" },
];

// Handle user input in the chatbot
function handleMessage() {
  const chatbox = document.getElementById("chatbox");
  const userInput = document.getElementById("userInput");
  const userMessage = userInput.value.trim();

  if (userMessage === "") return;

  // Add user's message to the chatbox
  chatbox.innerHTML += `<div class="message user">${userMessage}</div>`;

  // Handle the "Track Order" command
  if (/track order/i.test(userMessage)) {
    chatbox.innerHTML += `<div class="message bot">Got it! Opening the form to track your order.</div>`;
    new bootstrap.Modal(document.getElementById("orderModal")).show(); // Show modal
  } else {
    chatbox.innerHTML += `<div class="message bot">I didn't understand that. Type "Track Order" to get started.</div>`;
  }

  userInput.value = "";
  chatbox.scrollTop = chatbox.scrollHeight;
}

// Optionally, add the ability to handle the "Enter" key
document.getElementById("userInput").addEventListener("keydown", function (event) {
  if (event.key === "Enter") {
    handleMessage();
  }
});

// Handle order form submission
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
