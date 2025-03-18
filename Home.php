<?php
session_start();
include './php/db.php';

// Fetch All Products
$productlimit = 9;
$sql = "SELECT id, productName, productPrice, productDiscount, productImage FROM products LIMIT $productlimit";
$result = $conn->query($sql);

// Fetch Latest Products
$sql_latest_category = "
    SELECT p.id, p.productName, p.productPrice, p.productDiscount, p.productImage, p.productCategory
    FROM products p
    INNER JOIN (
        SELECT MAX(id) AS latest_id, productCategory
        FROM products
        GROUP BY productCategory
    ) latest_products ON p.id = latest_products.latest_id
    ORDER BY p.productCategory DESC
    LIMIT $productlimit
";
$result_latest_category = $conn->query($sql_latest_category);

// Fetch Latest Reviews
$sql_reviews = "SELECT r.rating, u.full_name, r.review
                FROM review r 
                JOIN user_registration u ON r.user_id = u.id 
                ORDER BY r.id DESC 
                LIMIT 10";
$result_reviews = $conn->query($sql_reviews);

$reviews = array();
if ($result_reviews->num_rows > 0) {
  while ($row = $result_reviews->fetch_assoc()) {
    $reviews[] = $row;
  }
}
?>


<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
  $product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
  $size = isset($_POST['size']) ? $_POST['size'] : '7';
  $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

  $sql = "SELECT * FROM products WHERE id = $product_id";
  $result = $conn->query($sql);
  $product = $result->fetch_assoc();

  if ($product) {
    if (!isset($_SESSION['cart'])) {
      $_SESSION['cart'] = [];
    }

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
      if ($item['product_id'] == $product_id && $item['size'] == $size) {
        $item['quantity'] += $quantity;
        $found = true;
        break;
      }
    }

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

    echo "<script>alert('Product added to cart'); window.location.href = 'mycart2.php';</script>";
    exit();
  }
}

// Handle quantity update
if (isset($_POST['update_cart'])) {
  foreach ($_POST['cartData'] as $data) {
    $_SESSION['cart'][$data['index']]['quantity'] = $data['quantity'];
  }
  echo 'Cart updated successfully!';
  exit();
}

