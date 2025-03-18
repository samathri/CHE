<?php

include './php/db.php';
session_start();
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT * FROM products WHERE id = $product_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    die("Product not found.");
}

	
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $review = $_POST['review'];
    $rating = $_POST['rating'];
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
	   
	   
	
	    $sql = "INSERT INTO review (review,user_id,rating) VALUES ('$review','$user_id','$rating')";
	
	    if ($conn->query($sql) === TRUE) {
	        
	        echo "<script>alert('Review Successful...!!'); window.location.href = '../Macherie/home.php';</script>";
	    } else {
	         }
	
	    $conn->close();
	}
	

?>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_product_id'])) {
    $delete_product_id = (int)$_POST['delete_product_id'];
    $size = $_POST['size'];

    // Check if the cart exists and has products
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        // Loop through the cart items and find the product to delete
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['product_id'] == $delete_product_id && $item['size'] == $size) {
                // Remove the product from the cart
                unset($_SESSION['cart'][$key]);
                // Re-index the array to avoid gaps in the cart
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                break;
            }
        }
    }

    // Redirect back to the cart page after deletion
    echo "<script>window.location.href = 'product-detail.php';</script>";
    exit();
}

// Handle quantity increase/decrease
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product_id'])) {
    $product_id = (int)$_POST['update_product_id'];
    $size = $_POST['size'];
    $action = $_POST['action'];

    // Check if the cart exists and has products
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        // Loop through the cart items and find the product to update
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id && $item['size'] == $size) {
                if ($action == 'increase') {
                    // Increase the quantity
                    $item['quantity']++;
                } elseif ($action == 'decrease' && $item['quantity'] > 1) {
                    // Decrease the quantity (minimum 1)
                    $item['quantity']--;
                }
                break;
            }
        }
        // Re-index the array to avoid gaps
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }

    // Redirect to update the cart
    echo "<script>window.location.href = 'product-detail.php';</script>";
    exit();
}

// If the form is submitted, add the product to the cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['delete_product_id']) && !isset($_POST['update_product_id'])) {
    // Get product details from POST data
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $size = isset($_POST['size']) ? $_POST['size'] : '7'; // Default size 7
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Fetch product details from the database
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();

    if ($product) {
        // Add product to session cart
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if product already exists in the cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id && $item['size'] == $size) {
                // Update the quantity if the product with the same size exists
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        // If the product doesn't exist in the cart, add it
        if (!$found) {
            $_SESSION['cart'][] = [
                'product_id' => $product_id,
                'name' => $product['productName'],
                'price' => $product['productPrice'],
                'quantity' => $quantity,
                'size' => $size,
                'image' => $product['productImage']
            ];
        }

        echo "<script>alert('Product added to cart'); window.location.href = 'product-detail.php';</script>";
        exit();
    }
}




?>

<?php

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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product["productName"]; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Sitka&display=swap" rel="stylesheet">
    <!-- Google Font: Lora and Sitka -->
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Sitka&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Bitter:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Sitka&display=swap" rel="stylesheet">
  <!-- Flag Icon CSS Library for Flags -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icon-css/css/flag-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="header.css">
  <link rel="stylesheet" href="side.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <!-- Bootstrap Icons CDN Link -->
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Sitka&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Bitter:wght@400;700&display=swap" rel="stylesheet">

  <!-- Flag Icon CSS Library for Flags -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icon-css/css/flag-icons.min.css">

  <!-- Bootstrap Icons CDN Link -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<style>
  #star-rating i {
    cursor: pointer;
    color: #ccc; /* Default unselected star color */
    font-size: 14px;
    margin-right: 5px;
}

#star-rating i.selected {
    color: gold; /* Highlighted star color */
}
  </style>

