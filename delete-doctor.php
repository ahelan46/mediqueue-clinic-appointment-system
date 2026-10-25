
<?php
require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: doctors.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: doctors.php?error=invalid');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
$stmt->execute([$id]);

header(
    'Location: doctors.php?deleted=' .
    ($stmt->rowCount() > 0 ? '1' : '0')
);
exit;
