<?php

require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';

require_login();

$get = $pdo->prepare("
SELECT supplier_id
FROM supplier_catalogue
WHERE catalogue_id = ?
");

$get->execute([$_GET['id']]);

$item = $get->fetch();

if(!$item){
    die("Item not found.");
}

$delete = $pdo->prepare("
DELETE FROM supplier_catalogue
WHERE catalogue_id = ?
");

$delete->execute([$_GET['id']]);

header("Location: ../supplier_view.php?id=".$item['supplier_id']);
exit;