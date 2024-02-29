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

    $jsonData = file_get_contents('data.json');
    $data = json_decode($jsonData, true);

    foreach ($data as $value) {
        echo $value['product_name'] . "<br>";
    }

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }



    $conn->close();
    ?>
</body>

</html>