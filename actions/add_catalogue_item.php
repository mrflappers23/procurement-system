<?php

require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';

require_login();

$stmt = $pdo->prepare("
INSERT INTO supplier_catalogue
(
    supplier_id,
    product_name,
    description,
    unit,
    unit_price
)
VALUES
(
    ?, ?, ?, ?, ?
)
");

$stmt->execute([

    $_POST['supplier_id'],

    trim($_POST['product_name']),

    trim($_POST['description']) ?: null,

    trim($_POST['unit']),

    $_POST['unit_price']

]);

header("Location: ../supplier_view.php?id=".$_POST['supplier_id']);
exit;