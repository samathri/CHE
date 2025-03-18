<?php
session_start();
include './php/db.php';

// Handle form submission to add product to cart
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $size = isset($_POST['size']) ? $_POST['size'] : '7'; 
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

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

        echo "<script>alert('Product added to cart'); window.location.href = 'mycart.php';</script>";
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

// Insert Cart Data into Database when Proceed to Checkout
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
    <title>Your Cart</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .cart-items { margin-bottom: 15px; }
        .cart-item { border-bottom: 1px solid #ddd; padding-bottom: 15px; margin-bottom: 15px; }
        .quantity-box { width: 100px; display: flex; justify-content: space-between; }
        .cart-item-name { font-weight: bold; }
        .cart-item-price { font-weight: bold; font-size: 1.2em; }
        .btn-close { background: none; border: none; }
        .special-instructions textarea { margin-top: 10px; }
    </style>
</head>
<body>

<div class="container my-5">
    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                foreach ($_SESSION['cart'] as $index => &$item):
                    $item_total = $item['price'] * $item['quantity'];
                    $total += $item_total;
                ?>
                    <tr>
                        <td>
                            <img src="<?php echo $item['image']; ?>" alt="" class="img-thumbnail" style="width: 100px;">
                            <?php echo $item['name']; ?>
                        </td>
                        <td>
                            <div class="quantity-box">
                                <button class="btn btn-sm btn-outline-secondary quantity-decrease" data-index="<?php echo $index; ?>">-</button>
                                <input type="number" class="form-control form-control-sm text-center quantity-input" value="<?php echo $item['quantity']; ?>" min="1" data-index="<?php echo $index; ?>">
                                <button class="btn btn-sm btn-outline-secondary quantity-increase" data-index="<?php echo $index; ?>">+</button>
                            </div>
                        </td>
                        <td>
                            Rs <?php echo number_format($item['price'], 2); ?>
                        </td>
                        <td>
                            Rs <?php echo number_format($item_total, 2); ?>
                        </td>
                        <td>
                            <button class="btn btn-danger btn-sm delete-item" data-index="<?php echo $index; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Total: Rs <?php echo number_format($total, 2); ?></h3>
        <form method="POST" action="">
            <button type="submit" class="btn btn-success" name="checkout">Proceed to Checkout</button>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Increase Quantity
        $('.quantity-increase').click(function() {
            var index = $(this).data('index');
            var quantityInput = $('.quantity-input[data-index="'+index+'"]');
            var currentQuantity = parseInt(quantityInput.val());
            quantityInput.val(currentQuantity + 1);
            updateCart();
        });

        // Decrease Quantity
        $('.quantity-decrease').click(function() {
            var index = $(this).data('index');
            var quantityInput = $('.quantity-input[data-index="'+index+'"]');
            var currentQuantity = parseInt(quantityInput.val());
            if (currentQuantity > 1) {
                quantityInput.val(currentQuantity - 1);
                updateCart();
            }
        });

        // Delete Item
        $('.delete-item').click(function() {
            var index = $(this).data('index');
            $.ajax({
                url: '',
                type: 'POST',
                data: { delete_item: true, index: index },
                success: function(response) {
                    window.location.reload();
                }
            });
        });

        function updateCart() {
            var cartData = [];
            $('input.quantity-input').each(function() {
                var index = $(this).data('index');
                var quantity = $(this).val();
                cartData.push({ index: index, quantity: quantity });
            });

            $.ajax({
                url: '',
                type: 'POST',
                data: { update_cart: true, cartData: cartData },
                success: function(response) {
                    window.location.reload();
                }
            });
        }
    });
</script>

</body>
</html>
