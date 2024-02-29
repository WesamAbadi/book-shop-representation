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

    $conn->close();
    ?>
</body>

</html>