// Handle item deletion
if (isset($_POST['delete_item'])) {
  $index = $_POST['index'];
  unset($_SESSION['cart'][$index]);
  $_SESSION['cart'] = array_values($_SESSION['cart']);
  echo 'Item deleted successfully!';
  exit();
}
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
if (isset($_POST['delete_product_id'])) {
  $product_id = $_POST['delete_product_id'];
  $size = $_POST['size'];

  // Check if the cart is set and contains items
  if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Loop through the cart to find and remove the item
    foreach ($_SESSION['cart'] as $key => $item) {
      if ($item['product_id'] == $product_id && $item['size'] == $size) {
        unset($_SESSION['cart'][$key]);  // Remove the item from the cart
        break;
      }
    }

    // Reindex the cart array to ensure no empty keys remain
    $_SESSION['cart'] = array_values($_SESSION['cart']);
  }
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

    // Insert into the orders table
    $stmt = $conn->prepare("INSERT INTO orders (product_id, product_name, size, quantity, price, total) 
                              VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issidd", $product_id, $product_name, $size, $quantity, $price, $total_price);
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
  <title>Bootstrap Header with Lora Font</title>
  <!-- Bootstrap 5 CDN Link -->
  <link href="//shop.irresistiblyminimal.com/cdn/shop/t/9/assets/timber.scss.css?v=113171219204054844681703145576"
    rel="stylesheet" type="text/css" media="all" />
  <link href="//shop.irresistiblyminimal.com/cdn/shop/t/9/assets/theme.scss.css?v=70137419790146847461703145577"
    rel="stylesheet" type="text/css" media="all" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap 5 CDN Link -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="header.css">
  <link rel="stylesheet" href="style.css">

  <!-- Google Font: Lora and Sitka -->
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Sitka&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Bitter:wght@400;700&display=swap" rel="stylesheet">

  <!-- Flag Icon CSS Library for Flags -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icon-css/css/flag-icons.min.css">

  <!-- Bootstrap Icons CDN Link -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<!-- Google Font: Lora and Sitka -->
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Sitka&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Bitter:wght@400;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Allura&family=Great+Vibes&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Allura&family=Great+Vibes&display=swap"
  rel="stylesheet">

<!-- Flag Icon CSS Library for Flags -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icon-css/css/flag-icons.min.css">

<!-- Bootstrap Icons CDN Link -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<!-- Include Swiper CSS -->
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />

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
</head>

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
          <li class="nav-item nav-desk">
            <a class="nav-link" href="sale2.php">Buy on Sale!</a>
          </li>
          <li class="nav-item dropdown nav-desk">
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
                <a class="dropdown-item" href="earings.php">Earrings<i
                    class="right-arrow bi bi-caret-right-fill"></i></a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#">Tiny Huggie Earrings</a></li>
                  <li><a class="dropdown-item" href="#">Bar & Line Earrings</a></li>
                  <li><a class="dropdown-item" href="#">Hoop Earrings</a></li>
                  <li><a class="dropdown-item" href="#">Stud Earrings</a></li>
                  <li><a class="dropdown-item" href="#">Dangle Earrings</a></li>
                </ul>
              </li>
              <li class="dropdown-submenu">
                <a class="dropdown-item" href="necklace.php">Necklaces<i
                    class="right-arrow bi bi-caret-right-fill"></i></a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#">Skinny Bar Necklaces</a></li>
                  <li><a class="dropdown-item" href="#">Lariat & Y-Style Necklaces</a></li>
                  <li><a class="dropdown-item" href="#">Good Luck & Wish Necklaces</a></li>
                  <li><a class="dropdown-item" href="#">Circle, Linked & Interlocking Necklaces</a></li>
                  <li><a class="dropdown-item" href="#">Layering Chain Necklaces</a></li>
                </ul>
              </li>
              <li class="dropdown-submenu">
                <a class="dropdown-item" href="Bangles and Bracelets.php">Bracelets & Bangles<i
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
          <li class="nav-item dropdown nav-desk">
            <a class="nav-link" href="HWA.php" id="whoWeAre" role="button">Who We Are<i
                class="down-arrow bi bi-caret-down-fill"></i></a>
            <ul class="dropdown-menu" aria-labelledby="whoWeAre">
              <li><a class="dropdown-item" href="faqs.php">FAQ + How we pay-it-forward ❤</a></li>
            </ul>
          </li>
        </ul>
        <ul class="icon-list  nav-desk">
          <li class="nav-item-2">
            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#searchModal"><i
                class="bi bi-search"></i></a>
          </li>
          <li class="nav-item-2">
            <?php if (isset($_SESSION['user_id'])): ?>
              <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" id="dropdownMenuLink"
                  data-bs-toggle="dropdown" aria-expanded="false">
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

  <?php


  // Update quantity via AJAX
  if (isset($_POST['action']) && $_POST['action'] == 'update_quantity') {
    $index = (int) $_POST['index'];
    $quantity = (int) $_POST['quantity'];

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
                    <button class="btn btn-sm quantity-decrease" data-index="<?php echo $index; ?>"
                      data-unit-price="<?php echo $item['price']; ?>">-</button>
                    <input type="number" class="form-control form-control-sm text-center quantity-input"
                      value="<?php echo $item['quantity']; ?>" min="1" data-index="<?php echo $index; ?>"
                      data-unit-price="<?php echo $item['price']; ?>">
                    <button class="btn btn-sm quantity-increase" data-index="<?php echo $index; ?>"
                      data-unit-price="<?php echo $item['price']; ?>">+</button>
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
          <h3 style="color:white">Total: Rs <span id="cart-total"><?php echo number_format($totalamount, 2); ?></span>
          </h3>
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
        $total = 0;
        foreach ($_SESSION['cart'] as $index => $item):
          $item_total = $item['price'] * $item['quantity'];
          $total += $item_total;
          ?>
          <div class="cart-items p-3">
            <div class="cart-item d-flex align-items-center mb-3">
              <img src="<?php echo $item['image']; ?>" alt="" class="img-thumbnail" style="width: 100px;">
              <div class="cart-item-details ms-2">
                <div class="cart-item-name"> <?php echo $item['name']; ?></div>

                <div class="cart-item-quantity d-flex align-items-center">
                  <div class="quantity-box d-flex align-items-center border">
                    <button class="btn btn-sm quantity-decrease" style="background: none !important;"
                      data-index="<?php echo $index; ?>">-</button>
                    <input type="number" class="form-control form-control-sm text-center quantity-input"
                      value="<?php echo $item['quantity']; ?>" min="1" data-index="<?php echo $index; ?>">
                    <button class="btn btn-sm quantity-increase" style="background: none !important;"
                      data-index="<?php echo $index; ?>">+</button>
                  </div>
                  <div class="cart-item-price ms-auto">Rs <?php echo number_format($item['price'], 2); ?></div>
                </div>

                <!-- Delete Button -->
                <form action="" method="POST" class="d-inline">
                  <input type="hidden" name="delete_product_id" value="<?php echo $item['product_id']; ?>">
                  <input type="hidden" name="size" value="<?php echo $item['size']; ?>">
                  <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fa fa-trash"></i> Delete
                  </button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>

        <h3 style="color: white">Total: Rs <?php echo number_format($total, 2); ?></h3>

        <form method="POST" action="">
          <button type="submit" class="checkout-btn" name="checkout">Proceed to Checkout</button>
        </form>

      <?php else: ?>
        <p>Your cart is empty.</p>
      <?php endif; ?>
      <hr>

      <div class="special-instructions p-3">
        <label for="instructions" class="form-label instructions">Special instructions for seller</label>
        <textarea id="instructions" class="form-control" rows="4"></textarea>
      </div>




    </div>
  </div>

  <!-- Home -->



  <section class="text-dark text-center py-5">
    <div class="container">
      <h1 class="display-4 custom-heading">Because Simplicity,</h1>
      <p class="lead custom-paragraph custom-paragraph-1">… is the key to happiness & effortless beauty</p>
      <p class="lead custom-paragraph">Get glamorous <br>with jewelry that works with life!</p>
    </div>
  </section>

  <section id="product-features" style="margin-top: -100px;">
    <div class="container">
      <h2 class="customer-favorites">Start Browsing!</h2>

      <div class="row text-center">
        <!-- First Image in the First Row -->
        <div class="col-md-6" onclick="window.location.href='rings.php';" style="cursor: pointer;">
          <div class="product-box p-4">
            <div class="image-overlay">
              <img src="images/image12.webp" class="product-img">
              <div class="overlay-text">Rings</div>
            </div>
          </div>
        </div>
        <!-- Second Image in the First Row -->
        <div class="col-md-6" onclick="window.location.href='earings.php';" style="cursor: pointer;">
          <div class="product-box p-4">
            <div class="image-overlay">
              <img src="images/image15.webp" class="product-img">
              <div class="overlay-text">Earrings</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Second Row for the next two images -->
      <div class="row text-center">
        <div class="col-md-6" onclick="window.location.href='necklace.php';" style="cursor: pointer;">
          <div class="product-box p-4">
            <div class="image-overlay">
              <img src="images/image17.webp" class="product-img">
              <div class="overlay-text">Necklace</div>
            </div>
          </div>
        </div>
        <div class="col-md-6" onclick="window.location.href='Bangles and Bracelets.php';" style="cursor: pointer;">
          <div class="product-box p-4">
            <div class="image-overlay">
              <img src="images/image18.webp" class="product-img">
              <div class="overlay-text">Bangles</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>


  <section id="features" class="py-5">

    <h2 class="customer-favorites" style="margin-top: -10px;">Shop the customer favorites!</h2>
    <div class="container">
      <div class="row text-center">
        <div class="row mt-4">

          <!-- card 1 -->
          <?php
          if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              $productPrice = $row["productPrice"];
              echo '<div class="col-sm-6 col-md-4 mb-4" id="items-grid">';
              echo '  <div class="card">';
              echo '      <a href="detail.php?id=' . $row["id"] . '">';
              echo '          <img src="' . $row["productImage"] . '" class="card-img-top" alt="' . $row["productName"] . '">';
              echo '      </a>';
              echo '      <div class="card-body">';
              echo '          <p class="card-text">';
              echo '              <span>' . $row["productName"] . '</span> — <span>RS ' . number_format($productPrice, 2) . '</span>';
              echo '          </p>';
              echo '      </div>';
              echo '  </div>';
              echo '</div>';
            }
          } else {
            echo '<p>No products available.</p>';
          }
          ?>
        </div>
      </div>
    </div>
  </section>


  <section class="image-left" style="margin-top: 100px;">

    <div class="p-4 image-and-text-container">
      <div class="image-container">
        <img src="images/image19.jfif" class="image-19">

        <div class="column">
          <img src="images/image20.jfif" class="image-20">
          <img src="images/image21.jfif" class="image-21">
        </div>
      </div>

      <div class="text-content">
        <h2 class="handcraft">100% Handcrafted Jewelry</h2>
        <p class="handcraft1">Why, hello there! My name’s Joe Han Lee and every piece of jewelry from Irresistibly
          Minimal is entirely handcrafted by me, piece-by-piece.</p>
        <p class="handcraft2">I hope that you’ll find your very own go-to everyday favorite piece, and may that very
          special piece be a part of you and a source of everyday happiness to you! 😊</p>
        <p class="handcraft3">Warmest, Joe.</p>

        <a href="Bare.php">

          <button class="btn shop-button"> Shop My Signature Collection ></button>
        </a>
      </div>

    </div>
  </section>


  <section>
    <div class="text-and-image">
      <div class="text-content2">
        <h2 class="Custom-Fitted">Custom-Fitted To You</h2>
        <p class="Custom-Fitted1">Every piece of jewelry at Irresistibly Minimal is entirely handcrafted. Thus, if
          requested, they may be customized to fit you like-a-glove! So, you can fully enjoy life’s moments without
          fussing over uncomfortable jewelry.</p>
        <p class="Custom-Fitted2">The designs are simple, timeless, and chic, so they’ll complement, rather than
          overpower… well, You! 😍</p><br>

        <a href="products.php">
          <button class="btn shop-button">Shop All Jewelry ></button>
        </a>
      </div>
      <div class="image-container1">
        <img src="images/image22.webp" alt="Jewelry Image">
      </div>
    </div>
  </section>


  <section>
    <div class="container">
      <h1 class="display-4 custom-heading"> In with the new, </h1>
      <p class="lead custom-paragraph custom-paragraph-1">… never out with the old! <br> I'm not big on trends so my
        pieces are designed to never go out of style.</p>
      <h2 class="customer-favorites">Shop my latest collection!</h2>
    </div>
  </section>


  <section id="features3" class="py-5">
    <div class="container">
      <div class="row text-center">
        <div class="row mt-4">
          <?php
          if ($result_latest_category->num_rows > 0) {
            while ($row = $result_latest_category->fetch_assoc()) {
              $productPrice = $row["productPrice"];
              echo '<div class="col-sm-6 col-md-4 mb-4" id="items-grid">';
              echo '  <div class="card">';
              echo '      <a href="detail.php?id=' . $row["id"] . '">';
              echo '          <img src="' . $row["productImage"] . '" class="card-img-top" alt="' . $row["productName"] . '">';
              echo '      </a>';
              echo '      <div class="card-body">';
              echo '          <p class="card-text">';
              echo '              <span>' . $row["productName"] . '</span> — <span>RS ' . number_format($productPrice, 2) . '</span>';
              echo '          </p>';
              echo '      </div>';
              echo '  </div>';
              echo '</div>';
            }
          } else {
            echo '<p>No latest products available.</p>';
          }
          ?>



        </div>
      </div>
    </div>
  </section>


  <section class="promotion-section">
    <div class="promo-box">
      <h2 class="promo-heading">SINCE PURE HAPPINESS IS GOLDEN,</h2>
      <p class="promo-text">...and YOU are irresistible!</p>
      <p class="promo-text">Join me, and fellow irresistibles, <br> <br> in our Irresistibles Club</p>
      <p class="promo-text"> & </p>
      <p class="promo-text">GET INSTANT 30% OFF 🎁 <br> <br>your first order now!</p> <br>
    </div>
  </section>

  <h2 class="customer-favorites">Shop my featured collection!</h2>




  <section id="features5" class="py-5">
    <div class="container">
      <div class="row text-center">
        <div class="row mt-4">

          <!-- card 1 -->
          <div class="col-sm-6 col-md-4 mb-4" id="items-grid">
            <div class="card">
              <a href="detail.php">
                <img src="images/ring-1.jpg" class="card-img-top" alt="Item Image">
              </a>
              <div class="card-body">
                <p class="card-text"> <span>Beatitude Rings</span> — <span>Rs 18,600</span></p>
              </div>
            </div>
          </div>

          <!-- card 2 -->
          <div class="col-sm-6 col-md-4 mb-4" id="items-grid">
            <div class="card">
              <a href="detail.php">
                <img src="images/ring-2.jpg" class="card-img-top" alt="Item Image">
              </a>
              <div class="card-body">
                <p class="card-text"><span>Eternal Rings No. VI</span> — <span>Rs 37,000</span></p>
              </div>
            </div>
          </div>

          <!-- card 3 -->
          <div class="col-sm-6 col-md-4 mb-4" id="items-grid">
            <div class="card">
              <a href="detail.php">
                <img src="images/ring-3.jpg" class="card-img-top" alt="Item Image">
              </a>
              <div class="card-body">
                <p class="card-text"><span>Eternal Rings No. V</span> — <span>Rs 35,000.00</span></p>
              </div>
            </div>
          </div>

          <!-- card 4 -->
          <div class="col-sm-6 col-md-4 mb-4" id="items-grid">
            <div class="card">
              <a href="detail.php">
                <img src="images/ring-4.jpg" class="card-img-top" alt="Item Image">
              </a>
              <div class="card-body">
                <p class="card-text"><span>LOVEx Hammered Ring</span> — <span>Rs 13,100</span></p>
              </div>
            </div>
          </div>

          <!-- card 5 -->
          <div class="col-sm-6 col-md-4 mb-4" id="items-grid">
            <div class="card">
              <a href="detail.php">
                <img src="images/ring-5.jpg" class="card-img-top" alt="Item Image">
              </a>
              <div class="card-body">
                <p class="card-text"><span>LOVEx Ring</span> — <span>Rs 16,300</span></p>
              </div>
            </div>
          </div>

          <!-- card 6 -->
          <div class="col-sm-6 col-md-4 mb-4" id="items-grid">
            <div class="card">
              <a href="detail.php">
                <img src="images/ring-6.jpg" class="card-img-top" alt="Item Image">
              </a>
              <div class="card-body">
                <p class="card-text"><span>Marvelous Rings</span> — <span>Rs 38,700</span></p>
              </div>
            </div>
          </div>

          <!-- card 7 -->
          <div class="col-sm-6 col-md-4 mb-4" id="items-grid">
            <div class="card">
              <a href="detail.php">
                <img src="images/ring-7.jpg" class="card-img-top" alt="Item Image">
              </a>
              <div class="card-body">
                <p class="card-text"><span>Love-Charm Ring</span> — <span>Rs 19,300</span></p>
              </div>
            </div>
          </div>

          <!-- card 8 -->
          <div class="col-sm-6 col-md-4 mb-4" id="items-grid">
            <div class="card">
              <a href="detail.php">
                <img src="images/ring-8.jpg" class="card-img-top" alt="Item Image">
              </a>
              <div class="card-body">
                <p class="card-text"><span>Gladness Rings</span> — <span>Rs 13,400</span></p>
              </div>
            </div>
          </div>

          <!-- card 9 -->
          <div class="col-sm-6 col-md-4 mb-4" id="items-grid">
            <div class="card">
              <a href="detail.php">
                <img src="images/ring-9.jpg" class="card-img-top" alt="Item Image">
              </a>
              <div class="card-body">
                <p class="card-text"><span>The Bliss Ring</span> — <span>Rs 8,300</span></p>
              </div>
            </div>
          </div>

          <!-- card 10 -->
          <div class="col-sm-6 col-md-4 mb-4" id="items-grid">
            <div class="card">
              <a href="detail.php">
                <img src="images/ring-10.jpg" class="card-img-top" alt="Item Image">
              </a>
              <div class="card-body">
                <p class="card-text"><span>Eternal Rings No. IV</span> — <span>Rs 35,000</span></p>
              </div>
            </div>
          </div>

          <!-- card 11 -->
          <div class="col-sm-6 col-md-4 mb-4" id="items-grid">
            <div class="card">
              <a href="detail.php">
                <img src="images/ring-11.jpg" class="card-img-top" alt="Item Image">
              </a>
              <div class="card-body">
                <p class="card-text"><span>The Bliss Ring</span> — <span>Rs 8,300</span></p>
              </div>
            </div>
          </div>

          <!-- card 12 -->
          <div class="col-sm-6 col-md-4 mb-4" id="items-grid">
            <div class="card">
              <a href="detail.php">
                <img src="images/ring-12.png" class="card-img-top" alt="Item Image">
              </a>
              <div class="card-body">
                <p class="card-text"><span>Very Tall Mirth Ring</span> — <span>Rs 23,700</span></p>
              </div>
            </div>
          </div>




        </div>
      </div>
    </div>
  </section>

  <section>
    <div class="container">
      <h1 class="display-4 custom-heading">Moments of Joy</h1>
      <p class="lead custom-paragraph custom-paragraph-1"
        style="margin-left: 40px; margin-right: 40px; line-height: 32.96px; font-weight: 400;">
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et
        <br> dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex
        <br> ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu
        fugiat
        <br> nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit"
      </p>
      <!-- Added spacing to improve clarity -->
      <hr style="color: black; width: auto; border: 1px solid black; margin: 20px auto;">
    </div>


    <!-- Testimonials Section -->
    <div class="testimonial-container">
      <div class="swiper testimonials-slider">
        <div class="swiper-wrapper">
          <?php
          if (!empty($reviews)) {
            foreach ($reviews as $review) {
              // Convert rating to stars
              $rating = intval($review['rating']);
              $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);

              // Truncate review to 70 characters
              $truncated_review = mb_substr($review['review'], 0, 147);
              if (strlen($review['review']) > 147) {
                $truncated_review .= '...';
              }
              ?>
              <div class="swiper-slide testimonial">
                <div class="testimonial-content">
                  <div class="testimonial-name">
                    <?php echo htmlspecialchars($review['full_name']); ?>
                  </div>
                  <div class="testimonial-review">
                    <?php echo htmlspecialchars($truncated_review); ?>
                  </div>
                  <div class="stars">
                    <?php echo $stars; ?>
                  </div>
                </div>
              </div>
            <?php
            }
          } else {
            // Fallback for no reviews
            for ($i = 0; $i < 10; $i++) {
              ?>
              <div class="swiper-slide testimonial">
                <div class="testimonial-content">
                  <div class="testimonial-name">Reviewer <?php echo $i + 1; ?></div>
                  <div class="testimonial-review">
                    This is a sample review for item. The content will dynamically adjust..
                  </div>
                  <div class="stars">
                    ★★★★☆
                  </div>
                </div>
              </div>
            <?php
            }
          }
          ?>
        </div>
        <!-- Pagination -->
        <div class="swiper-pagination"></div>
      </div>
    </div>
  </section>


  <section>
    <div class="container">
      <h1 class="display-4 custom-heading"> Like, comment, follow, </h1>
      <p class="lead custom-paragraph custom-paragraph-1"> … and have some fun with all of us on Instagram!<br> We love
        you already! @IrresistiblyMinimal </p>
    </div>
  </section>

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

          <!-- Payment Methods Section -->
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

  <!-- Modal (Search Bar Popup) -->
  <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-body">
          <div class="search-bar-container">
            <div class="search-bar">
              <form action="products.php" method="GET" class="search-box">
                <input type="text" name="query" class="search-input" placeholder="Search Our Store"
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


  <!-- Include Swiper JS -->
  <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>


  <script>
    const triangleDown = document.getElementById('triangleDown');
    const dropdownMenu = document.getElementById('jewelryDropdown');

    triangleDown.addEventListener('click', function () {
      if (dropdownMenu.style.display === "block") {
        dropdownMenu.style.display = "none";
      } else {
        dropdownMenu.style.display = "block";
      }
    });

    document.addEventListener('click', function (event) {
      if (!triangleDown.contains(event.target) && !dropdownMenu.contains(event.target)) {
        dropdownMenu.style.display = "none";
      }
    });



  </script>


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

  <script src="scrips.js"></script>
  <script src="script.js"></script>

  <script
    src="https://cdn.shopify.com/extensions/10143583-d4b6-4e47-90bd-b281ace5d8bb/inbox-1177/assets/inbox-chat-loader.js"
    type="text/javascript" defer="defer"></script>

  <script
    id="web-pixels-manager-setup">(function d(d, e, r, a, n) { var o, i, t, s, l = (i = (o = { modern: /Edge?\/(1{2}[4-9]|1[2-9]\d|[2-9]\d{2}|\d{4,})\.\d+(\.\d+|)|Firefox\/(1{2}[4-9]|1[2-9]\d|[2-9]\d{2}|\d{4,})\.\d+(\.\d+|)|Chrom(ium|e)\/(9{2}|\d{3,})\.\d+(\.\d+|)|(Maci|X1{2}).+ Version\/(15\.\d+|(1[6-9]|[2-9]\d|\d{3,})\.\d+)([,.]\d+|)( \(\w+\)|)( Mobile\/\w+|) Safari\/|Chrome.+OPR\/(9{2}|\d{3,})\.\d+\.\d+|(CPU[ +]OS|iPhone[ +]OS|CPU[ +]iPhone|CPU IPhone OS|CPU iPad OS)[ +]+(15[.]\d+|(1[6-9]|[2-9]\d|\d{3,})[.]\d+)([.]\d+|)|Android:?[ /-](12[89]|1[3-9]\d|[2-9]\d{2}|\d{4,})(\.\d+|)(\.\d+|)|Android.+Firefox\/(12[7-9]|1[3-9]\d|[2-9]\d{2}|\d{4,})\.\d+(\.\d+|)|Android.+Chrom(ium|e)\/(12[89]|1[3-9]\d|[2-9]\d{2}|\d{4,})\.\d+(\.\d+|)|SamsungBrowser\/([2-9]\d|\d{3,})\.\d+/, legacy: /Edge?\/(1[6-9]|[2-9]\d|\d{3,})\.\d+(\.\d+|)|Firefox\/(5[4-9]|[6-9]\d|\d{3,})\.\d+(\.\d+|)|Chrom(ium|e)\/(5[1-9]|[6-9]\d|\d{3,})\.\d+(\.\d+|)([\d.]+$|.*Safari\/(?![\d.]+ Edge\/[\d.]+$))|(Maci|X1{2}).+ Version\/(10\.\d+|(1[1-9]|[2-9]\d|\d{3,})\.\d+)([,.]\d+|)( \(\w+\)|)( Mobile\/\w+|) Safari\/|Chrome.+OPR\/(3[89]|[4-9]\d|\d{3,})\.\d+\.\d+|(CPU[ +]OS|iPhone[ +]OS|CPU[ +]iPhone|CPU IPhone OS|CPU iPad OS)[ +]+(10[.]\d+|(1[1-9]|[2-9]\d|\d{3,})[.]\d+)([.]\d+|)|Android:?[ /-](12[89]|1[3-9]\d|[2-9]\d{2}|\d{4,})(\.\d+|)(\.\d+|)|Mobile Safari.+OPR\/([89]\d|\d{3,})\.\d+\.\d+|Android.+Firefox\/(12[7-9]|1[3-9]\d|[2-9]\d{2}|\d{4,})\.\d+(\.\d+|)|Android.+Chrom(ium|e)\/(12[89]|1[3-9]\d|[2-9]\d{2}|\d{4,})\.\d+(\.\d+|)|Android.+(UC? ?Browser|UCWEB|U3)[ /]?(15\.([5-9]|\d{2,})|(1[6-9]|[2-9]\d|\d{3,})\.\d+)\.\d+|SamsungBrowser\/(5\.\d+|([6-9]|\d{2,})\.\d+)|Android.+MQ{2}Browser\/(14(\.(9|\d{2,})|)|(1[5-9]|[2-9]\d|\d{3,})(\.\d+|))(\.\d+|)|K[Aa][Ii]OS\/(3\.\d+|([4-9]|\d{2,})\.\d+)(\.\d+|)/ }).modern, t = o.legacy, s = navigator.userAgent, i.test(s) ? "modern" : (t.test(s), "legacy")); window.Shopify = window.Shopify || {}; var c = window.Shopify; c.analytics = c.analytics || {}; var u = c.analytics; u.replayQueue = [], u.publish = function (d, e, r) { return u.replayQueue.push([d, e, r]), !0 }; try { self.performance.mark("wpm:start") } catch (d) { } var h = [r, "/wpm", "/b", n, l.substring(0, 1), ".js"].join(""); !function (d) { var e = d.src, r = d.async, a = void 0 === r || r, n = d.onload, o = d.onerror, i = document.createElement("script"), t = document.head, s = document.body; i.async = a, i.src = e, n && i.addEventListener("load", n), o && i.addEventListener("error", o), t ? t.appendChild(i) : s ? s.appendChild(i) : console.error("Did not find a head or body element to append the script") }({ src: h, async: !0, onload: function () { var r = window.webPixelsManager.init(d); e(r); var a = window.Shopify.analytics; a.replayQueue.forEach((function (d) { var e = d[0], a = d[1], n = d[2]; r.publishCustomEvent(e, a, n) })), a.replayQueue = [], a.publish = r.publishCustomEvent, a.visitor = r.visitor }, onerror: function () { var e = d.storefrontBaseUrl.replace(/\/$/, ""), r = "".concat(e, "/.well-known/shopify/monorail/unstable/produce_batch"), n = JSON.stringify({ metadata: { event_sent_at_ms: (new Date).getTime() }, events: [{ schema_id: "web_pixels_manager_load/3.1", payload: { version: a || "latest", bundle_target: l, page_url: self.location.href, status: "failed", surface: d.surface, error_msg: "".concat(h, " has failed to load") }, metadata: { event_created_at_ms: (new Date).getTime() } }] }); try { if (self.navigator.sendBeacon.bind(self.navigator)(r, n)) return !0 } catch (d) { } var o = new XMLHttpRequest; try { return o.open("POST", r, !0), o.setRequestHeader("Content-Type", "text/plain"), o.send(n), !0 } catch (d) { console && console.warn && console.warn("[Web Pixels Manager] Got an unhandled error while logging a load error.") } return !1 } }) })({ shopId: 24986091617, storefrontBaseUrl: "https://shop.irresistiblyminimal.com", extensionsBaseUrl: "https://extensions.shopifycdn.com/cdn/shopifycloud/web-pixels-manager", surface: "storefront-renderer", enabledBetaFlags: [], webPixelsConfigList: [{ "id": "217448545", "configuration": "{\"config\":\"{\\\"pixel_id\\\":\\\"AW-697589334\\\",\\\"gtag_events\\\":[{\\\"type\\\":\\\"page_view\\\",\\\"action_label\\\":\\\"AW-697589334\\\/CxaECNCe6LIBENa80cwC\\\"},{\\\"type\\\":\\\"purchase\\\",\\\"action_label\\\":\\\"AW-697589334\\\/UWfjCNOe6LIBENa80cwC\\\"},{\\\"type\\\":\\\"view_item\\\",\\\"action_label\\\":\\\"AW-697589334\\\/pkjQCNae6LIBENa80cwC\\\"},{\\\"type\\\":\\\"add_to_cart\\\",\\\"action_label\\\":\\\"AW-697589334\\\/rYRPCNme6LIBENa80cwC\\\"},{\\\"type\\\":\\\"begin_checkout\\\",\\\"action_label\\\":\\\"AW-697589334\\\/a_hhCNye6LIBENa80cwC\\\"},{\\\"type\\\":\\\"search\\\",\\\"action_label\\\":\\\"AW-697589334\\\/kV0hCN-e6LIBENa80cwC\\\"},{\\\"type\\\":\\\"add_payment_info\\\",\\\"action_label\\\":\\\"AW-697589334\\\/ve8sCOKe6LIBENa80cwC\\\"}],\\\"enable_monitoring_mode\\\":false}\"}", "eventPayloadVersion": "v1", "runtimeContext": "OPEN", "scriptVersion": "afe7c2de16587d6c6689522527d6c67f", "type": "APP", "apiClientId": 1780363, "privacyPurposes": [] }, { "id": "64520289", "configuration": "{\"pixel_id\":\"411549753103885\",\"pixel_type\":\"facebook_pixel\",\"metaapp_system_user_token\":\"-\"}", "eventPayloadVersion": "v1", "runtimeContext": "OPEN", "scriptVersion": "8d894c63179843e74a9691414b5ad83d", "type": "APP", "apiClientId": 2329312, "privacyPurposes": ["ANALYTICS", "MARKETING", "SALE_OF_DATA"] }, { "id": "shopify-app-pixel", "configuration": "{}", "eventPayloadVersion": "v1", "runtimeContext": "STRICT", "scriptVersion": "0220", "apiClientId": "shopify-pixel", "type": "APP", "privacyPurposes": ["ANALYTICS", "MARKETING"] }, { "id": "shopify-custom-pixel", "eventPayloadVersion": "v1", "runtimeContext": "LAX", "scriptVersion": "0220", "apiClientId": "shopify-pixel", "type": "CUSTOM", "privacyPurposes": ["ANALYTICS", "MARKETING"] }], isMerchantRequest: false, initData: { "shop": { "name": "Irresistibly Minimal", "paymentSettings": { "currencyCode": "USD" }, "myshopifyDomain": "irresistiblyminimal.myshopify.com", "countryCode": "US", "storefrontUrl": "https:\/\/shop.irresistiblyminimal.com" }, "customer": null, "cart": null, "checkout": null, "productVariants": [], "purchasingCompany": null }, }, function pageEvents(webPixelsManagerAPI) { webPixelsManagerAPI.publish("page_viewed", {}); }, "https://shop.irresistiblyminimal.com/cdn", "1518c2ba4d2b3301a1e3cb6576947ef22edf7bb6", "3c762e5aw5b983e43pc2dc4883m545d5a27",);</script>
  <script>window.ShopifyAnalytics = window.ShopifyAnalytics || {};
    window.ShopifyAnalytics.meta = window.ShopifyAnalytics.meta || {};
    window.ShopifyAnalytics.meta.currency = 'LKR';
    var meta = { "page": { "pageType": "home" } };
    for (var attr in meta) {
      window.ShopifyAnalytics.meta[attr] = meta[attr];
    }</script>


  <!-- <script>
    function logout() {
        // Redirect to logout and then home page
        setTimeout(() => {
            window.location.href = "home.html";
        }, 100); // Adjust timeout if necessary
    }