<body>
 

    <div class="gray-box"> </div>
  
  <div id="mobileSidebar" class="mobile-sidebar d-md-none">
    <button id="closeMobileSidebar" class="btn btn-close btn-light"></button>
  
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
        <a class="nav-link" href="login.html">
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

 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div id="cartSidebar" class="sidebar">
        <div class="sidebar-content">
        <div class="d-flex justify-content-between align-items-center p-3">
      <h2 class="title_mycart" style="color: white">Your Cart</h2>
      <button id="closeCart" class="btn">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
            <hr>
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
                                <button class="btn btn-sm quantity-decrease" data-index="<?php echo $index; ?>" data-unit-price="<?php echo $item['price']; ?>">-</button>
                                <input type="number" class="form-control form-control-sm text-center quantity-input" value="<?php echo $item['quantity']; ?>" min="1" data-index="<?php echo $index; ?>" data-unit-price="<?php echo $item['price']; ?>">
                                <button class="btn btn-sm quantity-increase" data-index="<?php echo $index; ?>" data-unit-price="<?php echo $item['price']; ?>">+</button>
                                <form action="home.php" method="POST" class="d-inline">
                                <input type="hidden" name="delete_product_id" value="<?php echo $item['product_id']; ?>">
                                <input type="hidden" name="size" value="<?php echo $item['size']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fa fa-trash"></i> 
                                </button>
                                </form>
                            </div>
                            <div class="cart-item-price ms-auto">Rs <?php echo number_format($item_total, 2); ?></div>
                        </div>
                        
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="special-instructions p-3">
                <label for="instructions" class="form-label instructions">Special instructions for seller</label>
                <textarea id="instructions" class="form-control" rows="4"></textarea>
                </div>
                <h3>Total: Rs <span id="cart-total"><?php echo number_format($totalamount, 2); ?></span></h3>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
            <form method="POST" action="">
        <button type="submit" class="checkout-btn" name="checkout">Proceed to Checkout</button>
      </form>

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
        $(".quantity-decrease, .quantity-increase").click(function () {
            const isIncrease = $(this).hasClass("quantity-increase");
            const quantityInput = $(this).closest(".quantity-box").find(".quantity-input");
            let quantity = parseInt(quantityInput.val(), 10) || 1;
            const unitPrice = parseFloat($(this).data("unit-price")) || 0;
            const index = $(this).data("index");

            if (isIncrease) {
                quantity++;
            } else if (quantity > 1) {
                quantity--;
            }

            quantityInput.val(quantity);

            // Update item total
            const itemTotal = quantity * unitPrice;
            $(this).closest(".cart-item").find(".cart-item-price").text(`Rs ${itemTotal.toFixed(2)}`);

            // Update cart total
            updateTotalPrice();

            // Send update to server
            $.ajax({
                url: "",
                method: "POST",
                data: {
                    action: "update_quantity",
                    index: index,
                    quantity: quantity
                },
                success: function (response) {
                    const data = JSON.parse(response);
                    if (!data.success) {
                        alert(data.message || "Failed to update cart.");
                    }
                },
                error: function () {
                    alert("Error updating cart.");
                }
            });
        });
    });
    </script>
</body>
</html>
  <?php

// If the form is submitted, add the product to the cart
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get product details from POST data
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $size = isset($_POST['size']) ? $_POST['size'] : '7'; // Default size 7
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Fetch product details from the database
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $result = $conn->query($sql);
    $product = $result->fetch_assoc();

    if ($product) {
        // Add product to session cart
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if product already exists in the cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id && $item['size'] == $size) {
                // Update the quantity if the product with the same size exists
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        // If the product doesn't exist in the cart, add it
        if (!$found) {
            $_SESSION['cart'][] = [
                'product_id' => $product_id,
                'name' => $product['productName'],
                'price' => $product['productPrice'],
                'quantity' => $quantity,
                'size' => $size,
                'image' => $product['productImage']
            ];
        }

        echo "<script>alert('Product added to cart'); window.location.href = 'detail.php';</script>";
        exit();
    }
  }

