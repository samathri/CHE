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


// address view

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Check if the user has saved any edits in the temporary_address table
    $sql_temp = "SELECT * FROM temporary_address WHERE user_id = ?";
    $stmt_temp = $conn->prepare($sql_temp);
    $stmt_temp->bind_param("i", $user_id);
    $stmt_temp->execute();
    $result_temp = $stmt_temp->get_result();

    if ($result_temp->num_rows > 0) {
        // If there are edited details in the temporary_address table, use them
        $user_details = $result_temp->fetch_assoc();
        $user_address = [
            'firstName' => $user_details['firstName'],
            'lastName' => $user_details['lastName'],
            'address1' => $user_details['address1'],
            
            'city' => $user_details['city'],
            // 'country' => $user_details['country'],
            'zip' => $user_details['zip'],
            // 'phone' => $user_details['phone']
        ];
    } else {
        // If no edits are saved, fetch data from the user_address table
        $sql = "SELECT ua.*, ur.user_email FROM user_address ua 
                JOIN user_registration ur ON ua.user_id = ur.id
                WHERE ua.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user_details = $result->fetch_assoc();
            $user_address = [
                'firstName' => $user_details['firstName'],
                'lastName' => $user_details['lastName'],
                'address1' => $user_details['address1'],
                'address2' => $user_details['address2'],
                'city' => $user_details['city'],
                'country' => $user_details['country'],
                'zip' => $user_details['zip'],
                'phone' => $user_details['phone']
            ];
        } else {
            // If no address found, set default values or empty
            $user_address = [
                'firstName' => '',
                'lastName' => '',
                'address1' => '',
                'address2' => '',
                'city' => '',
                'country' => '',
                'zip' => '',
                'phone' => ''
            ];
        }
    }
} else {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit();
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
            echo "<div class='alert alert-success'>Details saved successfully to the temporary table!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error saving details: " . $conn->error . "</div>";
        }
    } else {
        header("Location: login.php");
        exit();
    }
}


?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bootstrap Header with Lora Font</title>
  <!-- Bootstrap 5 CDN Link -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   
   <link rel="stylesheet" href="header.css">
   <link rel="stylesheet" href="style.css">

    <!-- Google Font: Lora and Sitka -->
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Sitka&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Bitter:wght@400;700&display=swap" rel="stylesheet">

<!-- Flag Icon CSS Library for Flags -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icon-css/css/flag-icons.min.css">

<!-- Bootstrap Icons CDN Link -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

  <!-- Google Font: Lora and Sitka -->
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Sitka&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Bitter:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Allura&family=Great+Vibes&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Allura&family=Great+Vibes&display=swap" rel="stylesheet">
  
  <!-- Flag Icon CSS Library for Flags -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icon-css/css/flag-icons.min.css">
  
  <!-- Bootstrap Icons CDN Link -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link
  href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
  rel="stylesheet"
/>

</head>
<body>

  <!-- Gray Box at the Beginning of the Page -->
  <div class="gray-box"> </div>

<!-- mobile side bar start -->
<div id="mobileSidebar" class="mobile-sidebar d-md-none">
  <!-- Close Icon -->
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

<!-- Bootstrap Header Example with white background navbar -->
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


  <!-- Cart Sidebar -->
  <div id="cartSidebar" class="sidebar">
    <div class="sidebar-content">
      <!-- Title and Close Button -->
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
              <div class="quantity-box d-flex align-items-center border" >
                <button class="btn btn-sm quantity-decrease" style="background: none !important;">-</button>
                <input type="number" class="form-control form-control-sm text-center quantity-input" value="1" min="1">
                <button class="btn btn-sm quantity-increase" style="background: none !important;">+</button>
              </div>
              <div class="cart-item-price ms-auto">Rs 11,300.00</div>
            </div>

          </div>

        </div>

        <!-- Repeat for more cart items -->
      </div>
      <hr>

      <!-- Special Instructions Box -->
      <div class="special-instructions p-3">
        <label for="instructions" class="form-label instructions">Special instructions for seller</label>
        <textarea id="instructions" class="form-control" rows="4"></textarea>
      </div>



      <!-- Sticky Footer -->
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
            <div class="step active text-center">
                <div class="circle">2</div>
                <div class="step-name">Delivery Details</div>
            </div>
            <!-- Step 3 -->
            <div class="step text-center">
                <div class="circle">3</div>
                <div class="step-name">Payment</div>
            </div>
        </div>
    </div>
</div>


<div class="account-section">
  <!-- Title -->
  <h6>
    Account
    <span id="toggle-icon" class="dropdown-icon">
      <i class="bi bi-chevron-down"></i>
    </span>
  </h6>

  <!-- Email -->
  <p><?php echo htmlspecialchars($user_email); ?></p>

  <!-- Log Out Link (Hidden by default) -->
  <a href="#" id="logout-link" class="logout-link d-none">Log out</a>
  <hr style="margin: 10px 0;">
</div>

<!-- Form Container -->


