
<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT a.*, d.name AS doctor_name, d.specialization
     FROM appointments a
     JOIN doctors d ON d.id = a.doctor_id
     WHERE a.user_id = ?
     ORDER BY a.appointment_date DESC, a.appointment_time DESC"
);
$stmt->execute([(int)$_SESSION['user_id']]);
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - MediQueue</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .appointments {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .appointments h1 {
            color: #075078;
            margin-bottom: 20px;
        }

        .table-wrap {
            overflow-x: auto;
            background: white;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th {
            background: #104b6b;
            color: white;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #e5edf3;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            background: #fff0cf;
            color: #8b6200;
        }

        .success {
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 6px;
            background: #e4f9ec;
            color: #187442;
        }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">MediQueue</a>
    <nav>
        <a href="index.php">Home</a>
        <a href="doctors.php">Doctors</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="appointments">
    <h1>My Appointments</h1>

    <?php if (isset($_GET['booked']) && $_GET['booked'] === '1'): ?>
        <div class="success">Appointment booked successfully!</div>
    <?php endif; ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Doctor</th>
                    <th>Specialization</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Reason</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$appointments): ?>
                    <tr>
                        <td colspan="6">
                            No appointments yet. Visit the Doctors page to book one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($appointments as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                            <td><?= htmlspecialchars($a['specialization']) ?></td>
                            <td><?= htmlspecialchars($a['appointment_date']) ?></td>
                            <td><?= date('h:i A', strtotime($a['appointment_time'])) ?></td>
                            <td><?= htmlspecialchars($a['reason']) ?></td>
                            <td><span class="status"><?= htmlspecialchars($a['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
