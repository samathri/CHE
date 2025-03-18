<?php
include './php/db.php';
session_start();

$productsPerPage = 12;
$currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($currentPage - 1) * $productsPerPage;

$search = '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';

$where_clause = "
    (productName LIKE '% ring%' 
    OR productName LIKE 'ring%' 
    OR productName LIKE '% ring' 
    OR productName = 'ring')
";

if (isset($_GET["query"]) && !empty($_GET["query"])) {
    $search = $conn->real_escape_string($_GET["query"]);
    $where_clause = "
        (
            (productName LIKE '%$search%' OR productDescription LIKE '%$search%') 
            AND (
                productName LIKE '% ring%' 
                OR productName LIKE 'ring%' 
                OR productName LIKE '% ring' 
                OR productName = 'ring'
            )
        )
    ";
}

// Define the ORDER BY clause based on sort parameter
$order_clause = "productName ASC"; 
switch($sort) {
    case 'featured':
        $order_clause = "featured DESC, productName ASC";
        break;
    case 'alpha-asc':
        $order_clause = "productName ASC";
        break;
    case 'alpha-desc':
        $order_clause = "productName DESC";
        break;
    case 'price-asc':
        $order_clause = "productPrice ASC, productName ASC";
        break;
    case 'price-desc':
        $order_clause = "productPrice DESC, productName ASC";
        break;
    case 'date-asc':
        $check_column = $conn->query("SHOW COLUMNS FROM products LIKE 'createdAt'");
        if($check_column->num_rows > 0) {
            $order_clause = "createdAt ASC, productName ASC";
        } else {
            $order_clause = "id ASC, productName ASC"; 
        }
        break;
    case 'date-desc':
        $check_column = $conn->query("SHOW COLUMNS FROM products LIKE 'createdAt'");
        if($check_column->num_rows > 0) {
            $order_clause = "createdAt DESC, productName ASC";
        } else {
            $order_clause = "id DESC, productName ASC"; 
        }
        break;
    default:
        $order_clause = "
            CASE 
                WHEN productName LIKE '$search%' THEN 1
                ELSE 2
            END, 
            productName ASC
        ";
}

$sqlTotal = "SELECT COUNT(*) FROM products WHERE $where_clause";
$resultTotal = $conn->query($sqlTotal);
$totalProducts = $resultTotal->fetch_row()[0];
$totalPages = ceil($totalProducts / $productsPerPage);

$sql = "
    SELECT * 
    FROM products 
    WHERE $where_clause 
    ORDER BY $order_clause 
    LIMIT $offset, $productsPerPage
";
$result = $conn->query($sql);

// Handle AJAX requests
if (isset($_GET["ajax"])) {
    $products = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    echo json_encode([
        'products' => $products,
        'totalPages' => $totalPages
    ]);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Responsive Text Block</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Bootstrap 5 CDN Link -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="header.css">
  <link rel="stylesheet" href="style.css">
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
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
        <p>Taxes and <a href="Privacy Policy.php" class="shipping" style="text-decoration: underline;">shipping</a> calculated at checkout</p>
        <a href="product-detail.php">
        <button class="btn btn-warning w-100 mt-3">
          CHECKOUT <i class="bi bi-arrow-right ms-2"></i>
        </button>
        </a>
      </div>
    </div>
  </div>




  <div class="container text-center mt-5">
        <h1 class="title">Rings</h1>
        <div class="line mx-auto"></div>
        
    <p class="description mt-4">
        Get glamorous with just a few rings! Made with repeated hammering and really hot fire, our entirely handcrafted rings are honestly beautiful, and they’ll never go out of style!
    </p>
    <div class="d-flex justify-content-center align-items-center mt-3">
    <label for="sortSelect" class="me-2">Sort by:</label>
    <select id="sortSelect" name="sort" class="form-select w-auto" style="border-radius: 0;">
        <option value="default" <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'default') ? 'selected' : ''; ?>>Best selling</option>
        <option value="featured" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'featured') ? 'selected' : ''; ?>>Featured</option>
        <option value="alpha-asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'alpha-asc') ? 'selected' : ''; ?>>Alphabetically, A-Z</option>
        <option value="alpha-desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'alpha-desc') ? 'selected' : ''; ?>>Alphabetically, Z-A</option>
        <option value="price-asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price-asc') ? 'selected' : ''; ?>>Price, low to high</option>
        <option value="price-desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price-desc') ? 'selected' : ''; ?>>Price, high to low</option>
        <option value="date-asc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'date-asc') ? 'selected' : ''; ?>>Date, old to new</option>
        <option value="date-desc" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'date-desc') ? 'selected' : ''; ?>>Date, new to old</option>
    </select>
