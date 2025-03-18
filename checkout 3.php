<?php
include 'php/db.php';
session_start();

$search_query = '';

// Handle AJAX search requests
if (isset($_GET['ajax_search']) && isset($_GET['query'])) {
    $search_query = $conn->real_escape_string($_GET['query']);
    $sql = "
        SELECT * 
        FROM products 
        WHERE productName LIKE '%$search_query%'
        ORDER BY 
            CASE 
                WHEN productName LIKE '$search_query%' THEN 1
                ELSE 2
            END, 
            productName ASC
        LIMIT 5
    ";
    
    $results = $conn->query($sql);
    $products = array();
    
    if ($results->num_rows > 0) {
        while ($row = $results->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($products);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
  if (isset($_SESSION['user_id'])) {
      $user_id = $_SESSION['user_id'];

      // Capture form data
      $firstName = $_POST['firstName'];
      $lastName = $_POST['lastName'];
      $address1 = $_POST['address1'];
      $city = $_POST['city'];
      $zip = $_POST['zip'];

      // Insert or Update the temporary_address table
      $sql = "INSERT INTO temporary_address (user_id, firstName, lastName, address1, city, zip)
              VALUES (?, ?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE
              firstName = VALUES(firstName),
              lastName = VALUES(lastName),
              address1 = VALUES(address1),
              city = VALUES(city),
              zip = VALUES(zip)";
      
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("isssss", $user_id, $firstName, $lastName, $address1, $city, $zip);

      if ($stmt->execute()) {
         
      } else {
          echo "<div class='alert alert-danger'>Error saving details: " . $conn->error . "</div>";
      }
  } else {
      header("Location: login.html");
      exit();
  }
}

// new address



// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  echo "User not logged in.";
  exit();
}

// Get the logged-in user ID
$user_id = $_SESSION['user_id'];

// Fetch details for the specific user
$sql = "SELECT * FROM temporary_address WHERE user_id = ? ORDER BY id DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();



if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Validate and check if form fields exist
  $cardnumber = isset($_POST['cardnumber']) ? trim($_POST['cardnumber']) : null;
  $edate = isset($_POST['edate']) ? trim($_POST['edate']) : null;
  $scode = isset($_POST['scode']) ? trim($_POST['scode']) : null;
  $name = isset($_POST['name']) ? trim($_POST['name']) : null;

  // Validate required fields
  if ($cardnumber && $edate && $scode && $name) {
      // Prepare and bind the SQL statement
      $sql = "INSERT INTO payment_form (cardnumber, edate, scode, name) VALUES (?, ?, ?, ?)";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("ssss", $cardnumber, $edate, $scode, $name);

      if ($stmt->execute()) {
      } else {
          echo "<div class='alert alert-danger'>Error saving payment details: " . $conn->error . "</div>";
      }

      $stmt->close();
  } else {
  }
}
if (isset($_POST['checkout'])) {
  $total = 0;

  // Loop through cart items and insert into the orders table
  if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
      foreach ($_SESSION['cart'] as $item) {
          $product_id = $item['product_id'];
          $product_name = $item['name'];
          $size = $item['size'];
          $quantity = $item['quantity'];
          $price = $item['price'];
          $total_price = $price * $quantity;
          $total += $total_price;

          // Prepare and execute the SQL query
          $stmt = $conn->prepare("INSERT INTO orders (product_id, user_id, product_name, size, quantity, price, total) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
          if ($stmt) {
              $stmt->bind_param("iissidd", $product_id, $user_id, $product_name, $size, $quantity, $price, $total_price);
              if (!$stmt->execute()) {
                  echo "<div class='alert alert-danger'>Error placing order: " . $stmt->error . "</div>";
              }
              $stmt->close();
          } else {
              echo "<div class='alert alert-danger'>Database error: " . $conn->error . "</div>";
          }
      }

      // Clear the cart session
      unset($_SESSION['cart']);
      echo "<script>alert('Order placed successfully!'); window.location.href = 'home.php';</script>";
      exit();
  } else {
      echo "<div class='alert alert-warning'>Your cart is empty!</div>";
  }
}
$conn->close();
  
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap 5 CDN Link -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="header.css">
  <link rel="stylesheet" href="side.css">

    <link rel="stylesheet" href="style.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Sitka&display=swap" rel="stylesheet">
 <!-- Google Font: Lora and Sitka -->
 <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Sitka&display=swap" rel="stylesheet">
 <link href="https://fonts.googleapis.com/css2?family=Bitter:wght@400;700&display=swap" rel="stylesheet">

 <!-- Flag Icon CSS Library for Flags -->
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icon-css/css/flag-icons.min.css">

 <!-- Bootstrap Icons CDN Link -->
 <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

  <body>

    
    <div class="gray-box"> </div>
  
  
  <div id="mobileSidebar" class="mobile-sidebar d-md-none">

    <button id="closeMobileSidebar" class="btn">
      <i class="bi bi-x-lg"></i>
    </button><br>
      
    <div class="mobile-sidebar-header mt-3">
    <div class="search-wrapper d-flex align-items-center">
        <input 
            type="text" 
            class="form-control" 
            placeholder="Search Our Store"
            value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>"
        >
        <button type="button" class="btn btn-warning mobile-search-btn ms-2">
            <i class="bi bi-search"></i>
        </button>
    </div>
</div>
<div class="mobile-sidebar-menu mt-4">
    <ul class="list-unstyled">
        <li><a href="sale2.php" style="text-decoration: none; color: inherit;">Buy On Sale!</a></li>
        <li class="mobile-menu-item">
            <a href="products.php" style="text-decoration: none; color: inherit;">Shop Jewelry</a>
            <i class="bi bi-chevron-down mobile-toggle-icon"></i>
            <ul class="mobile-sub-menu" style="display: none;">
                <li class="mobile-submenu-item">
                    <a href="rings.php" style="text-decoration: none; color: inherit;">Rings</a>
                    <ul class="mobile-sub-sub-menu">
                        <li>Stacking Rings</li>
                        <li>CrissCross, X Rings</li>
                        <li>Chevron, V, U Rings</li>
                        <li>Couple Rings, Wedding Bands</li>
                        <li>Promise, Karma Rings</li>
                    </ul>
                </li>
                <li class="mobile-submenu-item">
                    <a href="earings.php" style="text-decoration: none; color: inherit;">Earrings</a>
                    <ul class="mobile-sub-sub-menu">
                        <li>Tiny Huggie Earrings</li>
                        <li>Bar & Line Earrings</li>
                        <li>Hoop Earrings</li>
                        <li>Stud Earrings</li>
                    </ul>
                </li>
                <li class="mobile-submenu-item">
                    <a href="necklace.php" style="text-decoration: none; color: inherit;">Necklaces</a>
                    <ul class="mobile-sub-sub-menu">
                        <li>Skinny Bar Necklaces</li>
                        <li>Lariat & Y-Style Necklaces</li>
                        <li>Good Luck & Wish Necklaces</li>
                        <li>Circle, Linked & Interlocking Necklaces</li>
                        <li>Layering Chain Necklaces</li>
                    </ul>
                </li>
                <li class="mobile-submenu-item">
                    <a href="bangles and Bracelets.php" style="text-decoration: none; color: inherit;">Bangles and Bracelets</a>
                    <ul class="mobile-sub-sub-menu">
                        <li>Chain Bracelets</li>
                        <li>Charm Bracelets</li>
                        <li>Thin Bangle Bracelets</li>
                        <li>Circle, Linked & Interlocking Bracelets</li>
                    </ul>
                </li>
                <li class="mobile-submenu-item">
                    <a href="Birthstone Jewelry.php" style="text-decoration: none; color: inherit;">Birthstone Jewelry</a>
                    <ul class="mobile-sub-sub-menu">
                        <li>Birthstone Necklaces</li>
                        <li>Birthstone Bracelets</li>
                        <li>Birthstone Bangles</li>
                    </ul>
                </li>
            </ul>
        </li>
        <li class="mobile-menu-item">
            <a href="HWA.php" style="text-decoration: none; color: inherit;">Who We Are</a>
            <i class="bi bi-chevron-down mobile-toggle-icon"></i>
            <ul class="mobile-sub-menu" style="display: none;">
                <li class="mobile-submenu-item">
                    <a href="faqs.php" style="text-decoration: none; color: inherit;">
                        FAQ + How we pay it forward
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</div>
    <div class="mobile-sidebar-footer mt-4">
      <ul class="list-unstyled">
        <li><a href="login.php" style="text-decoration: none; color: inherit;">Login</a></li>
        <li><a href="create-account.php" style="text-decoration: none; color: inherit;">Create Account</a></li>
        <li>Shop in CAD</li>
        <li>Contact: hello@domain.com</li>
        <li>Policies: What You Should Know</li>
      </ul>
    </div>
  </div>
  
  <nav class="navbar navbar-expand-md navbar-light bg-white">
    <div class="container-fluid">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation" id="hamburgerMenuBtn">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <!-- Logo -->
      <a class="navbar-brand mx-auto" href="Home.php">
       <img src="images/logo.svg" alt="Logo" class="mobile-logo">
     </a>
     
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="sale2.php">Buy on Sale!</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link" href="products.php" id="shopJewelry" role="button">Shop Jewelry <i
                class="down-arrow bi bi-caret-down-fill"></i></a>
            <ul class="dropdown-menu" aria-labelledby="shopJewelry">
              <li class="dropdown-submenu">
                <a class="dropdown-item" href="rings.php">Rings <i class="right-arrow bi bi-caret-right-fill"></i></a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#">Stacking Rings</a></li>
                  <li><a class="dropdown-item" href="#">Chevron, V, U Rings</a></li>
                  <li><a class="dropdown-item" href="#">CrissCross, X Rings</a></li>
                  <li><a class="dropdown-item" href="#">Couple Rings, Wedding Bands</a></li>
                  <li><a class="dropdown-item" href="#">Promise, Karma Rings</a></li>
                  <li><a class="dropdown-item" href="#">Mother-Daughter, Sisters & Friendship Rings</a></li>
                </ul>
              </li>
              <li class="dropdown-submenu">
                <a class="dropdown-item" href="earings.php">Earrings<i class="right-arrow bi bi-caret-right-fill"></i></a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#">Tiny Huggie Earrings</a></li>
                  <li><a class="dropdown-item" href="#">Bar & Line Earrings</a></li>
                  <li><a class="dropdown-item" href="#">Hoop Earrings</a></li>
                  <li><a class="dropdown-item" href="#">Stud Earrings</a></li>
                  <li><a class="dropdown-item" href="#">Dangle Earrings</a></li>
                </ul>
              </li>
              <li class="dropdown-submenu">
                <a class="dropdown-item" href="necklace.php">Necklaces<i class="right-arrow bi bi-caret-right-fill"></i></a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#">Skinny Bar Necklaces</a></li>
                  <li><a class="dropdown-item" href="#">Lariat & Y-Style Necklaces</a></li>
                  <li><a class="dropdown-item" href="#">Good Luck & Wish Necklaces</a></li>
                  <li><a class="dropdown-item" href="#">Circle, Linked & Interlocking Necklaces</a></li>
                  <li><a class="dropdown-item" href="#">Layering Chain Necklaces</a></li>
                </ul>
              </li>
              <li class="dropdown-submenu">
                <a class="dropdown-item" href="bangles and Bracelets.php">Bracelets & Bangles<i
                    class="right-arrow bi bi-caret-right-fill"></i></a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#">Chain Bracelets</a></li>
                  <li><a class="dropdown-item" href="#">Charm Bracelets</a></li>
                  <li><a class="dropdown-item" href="#">Thin Bangle Bracelets</a></li>
                  <li><a class="dropdown-item" href="#">Circle, Linked & Interlocking Bracelets</a></li>
                </ul>
              </li>
              <li class="dropdown-submenu">
                <a class="dropdown-item" href="Birthstone Jewelry.php">Birthstone Jewelry<i
                    class="right-arrow bi bi-caret-right-fill"></i></a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#">Birthstone Necklaces</a></li>
                  <li><a class="dropdown-item" href="#">Birthstone Bracelets</a></li>
                  <li><a class="dropdown-item" href="#">Birthstone Bangles</a></li>
                </ul>
              </li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link" href="HWA.php" id="whoWeAre" role="button">Who We Are<i
                class="down-arrow bi bi-caret-down-fill"></i></a>
            <ul class="dropdown-menu" aria-labelledby="whoWeAre">
              <li><a class="dropdown-item" href="faqs.php">FAQ + How we pay-it-forward ❤️</a></li>
            </ul>
          </li>
        </ul>
        <ul class="icon-list">
          <li class="nav-item-2">
            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#searchModal"><i
                class="bi bi-search"></i></a>
          </li>
          <li class="nav-item-2">
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person me-2"></i> 
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
            </a>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                <li><a class="dropdown-item" href="user_page.php">Profile</a></li>
                <li><a class="dropdown-item" href="./php/logout.php">Logout</a></li>
            </ul>
        </div>
    <?php else: ?>
        <a class="nav-link" href="login.php">
            <i class="bi bi-person"></i>
        </a>
    <?php endif; ?>
</li>
          <li class="nav-item-2">
            <a class="nav-link" href="#" id="cartIcon"><i class="bi bi-cart"></i></a>
          </li>
        </ul>
        
      </div>
      <div class="cart-icon">
        <a class="nav-link" href="#" id="cartIcon"><i class="bi bi-cart"></i></a>
      </div>
    </div>
  </nav>
  
  
    <!-- Cart Sidebar -->
    <div id="cartSidebar" class="sidebar">
      <div class="sidebar-content">
        
        <div class="d-flex justify-content-between align-items-center p-3">
          <h2>Your Cart</h2>
          <button id="closeCart" class="btn">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <hr>
  
        <!-- Cart Items List -->
        <div class="cart-items p-3">
          <div class="cart-item d-flex align-items-center mb-3">
            <img src="images/earing-1.jpg" alt="Item" class="cart-item-img">
            <div class="cart-item-details ms-2">
              <div class="cart-item-name">7th Heaven Classic Arc Earrings</div>
              <div class="cart-item-size">14K Gold-Filled</div>
              <div class="cart-item-quantity d-flex align-items-center">
                <div class="quantity-box d-flex align-items-center border">
                  <button class="btn btn-sm quantity-decrease">-</button>
                  <input type="number" class="form-control form-control-sm text-center quantity-input" value="1" min="1">
                  <button class="btn btn-sm quantity-increase">+</button>
                </div>
                <div class="cart-item-price ms-auto">Rs 11,300.00</div>
              </div>
  
            </div>
  
          </div>
  
          
        </div>
        <hr>
  

        <div class="special-instructions p-3">
          <label for="instructions" class="form-label instructions">Special instructions for seller</label>
          <textarea id="instructions" class="form-control" rows="4"></textarea>
        </div>
  
  
  

        <div class="cart-footer">
        <hr>
          <div class="d-flex justify-content-between">
            <h3>SUBTOTAL</h3>
            <h3>Rs 11,300.00</h3>
          </div>
          <p>Taxes and <a href="" class="shipping">shipping</a> calculated at checkout</p>
          <button class="btn btn-warning w-100 mt-3">
            CHECKOUT <i class="bi bi-arrow-right ms-2"></i>
          </button>
        </div>
      </div>
    </div>

    
<!-- Progress Bar Container -->
<div class="container">
  <div class="progress-container px-2">
      <div class="progress-bar-steps d-flex justify-content-between flex-wrap">
          <!-- Step 1 -->
          <div class="step text-center">
              <div class="circle">1</div>
              <div class="step-name">Product Details</div>
          </div>
          <!-- Step 2 -->
          <div class="step text-center">
              <div class="circle">2</div>
              <div class="step-name">Delivery Details</div>
          </div>
          <!-- Step 3 -->
          <div class="step active text-center">
              <div class="circle">3</div>
              <div class="step-name">Payment</div>
          </div>
      </div>
  </div>
</div>

    <div class="container my-5">
     <h4 class="mb-4">Order Summary</h4>
     <div class="d-flex flex-column flex-md-row align-items-center justify-content-between mb-4">
     <div class="product d-flex align-items-center gap-3">

        <div class="subtotal-container">
        <?php


// Update quantity via AJAX
if (isset($_POST['action']) && $_POST['action'] == 'update_quantity') {
    $index = (int)$_POST['index'];
    $quantity = (int)$_POST['quantity'];

    if (isset($_SESSION['cart'][$index])) {
        $_SESSION['cart'][$index]['quantity'] = $quantity;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
    }
    exit();
}



if (isset($_POST['checkout'])) {
    $user_id = $_SESSION['user_id'];  // Assuming the user is logged in and you have the user ID
    $total = 0;
    
    // Loop through cart items and insert into orders table
    foreach ($_SESSION['cart'] as $item) {
      $product_id = $item['product_id'];
      $product_name = $item['name'];
      $size = $item['size'];
      $quantity = $item['quantity'];
      $price = $item['price'];
      $total_price = $price * $quantity;
      $total += $total_price;
      $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;


      // Insert into the orders table
      $stmt = $conn->prepare("INSERT INTO orders (product_id,user_id, product_name, size, quantity, price, total) 
                              VALUES (?,?,?, ?, ?, ?, ?)");
      $stmt->bind_param("iissidd", $product_id,$user_id, $product_name, $size, $quantity, $price, $total_price);
      $stmt->execute();
    }
  
    // Clear the cart session after checkout
    unset($_SESSION['cart']);
    echo "<script>alert('Order placed successfully!'); window.location.href = 'home.php';</script>";
    exit();
  }
?>

 
    <style>
  .checkout-btn {
            background-color: #d4af37;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            width: 100%;
            margin-top: 20px;
        }
  </style>

    <div>
        <div class="sidebar-content">
        <div class="d-flex justify-content-between align-items-center p-3">
     
    </div>
            
            <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                <?php
                $totalamount = 0;
                foreach ($_SESSION['cart'] as $index => $item):
                    $item_total = floatval($item['price']) * intval($item['quantity']);
                    $totalamount += $item_total;
                ?>
                <div class="cart-item d-flex align-items-center mb-3">
                    <img src="<?php echo $item['image']; ?>" alt="" class="img-thumbnail" style="width: 100px;">
                    <div class="cart-item-details ms-2">
                        <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="cart-item-quantity d-flex align-items-center">
                            <div class="quantity-box d-flex align-items-center border">
                               
                            </div>
                            <div class="cart-item-price ms-auto">Rs <?php echo number_format($item_total, 2); ?></div>
                        </div>
                        
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="subtotal">
                <p></p>
                <p>                <h3>Total: Rs <span id="cart-total"><?php echo number_format($totalamount, 2); ?></span></h3>
                </p>
                
            </div>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
            

        </div>
    </div>

    <script>
    $(document).ready(function () {
        // Update total price
        function updateTotalPrice() {
            let totalamount = 0;
            $(".cart-item").each(function () {
                const quantity = parseInt($(this).find(".quantity-input").val(), 10) || 0;
                const unitPrice = parseFloat($(this).find(".quantity-decrease").data("unit-price")) || 0;
                totalamount += quantity * unitPrice;
            });
            $("#cart-total").text(totalamount.toFixed(2));
        }

        // Handle increase and decrease buttons
        
    });
    </script>

            <h4  class="mb-4">Address Summary</h4>
            
                        <?php 
                        
                        while($row = mysqli_fetch_assoc($result))
                        {
                            ?>
            <div class="subtotal">
                <p>First Name</p>
                <p><?php echo $row['firstName']; ?></p>
            </div>
            <div class="subtotal">
                <p>Last Name</p>
                <p><?php echo $row['lastName'];?></p>
            </div>
            <div class="subtotal">
                <p>Address</p>
                <p><?php echo $row['address1']; ?></p>
            </div>
            <div class="subtotal">
                <p>City</p>
                <p><?php echo $row['city']; ?></p>
            </div>
            <div class="subtotal">
                <p>Zip</p>
                <p><?php echo $row['zip']; ?></p>
            </div>
            <?php
                        }

?>
        </div>

        
        
    
       
    </div>

    
   
   
  </div>
        
        <div class="payment-options mb-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mb-3">
                <button class="btn btn-dark w-100 mb-2 mb-sm-0 me-sm-2">G Pay</button>
                <button class="btn btn-outline-secondary w-100 ms-sm-2">Pay with Card</button>
               
            </div>
        </div>
        
        <hr class="line-between">
        <h4> OR </h4>
        <h4 class="mb-4">Payment</h4>
        <p> All transactions are secure and encrypted </p>

        <div class="card-details border p-3">

            <div class="payment-method">

            <h5 class="fw-bold"> Credit / Debit Card </h5>
            <div class="payment-icon">
                <img src="images/visa.svg" alt="Visa">
              </div>
            <div class="payment-icon">
                <img src="images/master.svg" alt="MasterCard">
              </div>
            <div class="payment-icon">
                <img src="images/venmo.svg" alt="venmo">
              </div>
            <div class="payment-icon">
                <img src="images/discover.png" alt="Discover">
              </div>
              </div>
            
              <form id="payment-form" method="POST" action="">
    <div class="mb-3">
        <label for="card-number" class="form-label">Card Number</label>
        <input type="text" class="form-control" id="card-number" name="cardnumber" placeholder="1234 5678 9012 3456" required>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label for="expiration-date" class="form-label">Expiration Date (MM/YY)</label>
            <input type="text" class="form-control" id="expiration-date" name="edate" placeholder="MM/YY" required>
        </div>
        <div class="col-md-6">
            <label for="security-code" class="form-label">Security Code</label>
            <input type="text" class="form-control" id="security-code" name="scode" placeholder="CVV" required>
        </div>
    </div>
    <div class="mb-3">
        <label for="card-name" class="form-label">Name on Card</label>
        <input type="text" class="form-control" id="card-name" name="name" placeholder="Full Name" required>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="billing-same">
        <label class="form-check-label" for="billing-same">Use shipping address as billing address</label>
    </div>
    <button type="submit" class="btn btn-dark w-100">Pay Now</button>
</form>
       
<form method="POST" action="">
        <button type="submit" class="checkout-btn" name="checkout">Proceed to Checkout</button>
      </form>


 

<!-- Footer Section -->
  <footer class="footer">
    <div class="container-fluid">
      <div class="row d-flex justify-content-between ">
        
        <div class="footer-left d-flex ">
          <ul class="list-unstyled m-0 align">
            <li><a href="#">Shop in CAD <span class="flag-icon flag-icon-ca"></span></a></li>
            <li><a href="#">Shop in USD <span class="flag-icon flag-icon-us"></span></a></li>
            <li><a href="mailto:hello@irresistiblyminimal.com">Contact: hello@irresistiblyminimal.com</a></li>
            <li><a href="#">Policies: What you should know.com</a></li>
          </ul>
        </div>

        
        <div class="footer-right text-end">
          <p>&copy; 2024 Irresistibly Minimal</p>

          
          <div class="payment-methods">
            <div class="payment-icon">
              <img src="images/amex.svg" alt="American Express">
            </div>
            <div class="payment-icon">
              <img src="images/apple.svg" alt="Apple Pay">
            </div>
            <div class="payment-icon">
              <img src="images/diners.svg" alt="Diners Club">
            </div>
            <div class="payment-icon">
              <img src="images/discover.png" alt="Discover">
            </div>
            <div class="payment-icon">
              <img src="images/meta pay.svg" alt="Meta">
            </div>
            <div class="payment-icon">
              <img src="images/google pay.svg" alt="Google Pay">
            </div>
            <div class="payment-icon">
              <img src="images/master.svg" alt="MasterCard">
            </div>
            <div class="payment-icon">
              <img src="images/paypal.svg" alt="PayPal">
            </div>
            <div class="payment-icon">
              <img src="images/visa.svg" alt="Visa">
            </div>
            <div class="payment-icon">
              <img src="images/venmo.svg" alt="venmo">
            </div>
            <div class="payment-icon">
              <img src="images/shop.svg" alt="Shop pay">
            </div>

          </div>
        </div>
      </div>
    </div>
  </footer>

  
  <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="search-bar-container">
                    <div class="search-bar">
                        <form action="products.php" method="GET" class="search-box">
                            <input type="text" name="query" class="search-input" 
                                placeholder="Search Our Store" 
                                style="font-family: 'Sitka', serif;">
                            <button type="submit" class="search-icon-box">
                                <i class="bi bi-search search-icon"></i>
                            </button>
                        </form>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="scrips.js"></script>
  <script src="side bar/side bar/script.js"></script>
  


  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>



<script>
  const hamburgerMenuBtn = document.getElementById('hamburgerMenuBtn');
  const mobileSidebar = document.getElementById('mobileSidebar');
  const closeMobileSidebar = document.getElementById('closeMobileSidebar');

  hamburgerMenuBtn.addEventListener('click', () => {
    mobileSidebar.classList.add('active');
  });

  closeMobileSidebar.addEventListener('click', () => {
    mobileSidebar.classList.remove('active');
  });

  
  document.querySelectorAll('.mobile-toggle-icon').forEach(function (icon) {
        icon.addEventListener('click', function () {
            const submenu = this.nextElementSibling;
            if (submenu.style.display === 'none' || submenu.style.display === '') {
                submenu.style.display = 'block'; 
            } else {
                submenu.style.display = 'none'; 
            }
        });
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const searchResults = document.getElementById('searchResults');

    function performSearch() {
        const query = searchInput.value.trim();
        if (query.length > 0) {
            fetch(`${window.location.pathname}?ajax_search=1&query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        const resultsHtml = data.map(product => `
                            <div class="search-result-item">
                                <a href="detail.php?id=${product.id}" class="text-decoration-none">
                                    <div class="d-flex align-items-center p-2">
                                        <img src="${product.productImage}" alt="${product.productName}" style="width: 50px; height: 50px; object-fit: cover;">
                                        <div class="ms-3">
                                            <div class="fw-bold">${product.productName}</div>
                                            <div>${product.productPrice}</div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        `).join('');
                        searchResults.innerHTML = resultsHtml;
                    } else {
                        searchResults.innerHTML = '<p class="text-center">No products found</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    searchResults.innerHTML = '<p class="text-center">Error searching products</p>';
                });
        } else {
            searchResults.innerHTML = '';
        }
    }

    searchButton.addEventListener('click', performSearch);
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            performSearch();
        }
    });

    // Clear results when modal is closed
    const searchModal = document.getElementById('searchModal');
    searchModal.addEventListener('hidden.bs.modal', function () {
        searchResults.innerHTML = '';
        searchInput.value = '';
    });
});
</script>

<!--Mobile search bar-->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileSearchInput = document.querySelector('.mobile-sidebar-header .search-wrapper input');
    const mobileSearchBtn = document.querySelector('.mobile-sidebar-header .mobile-search-btn');
    const mobileSidebar = document.getElementById('mobileSidebar');

    function performMobileSearch() {
        const query = mobileSearchInput?.value?.trim() || '';

        if (mobileSidebar) {
            mobileSidebar.classList.remove('active');
        }

        const urlParams = new URLSearchParams(window.location.search);
        const currentSort = urlParams.get('sort');

        let searchUrl = 'products.php?';
        const params = new URLSearchParams();
        
        if (query) {
            params.append('query', query);
        }
        
        if (currentSort) {
            params.append('sort', currentSort);
        }

        window.location.href = searchUrl + params.toString();
    }

    if (mobileSearchBtn) {
        mobileSearchBtn.addEventListener('click', function(e) {
            e.preventDefault();
            performMobileSearch();
        });
    }

    if (mobileSearchInput) {
        mobileSearchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                performMobileSearch();
            }
        });

        const urlParams = new URLSearchParams(window.location.search);
        const existingQuery = urlParams.get('query');
        if (existingQuery) {
            mobileSearchInput.value = existingQuery;
        }
    }
});
</script>

</body>
</html>
