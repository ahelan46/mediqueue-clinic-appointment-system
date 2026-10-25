
<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM appointments
     WHERE user_id = ?"
);
$stmt->execute([$userId]);
$totalAppointments = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM appointments
     WHERE user_id = ? AND appointment_date >= CURDATE()
       AND status = 'Booked'"
);
$stmt->execute([$userId]);
$upcomingAppointments = (int)$stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MediQueue</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .dashboard {
            max-width: 1000px;
            margin: 35px auto;
            padding: 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin: 25px 0;
        }
        .stat-card {
            background: white;
            padding: 25px;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 14px #164e6310;
        }
        .stat-card h2 {
            color: #10a8e4;
            font-size: 32px;
        }
        .dashboard-links {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .dashboard-links a {
            background: #10a8e4;
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 6px;
        }
        @media(max-width:600px) {
            .stats { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<header class="navbar">
    <a href="index.php" class="logo">MediQueue</a>
    <nav>
        <a href="index.php">Home</a>
        <a href="doctors.php">Doctors</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="dashboard">
    <h1>Hello, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
    <p>Welcome to your MediQueue dashboard.</p>

    <section class="stats">
        <div class="stat-card">
            <h2><?= $totalAppointments ?></h2>
            <p>My Appointments</p>
        </div>
        <div class="stat-card">
            <h2><?= $upcomingAppointments ?></h2>
            <p>Upcoming Appointments</p>
        </div>
    </section>

    <div class="dashboard-links">
        <a href="doctors.php">Find a Doctor</a>
        <a href="my-appointments.php">View Appointments</a>
    </div>
</main>
</body>
</html>
