<?php
require '../session_check.php';
if ($_SESSION['rol'] !== 'admin') exit;

require '../config.php';
$pdo = getDBConnection();

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM MONTO WHERE id_monto = ?")->execute([(int)$_GET['delete']]);
    header("Location: monto_view.php");
    exit;
}

if ($_POST) {
    $pdo->prepare("INSERT INTO MONTO (tipo_personal, tipo_monto, monto) VALUES (?,?,?)")
        ->execute([$_POST['tipo_personal'], $_POST['tipo_monto'], (int)$_POST['monto']]);
    header("Location: monto_view.php");
    exit;
}