</div>
    <div class="d-flex justify-content-center align-items-center mt-3">
      <label for="browse" id="browse" class="me-2">Browse by:</label>
      <select id="browse" class="form-select w-auto" style="border-radius: 0;">
        <option selected>All</option>
        <option>14K GOLD-FILLED RINGS</option>
        <option>14K ROSE GOLD-FILLED RINGS</option>
        <option>CLASSIC RINGS</option>
        <option>ON SALE</option>
        <option>STERLING SILVER RINGS</option>
        <option>SUPER THIN RINGS</option>
      </select>
    </div>
    <div class="row mt-4" id="products-container">
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
                echo '<div class="col-12"><p>No products found matching your search.</p></div>';
            }
            ?>
        </div>

     
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php if ($currentPage <= 1) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $currentPage - 1; ?><?php echo isset($_GET['query']) ? '&query=' . urlencode($_GET['query']) : ''; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <?php
                for ($page = 1; $page <= $totalPages; $page++) {
                    if ($page == $currentPage) {
                        echo '<li class="page-item active"><span class="page-link">' . $page . '</span></li>';
                    } else {
                        echo '<li class="page-item"><a class="page-link" href="?page=' . $page . (isset($_GET['query']) ? '&query=' . urlencode($_GET['query']) : '') . '">' . $page . '</a></li>';
                    }
                }
                ?>

                <li class="page-item <?php if ($currentPage >= $totalPages) echo 'disabled'; ?>">
                    <a class="page-link" href="?page=<?php echo $currentPage + 1; ?><?php echo isset($_GET['query']) ? '&query=' . urlencode($_GET['query']) : ''; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

<div class="line-bottom mb-4 mx-auto"></div>


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

  <!-- Modal (Search Bar Popup) -->
  <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="search-bar-container">
                    <div class="search-bar">
                            <form id="searchForm" method="GET" action="" class="search-box">
                            
                                    <input type="text" name="query" class="search-input" placeholder="Search Our Store" 
                                           style="font-family: 'Sitka', serif;" value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
                                  
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


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="scripts.js"></script>

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
})();</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('searchForm');
        const searchInput = searchForm.querySelector('input[name="query"]');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                const query = searchInput.value;
                if (query.length >= 2) {
                    fetch(`?ajax=1&query=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(products => {
                            const container = document.getElementById('products-container');
                            container.innerHTML = '';
                            
                            if (products.length > 0) {
                                products.forEach(product => {
                                    container.innerHTML += `
                                        <div class="col-sm-6 col-md-4 mb-4" id="items-grid">
                                            <div class="card">
                                                <a href="detail.php?id=${product.id}">
                                                    <img src="${product.productImage}" class="card-img-top" alt="${product.productName}">
                                                </a>
                                                <div class="card-body">
                                                    <p class="card-text">
                                                        <span>${product.productName}</span> — <span>RS ${parseFloat(product.productPrice).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>`;
                                });
                            } else {
                                container.innerHTML = '<div class="col-12"><p>No products found matching your search.</p></div>';
                            }
                        });
                }
            }, 300);
        });
    });
    </script>

<script>
document.getElementById('sortSelect').addEventListener('change', function() {
    const currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('sort', this.value);
    window.location.href = currentUrl.toString();
});
</script>

<!--Mobile search bar-->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileSearchInput = document.querySelector('.search-wrapper input');
    const mobileSearchBtn = document.querySelector('.mobile-search-btn');
    const mobileSidebar = document.getElementById('mobileSidebar');

    function performMobileSearch() {
        const query = mobileSearchInput.value.trim();

        mobileSidebar.classList.remove('active');

        const urlParams = new URLSearchParams(window.location.search);
        const currentSort = urlParams.get('sort');

        let searchUrl = `?query=${encodeURIComponent(query)}`;
        if (currentSort) {
            searchUrl += `&sort=${currentSort}`;
        }

        window.location.href = searchUrl;
    }

    if (mobileSearchBtn) {
        mobileSearchBtn.addEventListener('click', performMobileSearch);
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
