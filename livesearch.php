<?php
include("php/db.php");
if(isset($_POST['input'])){
    $input = $_POST['input'];

    $query = "SELECT * FROM products WHERE productName LIKE '{$input}%'";

    $result = mysqli_query($conn,$query);

    if(mysqli_num_rows($result) > 0){ ?>


<?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $productPrice = $row["productPrice"];
                    echo '<div class="col-sm-6 col-md-4 mb-4 id="items-grid">';
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
                echo '<p> No products available. </p>';
            }
            ?>

    
   

    <?php

    }
}
?>