<div class="form-container">
  <h3 class="mb-4" style="font-weight: 600;">Delivery Details</h3>

  <!-- Country/Region -->
  <div class="form-floating mb-3">
    <select class="form-select" id="country">
      <option value="" selected disabled>Select your country</option>
      <option value="AF">Afghanistan</option>
      <option value="AL">Albania</option>
      <option value="DZ">Algeria</option>
      <option value="AS">American Samoa</option>
      <option value="AD">Andorra</option>
      <option value="AO">Angola</option>
      <option value="AI">Anguilla</option>
      <option value="AG">Antigua and Barbuda</option>
      <option value="AR">Argentina</option>
      <option value="AM">Armenia</option>
      <option value="AU">Australia</option>
      <option value="AT">Austria</option>
      <option value="AZ">Azerbaijan</option>
      <option value="BS">Bahamas</option>
      <option value="BH">Bahrain</option>
      <option value="BD">Bangladesh</option>
      <option value="BB">Barbados</option>
      <option value="BY">Belarus</option>
      <option value="BE">Belgium</option>
      <option value="BZ">Belize</option>
      <option value="BJ">Benin</option>
      <option value="BM">Bermuda</option>
      <option value="BT">Bhutan</option>
      <option value="BO">Bolivia</option>
      <option value="BA">Bosnia and Herzegovina</option>
      <option value="BW">Botswana</option>
      <option value="BR">Brazil</option>
      <option value="BN">Brunei</option>
      <option value="BG">Bulgaria</option>
      <option value="BF">Burkina Faso</option>
      <option value="BI">Burundi</option>
      <option value="KH">Cambodia</option>
      <option value="CM">Cameroon</option>
      <option value="CA">Canada</option>
      <option value="CV">Cape Verde</option>
      <option value="KY">Cayman Islands</option>
      <option value="CF">Central African Republic</option>
      <option value="TD">Chad</option>
      <option value="CL">Chile</option>
      <option value="CN">China</option>
      <option value="CO">Colombia</option>
      <option value="KM">Comoros</option>
      <option value="CG">Congo - Brazzaville</option>
      <option value="CD">Congo - Kinshasa</option>
      <option value="CR">Costa Rica</option>
      <option value="HR">Croatia</option>
      <option value="CU">Cuba</option>
      <option value="CY">Cyprus</option>
      <option value="CZ">Czechia</option>
      <option value="DK">Denmark</option>
      <option value="DJ">Djibouti</option>
      <option value="DM">Dominica</option>
      <option value="DO">Dominican Republic</option>
      <option value="EC">Ecuador</option>
      <option value="EG">Egypt</option>
      <option value="SV">El Salvador</option>
      <option value="GQ">Equatorial Guinea</option>
      <option value="ER">Eritrea</option>
      <option value="EE">Estonia</option>
      <option value="ET">Ethiopia</option>
      <option value="FJ">Fiji</option>
      <option value="FI">Finland</option>
      <option value="FR">France</option>
      <option value="GA">Gabon</option>
      <option value="GM">Gambia</option>
      <option value="GE">Georgia</option>
      <option value="DE">Germany</option>
      <option value="GH">Ghana</option>
      <option value="GR">Greece</option>
      <option value="GD">Grenada</option>
      <option value="GU">Guam</option>
      <option value="GT">Guatemala</option>
      <option value="GN">Guinea</option>
      <option value="GW">Guinea-Bissau</option>
      <option value="GY">Guyana</option>
      <option value="HT">Haiti</option>
      <option value="HN">Honduras</option>
      <option value="HK">Hong Kong SAR</option>
      <option value="HU">Hungary</option>
      <option value="IS">Iceland</option>
      <option value="IN">India</option>
      <option value="ID">Indonesia</option>
      <option value="IR">Iran</option>
      <option value="IQ">Iraq</option>
      <option value="IE">Ireland</option>
      <option value="IL">Israel</option>
      <option value="IT">Italy</option>
      <option value="JM">Jamaica</option>
      <option value="JP">Japan</option>
      <option value="JO">Jordan</option>
      <option value="KZ">Kazakhstan</option>
      <option value="KE">Kenya</option>
      <option value="KI">Kiribati</option>
      <option value="KW">Kuwait</option>
      <option value="KG">Kyrgyzstan</option>
      <option value="LA">Laos</option>
      <option value="LV">Latvia</option>
      <option value="LB">Lebanon</option>
      <option value="LS">Lesotho</option>
      <option value="LR">Liberia</option>
      <option value="LY">Libya</option>
      <option value="LI">Liechtenstein</option>
      <option value="LT">Lithuania</option>
      <option value="LU">Luxembourg</option>
      <option value="MG">Madagascar</option>
      <option value="MW">Malawi</option>
      <option value="MY">Malaysia</option>
      <option value="MV">Maldives</option>
      <option value="ML">Mali</option>
      <option value="MT">Malta</option>
      <option value="MH">Marshall Islands</option>
      <option value="MR">Mauritania</option>
      <option value="MU">Mauritius</option>
      <option value="MX">Mexico</option>
      <option value="FM">Micronesia</option>
      <option value="MD">Moldova</option>
      <option value="MC">Monaco</option>
      <option value="MN">Mongolia</option>
      <option value="ME">Montenegro</option>
      <option value="MA">Morocco</option>
      <option value="MZ">Mozambique</option>
      <option value="MM">Myanmar</option>
      <option value="NA">Namibia</option>
      <option value="NR">Nauru</option>
      <option value="NP">Nepal</option>
      <option value="NL">Netherlands</option>
      <option value="NZ">New Zealand</option>
      <option value="NI">Nicaragua</option>
      <option value="NE">Niger</option>
      <option value="NG">Nigeria</option>
      <option value="KP">North Korea</option>
      <option value="NO">Norway</option>
      <option value="OM">Oman</option>
      <option value="PK">Pakistan</option>
      <option value="PW">Palau</option>
      <option value="PA">Panama</option>
      <option value="PG">Papua New Guinea</option>
      <option value="PY">Paraguay</option>
      <option value="PE">Peru</option>
      <option value="PH">Philippines</option>
      <option value="PL">Poland</option>
      <option value="PT">Portugal</option>
      <option value="QA">Qatar</option>
      <option value="RO">Romania</option>
      <option value="RU">Russia</option>
      <option value="RW">Rwanda</option>
      <option value="WS">Samoa</option>
      <option value="SM">San Marino</option>
      <option value="SA">Saudi Arabia</option>
      <option value="SN">Senegal</option>
      <option value="RS">Serbia</option>
      <option value="SG">Singapore</option>
      <option value="SK">Slovakia</option>
      <option value="SI">Slovenia</option>
      <option value="ZA">South Africa</option>
      <option value="ES">Spain</option>
      <option value="LK">Sri Lanka</option>
      <option value="SE">Sweden</option>
      <option value="CH">Switzerland</option>
      <option value="TH">Thailand</option>
      <option value="TR">Turkey</option>
      <option value="UA">Ukraine</option>
      <option value="AE">United Arab Emirates</option>
      <option value="GB">United Kingdom</option>
      <option value="US">United States</option>
      <option value="VN">Vietnam</option>
      <option value="ZW">Zimbabwe</option>
    </select>
    <label for="country">Country/Region</label>
  </div>
  
  <form method="POST" action="checkout 3.php">
  <h3 class="mb-4">Edit Address Details</h3>

  <!-- First Name and Last Name -->
  <div class="row mb-3">
    <div class="col-md-6">
      <div class="form-floating">
        <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($user_address['firstName']); ?>" required />
        <label for="firstName">First Name</label>
      </div>
    </div>
    <div class="col-md-6">
      <div class="form-floating">
        <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($user_address['lastName']); ?>" required />
        <label for="lastName">Last Name</label>
      </div>
    </div>
  </div>

  <!-- Address -->
  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="address1" name="address1" value="<?php echo htmlspecialchars($user_address['address1']); ?>" required />
    <label for="address1">Address</label>
  </div>

  <!-- City -->
  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($user_address['city']); ?>" required />
    <label for="city">City</label>
  </div>

  <!-- Postal Code -->
  <div class="form-floating mb-3">
    <input type="text" class="form-control" id="zip" name="zip" value="<?php echo htmlspecialchars($user_address['zip']); ?>" required />
    <label for="zip">Postal Code</label>
  </div>
  <h3 class="mb-4" style="font-weight: 600;">Shipping methods</h3>
  <div class="shipping-method active d-flex align-items-center">
    <input type="radio" name="shipping" id="standardShipping" checked class="form-check-input custom-radio" />
    <label for="standardShipping" class="flex-grow-1">
      Standard 7-21 Business Days Delivery & Tracking
    </label>
    <span class="fw">Rs.8900.00</span>
  </div>

  <div class="shipping-method d-flex align-items-center">
    <input type="radio" name="shipping" id="expressShipping" class="form-check-input custom-radio" />
    <label for="expressShipping" class="flex-grow-1">
      3-5 Business Days Delivery & Tracking
    </label>
    <span class="fw">Rs.17,700.00</span>
  </div>

  <!-- Save Button -->
  <button type="submit" name="save" class="btn btn-primary">Next<span>&rarr;</span></button>
</form>

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
    const triangleDown = document.getElementById('triangleDown');
    const dropdownMenu = document.getElementById('jewelryDropdown');

    triangleDown.addEventListener('click', function() {
      if (dropdownMenu.style.display === "block") {
        dropdownMenu.style.display = "none";
      } else {
        dropdownMenu.style.display = "block";
      }
    });

    document.addEventListener('click', function(event) {
      if (!triangleDown.contains(event.target) && !dropdownMenu.contains(event.target)) {
        dropdownMenu.style.display = "none"; 
      }
    });


window.addEventListener('scroll', function () {
  const section = document.getElementById('features');
  const rect = section.getBoundingClientRect();
  
  const scrollPosition = window.scrollY + window.innerHeight - rect.top;

  const maxBlur = 20; 
  const blurAmount = Math.min((scrollPosition / rect.height) * maxBlur, maxBlur);

  section.style.filter = `blur(${blurAmount}px)`;

  if (scrollPosition < 0) {
    section.style.filter = 'blur(0px)';
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
