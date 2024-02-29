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
    $sql = "
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL
);

CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    sale_date DATE NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);";

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($conn->multi_query($sql) === TRUE) {
        echo "Tables created successfully";
    } else {
        echo "Error creating tables: " . $conn->error;
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
    }



    $conn->close();
    ?>
</body>

</html>