// Display the cart page
?>
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
  
        <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
      
                <?php
                $total = 0;
                foreach ($_SESSION['cart'] as $item):
                    $item_total = $item['price'] * $item['quantity'];
                    $total += $item_total;
                ?> 
                 <div class="cart-items p-3">
        <div class="cart-item d-flex align-items-center mb-3">
        <img src="<?php echo $item['image']; ?>" alt="" class="img-thumbnail" style="width: 100px;">
          <div class="cart-item-details ms-2">
            <div class="cart-item-name"> <?php echo $item['name']; ?></div>
            <div class="cart-item-size"><?php echo $item['quantity']; ?></div>
            <div class="cart-item-quantity d-flex align-items-center">
              <div class="quantity-box d-flex align-items-center border" >
                <button class="btn btn-sm quantity-decrease" style="background: none !important;">-</button>
                <input type="number" class="form-control form-control-sm text-center quantity-input" value="1" min="1">
                <button class="btn btn-sm quantity-increase" style="background: none !important;">+</button>
              </div>
              <div class="cart-item-price ms-auto">Rs <?php echo number_format($item['price'], 2); ?></div>
            </div>
            <td class="cart-item-actions">
                        <!-- Delete Button -->
                        <form action="product-detail.php" method="POST" class="d-inline">
                            <input type="hidden" name="delete_product_id" value="<?php echo $item['product_id']; ?>">
                            <input type="hidden" name="size" value="<?php echo $item['size']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>

          </div>

        </div>

      </div>
                   
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Total: $<?php echo number_format($total, 2); ?></h3>
        <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
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
          <p>Taxes and <a href="Privacy Policy.php" class="shipping" style="text-decoration: underline;">shipping</a> calculated at checkout</p>
          <a href="product-detail.php">
          <button class="btn btn-warning w-100 mt-3">
            CHECKOUT <i class="bi bi-arrow-right ms-2"></i>
          </button>
          </a>
        </div>
      </div>
    </div>

      <div id="cartSidebar" class="sidebar">
        <div class="sidebar-content">
          <div class="d-flex justify-content-between align-items-center p-3">
            <h2>Your Cart</h2>
            <button id="closeCart" class="btn">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
          <hr>
    
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
      

<form method="POST" action="product-detail.php">
    <div class="container my-5">
        <div class="row">
            <div class="col-md-6">
                <img id="mainImage" src="<?php echo $product["productImage"]; ?>" alt="Product Image" class="img-fluid">
                <div class="d-flex mt-3">
                    <img src="<?php echo $product["subImage1"]; ?>" alt="Thumbnail 1" class="img-thumbnail me-2 thumbnail">
                    <img src="<?php echo $product["subImage2"]; ?>" alt="Thumbnail 2" class="img-thumbnail me-2 thumbnail">
                    <img src="<?php echo $product["subImage3"]; ?>" alt="Thumbnail 3" class="img-thumbnail me-2 thumbnail">
                    <img src="<?php echo $product["productImage"]; ?>" alt="Thumbnail 4" class="img-thumbnail thumbnail">
                </div>
            </div>
    
            <div class="col-md-6">
                <h1 class="product-title"><?php echo $product["productName"]; ?></h1>
                <p class="text-muted"><?php echo $product["productPrice"]; ?><span class="text-danger"><?php echo $product["productDiscount"]; ?></span></p>
                <p class="text-muted"><?php echo $product["productName"]; ?></p>

                <!-- Form to select size and quantity -->
                <label for="size" class="form-label">Choose Your Ring Size</label>
                <select id="size" name="size" class="form-select mb-3">
                    <option value="7">Size 7</option>
                    <option value="8">Size 8</option>
                    <option value="9">Size 9</option>
                </select>

                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" id="quantity" name="quantity" class="form-control mb-3" min="1" value="1">

                <!-- Hidden Product ID -->
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                <!-- Submit Button -->
                <button type="submit" class="btn btn-dark w-100">Add to Cart</button>
            </div>
        </div>
    </div>
</form>

           
           <div class="row">
            <div class="col-6">
              <div class="reviewbox">
                <h5 id="review">
                  <span id="reviewText">Write a Review</span>
                  <i class="fa-solid fa-chevron-down" id="reviewIcon" onclick="toggleForm()"></i>
                </h5>
              
                <div class="review" id="reviewForm" style="display: none;">
    <form action="" method="post">
        <h3 class="mt-2" id="review_title">Submit your Feedback</h3>
        <div class="form-group">
            <label for="experienceRating" class="mt-2" id="text">Rate Your Experience</label>
        </div>
        <div class="form-group mt-2" id="star-rating">
            <!-- Star rating with data attributes -->
            <i class="fa-solid fa-star" data-value="1" onclick="selectRating(1)"></i>
            <i class="fa-solid fa-star" data-value="2" onclick="selectRating(2)"></i>
            <i class="fa-solid fa-star" data-value="3" onclick="selectRating(3)"></i>
            <i class="fa-solid fa-star" data-value="4" onclick="selectRating(4)"></i>
            <i class="fa-solid fa-star" data-value="5" onclick="selectRating(5)"></i>
        </div>
        <!-- Hidden input to store the rating -->
        <input type="hidden" name="rating" id="ratingValue" value="0">

        <div class="form-group mt-2">
            <label for="experienceFeedback" id="text">Write Your Experience</label>
            <textarea
                class="form-control mt-3"
                id="experienceFeedback"
                placeholder="Type Here"
                name="review"
            ></textarea>
        </div>
        <button type="submit" class="btn btn-dark w-100 mt-3">Submit</button>
    </form>