</script> -->




  <div id="shopify-block-15683396631634586217" class="shopify-block shopify-app-block">
    <script id="chat-button-container" data-horizontal-position=bottom_right data-vertical-position=lowest
      data-icon=chat_bubble data-text=no_text data-color=#1c1d1d data-secondary-color=#FFFFFF data-ternary-color=#6A6A6A
      data-greeting-message=%E2%81%A3%F0%9F%91%8B+Why%2C+hello+there%21+I%E2%80%99m+Joe%21%E2%A0%80%E2%A0%80%0A%0APlease+feel+free+to+message+me+with+your+questions%2C+and+I%E2%80%99ll+get+back+to+you+as+soon+as+I+can%21+I%E2%80%99m+a+full-time+maker%2C+so+I%E2%80%99ll+typically+reply+and+be+ready+to+chat+in+a+few+hours.%E2%A0%80%0A%0A%E2%9D%95+If+you%27re+going+to+leave+this+page%2C+please+email+me+your+questions+at+hello%40irresistiblyminimal.com+instead.+Speak+soon%21%E2%A0%80%0A%E2%A0%80%0AWarmest%2C%0AJoe.
      data-domain=shop.irresistiblyminimal.com data-external-identifier=E9Yt0tASO8zbBRHd53anhsNrHKbvZ13SQa4UJZKTbFk>
      </script>

    <script>(function () {
        function asyncLoad() {
          var urls = ["https:\/\/load.fomo.com\/api\/v1\/-7vYC_lDkTvchmfKVVng5Q\/load.js?shop=irresistiblyminimal.myshopify.com", "https:\/\/js.smile.io\/v1\/smile-shopify.js?shop=irresistiblyminimal.myshopify.com", "https:\/\/shopify.covet.pics\/covet-pics-widget-inject.js?shop=irresistiblyminimal.myshopify.com"];
          for (var i = 0; i < urls.length; i++) {
            var s = document.createElement('script');
            s.type = 'text/javascript';
            s.async = true;
            s.src = urls[i];
            var x = document.getElementsByTagName('script')[0];
            x.parentNode.insertBefore(s, x);
          }
        };
        if (window.attachEvent) {
          window.attachEvent('onload', asyncLoad);
        } else {
          window.addEventListener('load', asyncLoad, false);
        }
      })();</script>

    <div class="smile-shopify-init" data-channel-key="channel_M3MJFKPHS5Wm886LlVqu40eE"></div>


  </div>


  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const swiper = new Swiper('.testimonials-slider', {
        slidesPerView: 5, // Show 5 slides at a time
        centeredSlides: true, // Center the active slide
        loop: true, // Enable infinite looping
        spaceBetween: 30, // Set consistent spacing between slides
        autoplay: {
          delay: 3000, // Auto-rotate every 3 seconds
          disableOnInteraction: false,
        },
        pagination: {
          el: '.swiper-pagination',
          clickable: true,
        },
        on: {
          slideChangeTransitionEnd: function () {
            // Reset styles for all slides
            document.querySelectorAll('.swiper-slide').forEach(slide => {
              slide.style.opacity = '0.6';
              slide.style.transform = 'scale(1)';
              slide.style.transition = 'transform 0.3s, opacity 0.3s'; // Smooth transitions
            });

            // Apply styles for the active slide
            const activeSlide = document.querySelector('.swiper-slide.swiper-slide-active');
            if (activeSlide) {
              activeSlide.style.opacity = '1';
              activeSlide.style.transform = 'scale(1.2)';
            }
          },
        },
        breakpoints: {
          320: {
            slidesPerView: 1,
            spaceBetween: 15,
          },
          768: {
            slidesPerView: 3,
            spaceBetween: 20,
          },
          1024: {
            slidesPerView: 5,
            spaceBetween: 30, // Consistent 30px gap for larger screens
          },
        },
      });

      // Initialize styles for the active slide
      const initializeActiveSlide = () => {
        const activeSlide = document.querySelector('.swiper-slide.swiper-slide-active');
        if (activeSlide) {
          activeSlide.style.opacity = '1';
          activeSlide.style.transform = 'scale(1.2)';
          activeSlide.style.transition = 'transform 0.3s, opacity 0.3s'; // Smooth transitions
        }
      };

      setTimeout(initializeActiveSlide, 100);
    });

  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
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
      searchInput.addEventListener('keypress', function (e) {
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

    // Wait for the DOM to load before adding event listeners
    document.addEventListener("DOMContentLoaded", function () {

      // Function to handle the increase and decrease of quantity
      const quantityIncreaseButtons = document.querySelectorAll('.quantity-increase');
      const quantityDecreaseButtons = document.querySelectorAll('.quantity-decrease');

      // Event listener for increase buttons
      quantityIncreaseButtons.forEach(button => {
        button.addEventListener('click', function () {
          const quantityInput = this.closest('.quantity-box').querySelector('.quantity-input');
          let currentValue = parseInt(quantityInput.value);
          quantityInput.value = currentValue + 1;
          updateTotalPrice(this.closest('.cart-item'));
        });
      });

      // Event listener for decrease buttons
      quantityDecreaseButtons.forEach(button => {
        button.addEventListener('click', function () {
          const quantityInput = this.closest('.quantity-box').querySelector('.quantity-input');
          let currentValue = parseInt(quantityInput.value);
          if (currentValue > 1) {
            quantityInput.value = currentValue - 1;
            updateTotalPrice(this.closest('.cart-item'));
          }
        });
      });

      // Function to update the total price based on quantity
      function updateTotalPrice(cartItem) {
        const quantity = parseInt(cartItem.querySelector('.quantity-input').value);
        const price = parseFloat(cartItem.querySelector('.cart-item-price').textContent.replace('Rs ', '').replace(',', ''));
        const totalPrice = quantity * price;
        cartItem.querySelector('.cart-item-price').textContent = 'Rs ' + totalPrice.toFixed(2);
        updateCartTotal();
      }

      // Function to update the cart total price
      function updateCartTotal() {
        let total = 0;
        const cartItems = document.querySelectorAll('.cart-item');
        cartItems.forEach(item => {
          const quantity = parseInt(item.querySelector('.quantity-input').value);
          const price = parseFloat(item.querySelector('.cart-item-price').textContent.replace('Rs ', '').replace(',', ''));
          total += quantity * price;
        });
        document.querySelector('.cart-footer h3:last-child').textContent = 'Rs ' + total.toFixed(2);
      }

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