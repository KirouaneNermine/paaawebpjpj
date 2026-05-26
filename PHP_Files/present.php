<?php
session_start();
$conn = mysqli_connect("localhost", "root", "", "circusas_db");


if(isset($_POST['add_to_cart'])){
    if(!isset($_SESSION['user_id'])){
        $msg = "You must be logged in to buy!";

    } else {

        $user_id = $_SESSION['user_id'];
        $product_id = $_POST['product_id'];
        $qty_wanted = $_POST['quantity'];
        $sql1 = "SELECT * FROM products 
                WHERE id=$product_id";
        // ading to cart logic
        // getting all the products theuser selected 
        // in a the cart

        $prod = mysqli_fetch_assoc(mysqli_query($conn, $sql1));

        if($prod['quantity'] < $qty_wanted){
            $msg = "Not enough stock!";
            // checking quantityof the stock

        } else {
            $sql2 = "SELECT * FROM cart 
                    WHERE user_id=$user_id 
                    AND product_id=$product_id";
            $check = mysqli_fetch_assoc(mysqli_query($conn, $sql2));

            if($check){
                // upadate the qunatity if we wanted more
                $new_qty = $check['quantity'] + $qty_wanted;
                $sql3 = "UPDATE cart SET 
                         quantity=$new_qty 
                         WHERE user_id=$user_id 
                         AND product_id=$product_id";
                mysqli_query($conn, $sql3);
            } else {
                // if i didnt choos that product yet ill change add to the cart the products after checking

                $sql4 = "INSERT INTO cart
                        (user_id, product_id, quantity) 
                        VALUES ($user_id, $product_id, $qty_wanted)";

                mysqli_query($conn, $sql4);
            }
            $msg = "Added to cart!";

        }
    }
}

//remove items
if(isset($_POST['remove_item'])){
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $sql5 = "DELETE FROM cart
             WHERE user_id=$user_id 
             AND product_id=$product_id";
    mysqli_query($conn, $sql5);
}
// giving the right to the user to remove all the items he wanna buy


//clear cart
if(isset($_POST['clear_cart'])){
    $user_id = $_SESSION['user_id'];
    $sql6 =  "DELETE FROM cart 
              WHERE user_id=$user_id";
    mysqli_query($conn, $sql6);
}
// giving him the right to anuller the command 


if(isset($_POST['buy_now'])){
    $user_id = $_SESSION['user_id'];
    $sql7 = "SELECT cart.quantity, cart.product_id, products.price, products.quantity
             as stock FROM cart 
             JOIN products ON cart.product_id = products.id
             WHERE cart.user_id=$user_id";
             //getting info pour calculer the price 


    $cart_q = mysqli_query($conn, $sql7);
    $items = [];
    //gettingthe items as rows storing them tp use them to chnage infos
    $total = 0;
    while($row = mysqli_fetch_assoc($cart_q)){
        $items[] = $row;
        $total += $row['price'] * $row['quantity'];
        //prix calculer here
    }

    $sql8 = "SELECT balance FROM users WHERE id=$user_id";
    //get balance
    $user = mysqli_fetch_assoc(mysqli_query($conn, $sql8));

    if($user['balance'] < $total){
        $msg = "Insufficient balance!";
        //if balance is less 
    } else {
        $out_of_stock = false;
        foreach($items as $item){
            if($item['stock'] < $item['quantity']){ 
                $out_of_stock = true;
                break;
                // make the products out of stock
            }
        }

        if($out_of_stock){
            $msg = "One product is out of stock!";
        } else {
            // we chnage the balance when he buy it
            mysqli_query($conn, "UPDATE users SET balance = balance - $total WHERE id=$user_id");
            foreach($items as $item){
                mysqli_query($conn, "UPDATE products SET quantity 
                                    = quantity - {$item['quantity']}
                                     WHERE id={$item['product_id']}");
                                     // change quantité we got stored at items
            }
            mysqli_query($conn, "DELETE FROM cart WHERE user_id=$user_id");
            $msg = "Purchase successful!";
        }
    }
}


$result = mysqli_query($conn, "SELECT * FROM products");


$cart_items = [];
$cart_total = 0;
$balance = null;

if(isset($_SESSION['user_id'])){
    $uid = $_SESSION['user_id'];
    $sql11= "SELECT balance FROM users
             WHERE id=$uid";
    $balance = mysqli_fetch_assoc(mysqli_query($conn, $sql11))['balance'];
    $sql12 =  "SELECT cart.quantity, cart.product_id, products.name, products.price, products.image
               FROM cart JOIN products ON cart.product_id = products.id 
               WHERE cart.user_id=$uid";

    $cart_q = mysqli_query($conn,$sql12);
    while($ci = mysqli_fetch_assoc($cart_q)){
        // getting info aboutitems to putthem in the cart
        // calculatethe price total of the products that we wanna purchase
        $cart_items[] = $ci;
        $cart_total += $ci['price'] * $ci['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=UnifrakturMaguntia&family=Cinzel+Decorative&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="present.css">
    <title>Products:</title>
</head>
<body>

    <header class="hero">
        <img src="bgads.jpg" alt="collection banner">
        <div class="hero-text">
            <h2>Check Our Collection</h2>
            <p><span>90% OFF</span> !!!</p>
        </div>
    </header>

    <main>
        <div class="inf">
            <p>FREE SHIPPING OVER $60</p>
            <p>UP TO 90% OFF</p>
            <p>FREE SHIPPING OVER $60</p>
        </div>

        <?php if(isset($msg)) echo "<p>$msg</p>"; ?>

        <div id="popo">
       <!-- Products -->
        <?php while($product = mysqli_fetch_assoc($result)): ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                </div>
                <div class="product-info">
                    <h3 class="product-name"><?php echo $product['name']; ?></h3>
                    <p class="product-price">$<?php echo $product['price']; ?></p>
                    <p class="product-stock"><?php echo $product['quantity'] > 0 ? "In stock: ".$product['quantity'] : "Out of stock"; ?></p>
                </div>
                <div class="product-action">
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <p class="product-login">Please login to buy</p>
                    <?php elseif($product['quantity'] > 0): ?>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <input class="product-qty" type="number" name="quantity" value="1" min="1" max="<?php echo $product['quantity']; ?>">
                            <button class="product-btn" type="submit" name="add_to_cart">Add to Cart</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
       
        <div>
            <h2>Your Cart</h2>
            <?php if(count($cart_items) == 0): ?>

                <p>No products selected</p>
            <?php else: ?>
                <?php foreach($cart_items as $ci): ?>
                    <div>

                        <p><?php echo $ci['name']; ?></p>
                        <p>Qty: <?php echo $ci['quantity']; ?></p>
                        <p>$<?php echo number_format($ci['price'] * $ci['quantity'], 2); ?></p>

                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $ci['product_id']; ?>">
                            <button type="submit" name="remove_item">Remove</button>
                        </form>
                    </div>
                <?php endforeach; ?>

                <p>Total: $<?php echo number_format($cart_total, 2); ?></p>
                <p>Your balance: $<?php echo number_format($balance, 2); ?></p>
                <?php if($cart_total > $balance): ?>
                    <p>Over budget!</p>
                <?php endif; ?>

                <form method="POST">
                    <button type="submit" name="buy_now" <?php if($cart_total > $balance) echo 'disabled'; ?>>Confirm Order</button>
                </form>
                <form method="POST">
                    <button type="submit" name="clear_cart">Cancel Cart</button>
                </form>
            <?php endif; ?>
        </div>

    </main>

    <footer>
        <p>&copy;2025 Circusas. All rights reserved.</p>
    </footer>

</body>
</html>