</div>
<script>
function selectRating(rating) {
    // Get all star elements
    const stars = document.querySelectorAll("#star-rating i");

    // Loop through all stars
    stars.forEach((star, index) => {
        if (index < rating) {
            // Highlight stars up to the clicked one
            star.classList.add("selected");
        } else {
            // Remove highlight from other stars
            star.classList.remove("selected");
        }
    });

    // Set the hidden input value to the selected rating
    document.getElementById("ratingValue").value = rating;
}

</script>
              </div>
            </div>
          
              
              <div class="col-6 text-muted" id="description">
                This set of 2 gorgeous genuine 14K Gold-Filled stacking rings can be worn individually,
                together as a set, or mixed and matched, to accompany all the other rings you have! The
                stacking rings are really thin and shiny, so they'll fit right in with whatever you feel like
                wearing, while adding just the right amount of sparkle to your outfit. All this, so you can look
                stunning efforltlessy!
                
              </div>
             </div>
           </div>
            
            
    
            <h2 class="text-center mt-5">You rr hi May Also Like</h2>
            <div class="row mt-3">
                <div class="col-md-4">
                    <img src="images/c.webp" alt="Similar Product 1" class="img-fluid">
                    <p class="text-center">LOVS Unstressed Ring — RS 12,000</p>
                </div>
                <div class="col-md-4">
                    <img src="images/cc2.webp" alt="Similar Product 2" class="img-fluid">
                    <p class="text-center">LOVS Like Roseminded Ring — RS 14,000</p>
                </div>
                <div class="col-md-4">
                    <img src="images/cc4.webp" alt="Similar Product 3" class="img-fluid">
                    <p class="text-center">LOVS United Ring — RS 15,000</p>
                </div>
            </div>
        </div>
    
       
        
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

    document.addEventListener("DOMContentLoaded", () => {
        const thumbnails = document.querySelectorAll('.thumbnail');
        const mainImage = document.getElementById('mainImage');
    
        thumbnails.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                mainImage.src = thumb.src;
            });
        });
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
    
      function toggleForm() {
        const form = document.getElementById('reviewForm');
        const reviewText = document.getElementById('reviewText');
        const reviewIcon = document.getElementById('reviewIcon');
    
        if (form.style.display === 'none' || form.style.display === '') {
          form.style.display = 'block'; 
          reviewText.textContent = 'Cancel'; 
          reviewIcon.classList.replace('fa-chevron-down', 'fa-chevron-up'); 
        } else {
          form.style.display = 'none'; 
          reviewText.textContent = 'Write a Review'; 
          reviewIcon.classList.replace('fa-chevron-up', 'fa-chevron-down'); 
        }
      }

    </script>
    <script src="https://cdn.shopify.com/extensions/10143583-d4b6-4e47-90bd-b281ace5d8bb/inbox-1177/assets/inbox-chat-loader.js" type="text/javascript" defer="defer"></script>

    <script id="web-pixels-manager-setup">(function d(d,e,r,a,n){var o,i,t,s,l=(i=(o={modern:/Edge?\/(1{2}[4-9]|1[2-9]\d|[2-9]\d{2}|\d{4,})\.\d+(\.\d+|)|Firefox\/(1{2}[4-9]|1[2-9]\d|[2-9]\d{2}|\d{4,})\.\d+(\.\d+|)|Chrom(ium|e)\/(9{2}|\d{3,})\.\d+(\.\d+|)|(Maci|X1{2}).+ Version\/(15\.\d+|(1[6-9]|[2-9]\d|\d{3,})\.\d+)([,.]\d+|)( \(\w+\)|)( Mobile\/\w+|) Safari\/|Chrome.+OPR\/(9{2}|\d{3,})\.\d+\.\d+|(CPU[ +]OS|iPhone[ +]OS|CPU[ +]iPhone|CPU IPhone OS|CPU iPad OS)[ +]+(15[._]\d+|(1[6-9]|[2-9]\d|\d{3,})[._]\d+)([._]\d+|)|Android:?[ /-](12[89]|1[3-9]\d|[2-9]\d{2}|\d{4,})(\.\d+|)(\.\d+|)|Android.+Firefox\/(12[7-9]|1[3-9]\d|[2-9]\d{2}|\d{4,})\.\d+(\.\d+|)|Android.+Chrom(ium|e)\/(12[89]|1[3-9]\d|[2-9]\d{2}|\d{4,})\.\d+(\.\d+|)|SamsungBrowser\/([2-9]\d|\d{3,})\.\d+/,legacy:/Edge?\/(1[6-9]|[2-9]\d|\d{3,})\.\d+(\.\d+|)|Firefox\/(5[4-9]|[6-9]\d|\d{3,})\.\d+(\.\d+|)|Chrom(ium|e)\/(5[1-9]|[6-9]\d|\d{3,})\.\d+(\.\d+|)([\d.]+$|.*Safari\/(?![\d.]+ Edge\/[\d.]+$))|(Maci|X1{2}).+ Version\/(10\.\d+|(1[1-9]|[2-9]\d|\d{3,})\.\d+)([,.]\d+|)( \(\w+\)|)( Mobile\/\w+|) Safari\/|Chrome.+OPR\/(3[89]|[4-9]\d|\d{3,})\.\d+\.\d+|(CPU[ +]OS|iPhone[ +]OS|CPU[ +]iPhone|CPU IPhone OS|CPU iPad OS)[ +]+(10[._]\d+|(1[1-9]|[2-9]\d|\d{3,})[._]\d+)([._]\d+|)|Android:?[ /-](12[89]|1[3-9]\d|[2-9]\d{2}|\d{4,})(\.\d+|)(\.\d+|)|Mobile Safari.+OPR\/([89]\d|\d{3,})\.\d+\.\d+|Android.+Firefox\/(12[7-9]|1[3-9]\d|[2-9]\d{2}|\d{4,})\.\d+(\.\d+|)|Android.+Chrom(ium|e)\/(12[89]|1[3-9]\d|[2-9]\d{2}|\d{4,})\.\d+(\.\d+|)|Android.+(UC? ?Browser|UCWEB|U3)[ /]?(15\.([5-9]|\d{2,})|(1[6-9]|[2-9]\d|\d{3,})\.\d+)\.\d+|SamsungBrowser\/(5\.\d+|([6-9]|\d{2,})\.\d+)|Android.+MQ{2}Browser\/(14(\.(9|\d{2,})|)|(1[5-9]|[2-9]\d|\d{3,})(\.\d+|))(\.\d+|)|K[Aa][Ii]OS\/(3\.\d+|([4-9]|\d{2,})\.\d+)(\.\d+|)/}).modern,t=o.legacy,s=navigator.userAgent,i.test(s)?"modern":(t.test(s),"legacy"));window.Shopify=window.Shopify||{};var c=window.Shopify;c.analytics=c.analytics||{};var u=c.analytics;u.replayQueue=[],u.publish=function(d,e,r){return u.replayQueue.push([d,e,r]),!0};try{self.performance.mark("wpm:start")}catch(d){}var h=[r,"/wpm","/b",n,l.substring(0,1),".js"].join("");!function(d){var e=d.src,r=d.async,a=void 0===r||r,n=d.onload,o=d.onerror,i=document.createElement("script"),t=document.head,s=document.body;i.async=a,i.src=e,n&&i.addEventListener("load",n),o&&i.addEventListener("error",o),t?t.appendChild(i):s?s.appendChild(i):console.error("Did not find a head or body element to append the script")}({src:h,async:!0,onload:function(){var r=window.webPixelsManager.init(d);e(r);var a=window.Shopify.analytics;a.replayQueue.forEach((function(d){var e=d[0],a=d[1],n=d[2];r.publishCustomEvent(e,a,n)})),a.replayQueue=[],a.publish=r.publishCustomEvent,a.visitor=r.visitor},onerror:function(){var e=d.storefrontBaseUrl.replace(/\/$/,""),r="".concat(e,"/.well-known/shopify/monorail/unstable/produce_batch"),n=JSON.stringify({metadata:{event_sent_at_ms:(new Date).getTime()},events:[{schema_id:"web_pixels_manager_load/3.1",payload:{version:a||"latest",bundle_target:l,page_url:self.location.href,status:"failed",surface:d.surface,error_msg:"".concat(h," has failed to load")},metadata:{event_created_at_ms:(new Date).getTime()}}]});try{if(self.navigator.sendBeacon.bind(self.navigator)(r,n))return!0}catch(d){}var o=new XMLHttpRequest;try{return o.open("POST",r,!0),o.setRequestHeader("Content-Type","text/plain"),o.send(n),!0}catch(d){console&&console.warn&&console.warn("[Web Pixels Manager] Got an unhandled error while logging a load error.")}return!1}})})({shopId: 24986091617,storefrontBaseUrl: "https://shop.irresistiblyminimal.com",extensionsBaseUrl: "https://extensions.shopifycdn.com/cdn/shopifycloud/web-pixels-manager",surface: "storefront-renderer",enabledBetaFlags: [],webPixelsConfigList: [{"id":"217448545","configuration":"{\"config\":\"{\\\"pixel_id\\\":\\\"AW-697589334\\\",\\\"gtag_events\\\":[{\\\"type\\\":\\\"page_view\\\",\\\"action_label\\\":\\\"AW-697589334\\\/CxaECNCe6LIBENa80cwC\\\"},{\\\"type\\\":\\\"purchase\\\",\\\"action_label\\\":\\\"AW-697589334\\\/UWfjCNOe6LIBENa80cwC\\\"},{\\\"type\\\":\\\"view_item\\\",\\\"action_label\\\":\\\"AW-697589334\\\/pkjQCNae6LIBENa80cwC\\\"},{\\\"type\\\":\\\"add_to_cart\\\",\\\"action_label\\\":\\\"AW-697589334\\\/rYRPCNme6LIBENa80cwC\\\"},{\\\"type\\\":\\\"begin_checkout\\\",\\\"action_label\\\":\\\"AW-697589334\\\/a_hhCNye6LIBENa80cwC\\\"},{\\\"type\\\":\\\"search\\\",\\\"action_label\\\":\\\"AW-697589334\\\/kV0hCN-e6LIBENa80cwC\\\"},{\\\"type\\\":\\\"add_payment_info\\\",\\\"action_label\\\":\\\"AW-697589334\\\/ve8sCOKe6LIBENa80cwC\\\"}],\\\"enable_monitoring_mode\\\":false}\"}","eventPayloadVersion":"v1","runtimeContext":"OPEN","scriptVersion":"afe7c2de16587d6c6689522527d6c67f","type":"APP","apiClientId":1780363,"privacyPurposes":[]},{"id":"64520289","configuration":"{\"pixel_id\":\"411549753103885\",\"pixel_type\":\"facebook_pixel\",\"metaapp_system_user_token\":\"-\"}","eventPayloadVersion":"v1","runtimeContext":"OPEN","scriptVersion":"8d894c63179843e74a9691414b5ad83d","type":"APP","apiClientId":2329312,"privacyPurposes":["ANALYTICS","MARKETING","SALE_OF_DATA"]},{"id":"shopify-app-pixel","configuration":"{}","eventPayloadVersion":"v1","runtimeContext":"STRICT","scriptVersion":"0220","apiClientId":"shopify-pixel","type":"APP","privacyPurposes":["ANALYTICS","MARKETING"]},{"id":"shopify-custom-pixel","eventPayloadVersion":"v1","runtimeContext":"LAX","scriptVersion":"0220","apiClientId":"shopify-pixel","type":"CUSTOM","privacyPurposes":["ANALYTICS","MARKETING"]}],isMerchantRequest: false,initData: {"shop":{"name":"Irresistibly Minimal","paymentSettings":{"currencyCode":"USD"},"myshopifyDomain":"irresistiblyminimal.myshopify.com","countryCode":"US","storefrontUrl":"https:\/\/shop.irresistiblyminimal.com"},"customer":null,"cart":null,"checkout":null,"productVariants":[],"purchasingCompany":null},},function pageEvents(webPixelsManagerAPI) {webPixelsManagerAPI.publish("page_viewed", {});},"https://shop.irresistiblyminimal.com/cdn","1518c2ba4d2b3301a1e3cb6576947ef22edf7bb6","3c762e5aw5b983e43pc2dc4883m545d5a27",);</script>  <script>window.ShopifyAnalytics = window.ShopifyAnalytics || {};
    window.ShopifyAnalytics.meta = window.ShopifyAnalytics.meta || {};
    window.ShopifyAnalytics.meta.currency = 'LKR';
    var meta = {"page":{"pageType":"home"}};
    for (var attr in meta) {
      window.ShopifyAnalytics.meta[attr] = meta[attr];
    }</script>
    
    
    
      
    <div id="shopify-block-15683396631634586217" class="shopify-block shopify-app-block"><script
      id="chat-button-container"
      data-horizontal-position=bottom_right
      data-vertical-position=lowest
      data-icon=chat_bubble
      data-text=no_text
      data-color=#1c1d1d
      data-secondary-color=#FFFFFF
      data-ternary-color=#6A6A6A
      
        data-greeting-message=%E2%81%A3%F0%9F%91%8B+Why%2C+hello+there%21+I%E2%80%99m+Joe%21%E2%A0%80%E2%A0%80%0A%0APlease+feel+free+to+message+me+with+your+questions%2C+and+I%E2%80%99ll+get+back+to+you+as+soon+as+I+can%21+I%E2%80%99m+a+full-time+maker%2C+so+I%E2%80%99ll+typically+reply+and+be+ready+to+chat+in+a+few+hours.%E2%A0%80%0A%0A%E2%9D%95+If+you%27re+going+to+leave+this+page%2C+please+email+me+your+questions+at+hello%40irresistiblyminimal.com+instead.+Speak+soon%21%E2%A0%80%0A%E2%A0%80%0AWarmest%2C%0AJoe.
      
      data-domain=shop.irresistiblyminimal.com
      data-external-identifier=E9Yt0tASO8zbBRHd53anhsNrHKbvZ13SQa4UJZKTbFk
    >
    </script>
    
    <script>(function() {
      function asyncLoad() {
        var urls = ["https:\/\/load.fomo.com\/api\/v1\/-7vYC_lDkTvchmfKVVng5Q\/load.js?shop=irresistiblyminimal.myshopify.com","https:\/\/js.smile.io\/v1\/smile-shopify.js?shop=irresistiblyminimal.myshopify.com","https:\/\/shopify.covet.pics\/covet-pics-widget-inject.js?shop=irresistiblyminimal.myshopify.com"];
        for (var i = 0; i < urls.length; i++) {
          var s = document.createElement('script');
          s.type = 'text/javascript';
          s.async = true;
          s.src = urls[i];
          var x = document.getElementsByTagName('script')[0];
          x.parentNode.insertBefore(s, x);
        }
      };
      if(window.attachEvent) {
        window.attachEvent('onload', asyncLoad);
      } else {
        window.addEventListener('load', asyncLoad, false);
      }
    })();
    
    
    function selectRating(rating) {
    // Get all star elements
    const stars = document.querySelectorAll("#star-rating i");

    // Loop through stars and update classes
    stars.forEach((star, index) => {
        if (index < rating) {
            // Add "selected" class to stars less than or equal to the rating
            star.classList.add("selected");
        } else {
            // Remove "selected" class from other stars
            star.classList.remove("selected");
        }
    });

    // Set the hidden input value to the selected rating
    document.getElementById("ratingValue").value = rating;
}

    
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
    
    <div class="smile-shopify-init"
      data-channel-key="channel_M3MJFKPHS5Wm886LlVqu40eE"
    
    ></div>
    </div>
</body>
</html>
<script>
  function selectRating(rating) {
    const stars = document.querySelectorAll("#star-rating i");
    stars.forEach((star, index) => {
        // Highlight stars up to the clicked one
        if (index < rating) {
            star.classList.add("selected");
        } else {
            star.classList.remove("selected");
        }
    });
    // Update the hidden input field with the selected rating value
    document.getElementById("ratingValue").value = rating;
}

  </script>






