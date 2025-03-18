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

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FAQ & Giving Rewards</title>
  <!-- Link Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Link Custom CSS -->
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



  <div class="container-faq mt-5">

    <h1 class="title text-center">Terms of service</h1>

    <div class="line mb-4 mx-auto"></div>


      <p><strong>OVERVIEW</strong><br>
      This website is operated by Irresistibly Minimal. Throughout the site, the terms “we”, “us” and “our” refer to Irresistibly Minimal. Irresistibly Minimal offers this website, including all information, tools and services available from this site to you, the user, conditioned upon your acceptance of all terms, conditions, policies and notices stated here.</p>
        
      <p>
        By visiting our site and/ or purchasing something from us, you engage in our “Service” and agree to be bound by the following terms and conditions (“Terms of Service”, “Terms”), including those additional terms and conditions and policies referenced herein and/or available by hyperlink. These Terms of Service apply to all users of the site, including without limitation users who are browsers, vendors, customers, merchants, and/ or contributors of content.
      </p>

      <p>
        Please read these Terms of Service carefully before accessing or using our website. By accessing or using any part of the site, you agree to be bound by these Terms of Service. If you do not agree to all the terms and conditions of this agreement, then you may not access the website or use any services. If these Terms of Service are considered an offer, acceptance is expressly limited to these Terms of Service.
      </p>

      <p>
        Any new features or tools which are added to the current store shall also be subject to the Terms of Service. You can review the most current version of the Terms of Service at any time on this page. We reserve the right to update, change or replace any part of these Terms of Service by posting updates and/or changes to our website. It is your responsibility to check this page periodically for changes. Your continued use of or access to the website following the posting of any changes constitutes acceptance of those changes.
      </p>

      <p>
        Our store is hosted on Shopify Inc. They provide us with the online e-commerce platform that allows us to sell our products and services to you.
      </p>


      <p><strong>SECTION 2 - GENERAL CONDITIONS</strong><br>
        We reserve the right to refuse service to anyone for any reason at any time.
        You understand that your content (not including credit card information), may be transferred unencrypted and involve (a) transmissions over various networks; and (b) changes to conform and adapt to technical requirements of connecting networks or devices. Credit card information is always encrypted during transfer over networks.
        You agree not to reproduce, duplicate, copy, sell, resell or exploit any portion of the Service, use of the Service, or access to the Service or any contact on the website through which the service is provided, without express written permission by us.
        The headings used in this agreement are included for convenience only and will not limit or otherwise affect these Terms.
      </p>


      <p><strong>SECTION 3 - ACCURACY, COMPLETENESS AND TIMELINESS OF INFORMATION</strong><br>
        We are not responsible if information made available on this site is not accurate, complete or current. The material on this site is provided for general information only and should not be relied upon or used as the sole basis for making decisions without consulting primary, more accurate, more complete or more timely sources of information. Any reliance on the material on this site is at your own risk.
        This site may contain certain historical information. Historical information, necessarily, is not current and is provided for your reference only. We reserve the right to modify the contents of this site at any time, but we have no obligation to update any information on our site. You agree that it is your responsibility to monitor changes to our site.
      </p>

      <p><strong>SECTION 4 - MODIFICATIONS TO THE SERVICE AND PRICES</strong><br>
        Prices for our products are subject to change without notice.
        We reserve the right at any time to modify or discontinue the Service (or any part or content thereof) without notice at any time.
        We shall not be liable to you or to any third-party for any modification, price change, suspension or discontinuance of the Service.
      </p>

      <p><strong>SECTION 5 - PRODUCTS OR SERVICES (if applicable)</strong><br>
        Certain products or services may be available exclusively online through the website. These products or services may have limited quantities and are subject to return or exchange only according to our Return Policy.
        We have made every effort to display as accurately as possible the colors and images of our products that appear at the store. We cannot guarantee that your computer monitor's display of any color will be accurate.
        We reserve the right, but are not obligated, to limit the sales of our products or Services to any person, geographic region or jurisdiction. We may exercise this right on a case-by-case basis. We reserve the right to limit the quantities of any products or services that we offer. All descriptions of products or product pricing are subject to change at anytime without notice, at the sole discretion of us. We reserve the right to discontinue any product at any time. Any offer for any product or service made on this site is void where prohibited.
        We do not warrant that the quality of any products, services, information, or other material purchased or obtained by you will meet your expectations, or that any errors in the Service will be corrected.
      </p>

      <p><strong>SECTION 6 - ACCURACY OF BILLING AND ACCOUNT INFORMATION</strong><br>
        We reserve the right to refuse any order you place with us. We may, in our sole discretion, limit or cancel quantities purchased per person, per household or per order. These restrictions may include orders placed by or under the same customer account, the same credit card, and/or orders that use the same billing and/or shipping address. In the event that we make a change to or cancel an order, we may attempt to notify you by contacting the e‑mail and/or billing address/phone number provided at the time the order was made. We reserve the right to limit or prohibit orders that, in our sole judgment, appear to be placed by dealers, resellers or distributors.

        <p>You agree to provide current, complete and accurate purchase and account information for all purchases made at our store. You agree to promptly update your account and other information, including your email address and credit card numbers and expiration dates, so that we can complete your transactions and contact you as needed.</p>
        
        <p>For more detail, please review our Returns Policy.</p>
      </p>

      <p><strong>SECTION 7 - OPTIONAL TOOLS</strong><br>
        We may provide you with access to third-party tools over which we neither monitor nor have any control nor input.
        You acknowledge and agree that we provide access to such tools ”as is” and “as available” without any warranties, representations or conditions of any kind and without any endorsement. We shall have no liability whatsoever arising from or relating to your use of optional third-party tools.
        Any use by you of optional tools offered through the site is entirely at your own risk and discretion and you should ensure that you are familiar with and approve of the terms on which tools are provided by the relevant third-party provider(s).
        We may also, in the future, offer new services and/or features through the website (including, the release of new tools and resources). Such new features and/or services shall also be subject to these Terms of Service.
      </p>


      <p><strong>SECTION 8 - THIRD-PARTY LINKS</strong><br>
        Certain content, products and services available via our Service may include materials from third-parties.
        Third-party links on this site may direct you to third-party websites that are not affiliated with us. We are not responsible for examining or evaluating the content or accuracy and we do not warrant and will not have any liability or responsibility for any third-party materials or websites, or for any other materials, products, or services of third-parties.
        We are not liable for any harm or damages related to the purchase or use of goods, services, resources, content, or any other transactions made in connection with any third-party websites. Please review carefully the third-party's policies and practices and make sure you understand them before you engage in any transaction. Complaints, claims, concerns, or questions regarding third-party products should be directed to the third-party.
      </p>


      <p><strong>SECTION 9 - USER COMMENTS, FEEDBACK AND OTHER SUBMISSIONS</strong><br>
        If, at our request, you send certain specific submissions (for example contest entries) or without a request from us you send creative ideas, suggestions, proposals, plans, or other materials, whether online, by email, by postal mail, or otherwise (collectively, 'comments'), you agree that we may, at any time, without restriction, edit, copy, publish, distribute, translate and otherwise use in any medium any comments that you forward to us. We are and shall be under no obligation (1) to maintain any comments in confidence; (2) to pay compensation for any comments; or (3) to respond to any comments.
        We may, but have no obligation to, monitor, edit or remove content that we determine in our sole discretion are unlawful, offensive, threatening, libelous, defamatory, pornographic, obscene or otherwise objectionable or violates any party’s intellectual property or these Terms of Service.
        You agree that your comments will not violate any right of any third-party, including copyright, trademark, privacy, personality or other personal or proprietary right. You further agree that your comments will not contain libelous or otherwise unlawful, abusive or obscene material, or contain any computer virus or other malware that could in any way affect the operation of the Service or any related website. You may not use a false e‑mail address, pretend to be someone other than yourself, or otherwise mislead us or third-parties as to the origin of any comments. You are solely responsible for any comments you make and their accuracy. We take no responsibility and assume no liability for any comments posted by you or any third-party.
      </p>

      <p><strong>SECTION 10 - PERSONAL INFORMATION</strong><br>
      Your submission of personal information through the store is governed by our Privacy Policy. To view our Privacy Policy.
      </p>

      <p><strong>SECTION 11 - ERRORS, INACCURACIES AND OMISSIONS</strong><br>
        Occasionally there may be information on our site or in the Service that contains typographical errors, inaccuracies or omissions that may relate to product descriptions, pricing, promotions, offers, product shipping charges, transit times and availability. We reserve the right to correct any errors, inaccuracies or omissions, and to change or update information or cancel orders if any information in the Service or on any related website is inaccurate at any time without prior notice (including after you have submitted your order).
        We undertake no obligation to update, amend or clarify information in the Service or on any related website, including without limitation, pricing information, except as required by law. No specified update or refresh date applied in the Service or on any related website, should be taken to indicate that all information in the Service or on any related website has been modified or updated.
      </p>

      <p><strong>SECTION 12 - PROHIBITED USES</strong><br>
        In addition to other prohibitions as set forth in the Terms of Service, you are prohibited from using the site or its content: (a) for any unlawful purpose; (b) to solicit others to perform or participate in any unlawful acts; (c) to violate any international, federal, provincial or state regulations, rules, laws, or local ordinances; (d) to infringe upon or violate our intellectual property rights or the intellectual property rights of others; (e) to harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate based on gender, sexual orientation, religion, ethnicity, race, age, national origin, or disability; (f) to submit false or misleading information; (g) to upload or transmit viruses or any other type of malicious code that will or may be used in any way that will affect the functionality or operation of the Service or of any related website, other websites, or the Internet; (h) to collect or track the personal information of others; (i) to spam, phish, pharm, pretext, spider, crawl, or scrape; (j) for any obscene or immoral purpose; or (k) to interfere with or circumvent the security features of the Service or any related website, other websites, or the Internet. We reserve the right to terminate your use of the Service or any related website for violating any of the prohibited uses.
      </p>

      <p><strong>SECTION 13 - DISCLAIMER OF WARRANTIES; LIMITATION OF LIABILITY</strong><br>
        We do not guarantee, represent or warrant that your use of our service will be uninterrupted, timely, secure or error-free.
        We do not warrant that the results that may be obtained from the use of the service will be accurate or reliable.
        You agree that from time to time we may remove the service for indefinite periods of time or cancel the service at any time, without notice to you.
        You expressly agree that your use of, or inability to use, the service is at your sole risk. The service and all products and services delivered to you through the service are (except as expressly stated by us) provided 'as is' and 'as available' for your use, without any representation, warranties or conditions of any kind, either express or implied, including all implied warranties or conditions of merchantability, merchantable quality, fitness for a particular purpose, durability, title, and non-infringement.
        In no case shall Irresistibly Minimal, our directors, officers, employees, affiliates, agents, contractors, interns, suppliers, service providers or licensors be liable for any injury, loss, claim, or any direct, indirect, incidental, punitive, special, or consequential damages of any kind, including, without limitation lost profits, lost revenue, lost savings, loss of data, replacement costs, or any similar damages, whether based in contract, tort (including negligence), strict liability or otherwise, arising from your use of any of the service or any products procured using the service, or for any other claim related in any way to your use of the service or any product, including, but not limited to, any errors or omissions in any content, or any loss or damage of any kind incurred as a result of the use of the service or any content (or product) posted, transmitted, or otherwise made available via the service, even if advised of their possibility. Because some states or jurisdictions do not allow the exclusion or the limitation of liability for consequential or incidental damages, in such states or jurisdictions, our liability shall be limited to the maximum extent permitted by law.
      </p>

      <p><strong>SECTION 14 - INDEMNIFICATION</strong><br>
        You agree to indemnify, defend and hold harmless Irresistibly Minimal and our parent, subsidiaries, affiliates, partners, officers, directors, agents, contractors, licensors, service providers, subcontractors, suppliers, interns and employees, harmless from any claim or demand, including reasonable attorneys’ fees, made by any third-party due to or arising out of your breach of these Terms of Service or the documents they incorporate by reference, or your violation of any law or the rights of a third-party.
      </p>
      
      <p><strong>SECTION 15 - SEVERABILITY</strong><br>
        In the event that any provision of these Terms of Service is determined to be unlawful, void or unenforceable, such provision shall nonetheless be enforceable to the fullest extent permitted by applicable law, and the unenforceable portion shall be deemed to be severed from these Terms of Service, such determination shall not affect the validity and enforceability of any other remaining provisions.
      </p>

      <p><strong>SECTION 16 - TERMINATION</strong><br>
        The obligations and liabilities of the parties incurred prior to the termination date shall survive the termination of this agreement for all purposes.
        These Terms of Service are effective unless and until terminated by either you or us. You may terminate these Terms of Service at any time by notifying us that you no longer wish to use our Services, or when you cease using our site.
        If in our sole judgment you fail, or we suspect that you have failed, to comply with any term or provision of these Terms of Service, we also may terminate this agreement at any time without notice and you will remain liable for all amounts due up to and including the date of termination; and/or accordingly may deny you access to our Services (or any part thereof).
      </p>

      <p><strong>SECTION 17 - ENTIRE AGREEMENT</strong><br>
        The failure of us to exercise or enforce any right or provision of these Terms of Service shall not constitute a waiver of such right or provision.
        These Terms of Service and any policies or operating rules posted by us on this site or in respect to The Service constitutes the entire agreement and understanding between you and us and govern your use of the Service, superseding any prior or contemporaneous agreements, communications and proposals, whether oral or written, between you and us (including, but not limited to, any prior versions of the Terms of Service).
        Any ambiguities in the interpretation of these Terms of Service shall not be construed against the drafting party.
      </p>

      <p><strong>SECTION 18 - GOVERNING LAW</strong><br>
        These Terms of Service and any separate agreements whereby we provide you Services shall be governed by and construed in accordance with the laws of United States.
      </p>

      <p><strong>SECTION 19 - CHANGES TO TERMS OF SERVICE</strong><br>
        You can review the most current version of the Terms of Service at any time at this page.
        We reserve the right, at our sole discretion, to update, change or replace any part of these Terms of Service by posting updates and changes to our website. It is your responsibility to check our website periodically for changes. Your continued use of or access to our website or the Service following the posting of any changes to these Terms of Service constitutes acceptance of those changes.
      </p>

      <p><strong>SECTION 20 - CONTACT INFORMATION</strong><br>
        Questions about the Terms of Service should be sent to us at hello@irresistiblyminimal.com.
      </p>

      <div class="line-bottom mb-4 mx-auto" ></div>


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


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/scrips.js"></script>

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
