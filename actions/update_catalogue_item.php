<?php

require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';

require_login();

$stmt = $pdo->prepare("
UPDATE supplier_catalogue
SET
    product_name = ?,
    description = ?,
    unit = ?,
    unit_price = ?
WHERE catalogue_id = ?
");

$stmt->execute([

    trim($_POST['product_name']),

    trim($_POST['description']) ?: null,

    trim($_POST['unit']),

    $_POST['unit_price'],

    $_POST['catalogue_id']

]);

header("Location: ../supplier_view.php?id=".$_POST['supplier_id']);
exit;