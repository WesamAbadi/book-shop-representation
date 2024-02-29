<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h2>shop data</h2>

    <?php
    require_once('db_config.php');
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


    $jsonData = file_get_contents('data.json');
    $data = json_decode($jsonData, true);

    foreach ($data as $key => $value) {
        $sale_id = $value['sale_id'];
        $customer_name = $value['customer_name'];
        $customer_mail = $value['customer_mail'];
        $product_id = $value['product_id'];
        $product_name = $value['product_name'];
        $product_price = $value['product_price'];
        $sale_date = $value['sale_date'];


        $customerQuery = "SELECT id FROM customers WHERE name = '$customer_name'";
        $customerResult = $conn->query($customerQuery);
        if ($customerResult->num_rows == 0) {
            $conn->query("INSERT INTO customers (name) VALUES ('$customer_name')");
            $customerId = $conn->insert_id;
        } else {
            $customerRow = $customerResult->fetch_assoc();
            $customerId = $customerRow['id'];
        }

        $productQuery = "SELECT id FROM products WHERE name = ?";
        $productStmt = $conn->prepare($productQuery);
        $productStmt->bind_param("s", $product_name);
        $productStmt->execute();
        $productResult = $productStmt->get_result();

        if ($productResult->num_rows == 0) {
            $productInsertQuery = "INSERT INTO products (name, price) VALUES (?, ?)";
            $productInsertStmt = $conn->prepare($productInsertQuery);
            $productInsertStmt->bind_param("sd", $product_name, $product_price);
            $productInsertStmt->execute();
            $productId = $conn->insert_id;
            $productInsertStmt->close();
        } else {
            $productRow = $productResult->fetch_assoc();
            $productId = $productRow['id'];
        }

        $saleQuery = "INSERT INTO sales (id, customer_id, product_id, sale_date) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE id = id";
        $saleStmt = $conn->prepare($saleQuery);
        $saleStmt->bind_param("iisi", $sale_id, $customerId, $productId, $sale_date);
        $saleStmt->execute();
        $saleStmt->close();
    }

    $productDisplayQuery = "SELECT * FROM products";
    $productDisplayResult = $conn->query($productDisplayQuery);

    if ($productDisplayResult->num_rows > 0) {
        echo "<h3>Products:</h3>";
        echo "<ul>";
        while ($row = $productDisplayResult->fetch_assoc()) {
            echo "<li>" . $row['name'] . " - $" . $row['price'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "No products found.";
    }

    $conn->close();
    ?>

    <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <label for="customer_filter">Customer:</label>
        <input type="text" id="customer_filter" name="customer_filter">
        <label for="product_filter">Product:</label>
        <input type="text" id="product_filter" name="product_filter">
        <label for="min_price">Min Price:</label>
        <input type="number" id="min_price" name="min_price" value="0">
        <label for="max_price">Max Price:</label>
        <input type="number" id="max_price" name="max_price" value="100">
        <input type="submit" value="Filter">
    </form>

    <?php
    require_once('db_config.php');
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $customer_filter = isset($_GET['customer_filter']) ? $_GET['customer_filter'] : '';
    $product_filter = isset($_GET['product_filter']) ? $_GET['product_filter'] : '';
    $min_price = isset($_GET['min_price']) ? $_GET['min_price'] : null;
    $max_price = isset($_GET['max_price']) ? $_GET['max_price'] : null;

    $query = "SELECT sales.id, customers.name AS customer_name, products.name AS product_name, products.price 
              FROM sales 
              INNER JOIN customers ON sales.customer_id = customers.id 
              INNER JOIN products ON sales.product_id = products.id 
              WHERE customers.name LIKE '%$customer_filter%' 
              AND products.name LIKE '%$product_filter%'";

    if ($min_price !== null) {
        $query .= " AND products.price >= $min_price";
    }

    if ($max_price !== null) {
        $query .= " AND products.price <= $max_price";
    }

    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo "<h3>Filtered Results:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Sale ID</th><th>Customer Name</th><th>Product Name</th><th>Product Price</th></tr>";

        $total_price = 0;

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['customer_name'] . "</td>";
            echo "<td>" . $row['product_name'] . "</td>";
            echo "<td>$" . $row['price'] . "</td>";
            echo "</tr>";
            $total_price += $row['price'];
        }

        echo "<tr><td colspan='3'>Total Price:</td><td>$total_price</td></tr>";
        echo "</table>";
    } else {
        echo "Nothing";
    }

    $conn->close();
    ?>
</body>

</html>