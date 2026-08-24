<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = $_POST['customer_name'];
    $productName = $_POST['product_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $total = $quantity * $price;
    $companyName = "91 𝐂𝐋𝐔𝐁";

    // Create PDF using DOMPDF
    $dompdf = new Dompdf();
    $html = "
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            h1 { text-align: center; color: #333; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
            th { background-color: #f4f4f4; }
            .total { font-weight: bold; }
        </style>
        <h1>Invoice - $companyName</h1>
        <p><strong>Customer Name:</strong> $customerName</p>
        <table>
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
            <tr>
                <td>$productName</td>
                <td>$quantity</td>
                <td>₹$price</td>
                <td>₹$total</td>
            </tr>
        </table>
        <p class='total'>Grand Total: ₹$total</p>
    ";

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Output as PDF download
    $dompdf->stream("invoice_$customerName.pdf", ["Attachment" => true]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create Invoice - 91 𝐂𝐋𝐔𝐁</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
            margin: 0;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 0 auto;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<h2 style="text-align: center;">Create Invoice - 91 𝐂𝐋𝐔𝐁</h2>

<form method="POST" action="">
    <label for="customer_name">Customer Name:</label>
    <input type="text" id="customer_name" name="customer_name" required />

    <label for="product_name">Product Name:</label>
    <input type="text" id="product_name" name="product_name" required />

    <label for="quantity">Quantity:</label>
    <input type="number" id="quantity" name="quantity" required />

    <label for="price">Price per Item (₹):</label>
    <input type="number" id="price" name="price" step="0.01" required />

    <button type="submit">Generate Invoice</button>
</form>

</body>
</html>
