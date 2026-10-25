
<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$error = '';

$doctorId = filter_input(INPUT_GET, 'doctor_id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctorId = filter_input(INPUT_POST, 'doctor_id', FILTER_VALIDATE_INT);
}

if (!$doctorId || $doctorId < 1) {
    header('Location: doctors.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$doctorId]);
$doctor = $stmt->fetch();

if (!$doctor) {
    exit('Doctor not found.');
}

$availableSlots = [
    '09:00', '10:00', '11:00', '12:00',
    '14:00', '15:00', '16:00', '17:00'
];

$date = $_POST['appointment_date'] ?? '';
$time = $_POST['appointment_time'] ?? '';
$reason = trim($_POST['reason'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $today = date('Y-m-d');

    if ($date === '' || $date < $today) {
        $error = 'Please select today or a future date.';
    } elseif (!in_array($time, $availableSlots, true)) {
        $error = 'Please select a valid appointment time.';
    } elseif ($reason === '' || strlen($reason) > 255) {
        $error = 'Enter a reason of up to 255 characters.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO appointments
                (user_id, doctor_id, appointment_date,
                 appointment_time, reason)
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $userId,
                $doctorId,
                $date,
                $time . ':00',
                $reason
            ]);

            header('Location: my-appointments.php?booked=1');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'This doctor already has a booking at that date and time. Please choose another slot.';
            } else {
                error_log($e->getMessage());
                $error = 'Unable to book the appointment. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - MediQueue</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .booking-card {
            max-width: 560px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px #164e6315;
        }

        .booking-card h2 {
            color: #075078;
            margin-bottom: 20px;
        }

        .doctor-info {
            background: #e1f4ff;
            padding: 15px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        .doctor-info h3 {
            color: #075078;
            margin-bottom: 5px;
        }

        .booking-card label {
            display: block;
            margin: 15px 0 7px;
            color: #34566b;
            font-size: 14px;
        }

        .booking-card input,
        .booking-card select,
        .booking-card textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d4e2eb;
            border-radius: 6px;
            font: inherit;
        }

        .booking-card textarea {
            resize: vertical;
        }

        .booking-card button {
            width: 100%;
            margin-top: 20px;
            padding: 13px;
            background: #10a8e4;
            color: white;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }

        .booking-card button:hover {
            background: #078dc5;
        }

        .error {
            background: #fff0f0;
            color: #a52222;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .back-link {
            display: inline-block;
            margin-top: 18px;
            color: #0877b4;
            text-decoration: none;
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

<main class="booking-card">
    <h2>Book Appointment</h2>

    <div class="doctor-info">
        <h3><?= htmlspecialchars($doctor['name']) ?></h3>
        <p>
            <?= htmlspecialchars($doctor['specialization']) ?>
            · ₹<?= number_format((float)$doctor['fee'], 2) ?>
        </p>
    </div>

    <?php if ($error !== ''): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="book-appointment.php">
        <input type="hidden" name="doctor_id"
               value="<?= (int)$doctor['id'] ?>">

        <label for="appointment_date">Appointment Date</label>
        <input
            type="date"
            id="appointment_date"
            name="appointment_date"
            min="<?= date('Y-m-d') ?>"
            value="<?= htmlspecialchars($date) ?>"
            required
        >

        <label for="appointment_time">Appointment Time</label>
        <select id="appointment_time" name="appointment_time" required>
            <option value="">Select a time</option>
            <?php foreach ($availableSlots as $slot): ?>
                <option value="<?= $slot ?>"
                    <?= $time === $slot ? 'selected' : '' ?>>
                    <?= date('h:i A', strtotime($slot)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="reason">Reason for Visit</label>
        <textarea
            id="reason"
            name="reason"
            rows="3"
            maxlength="255"
            placeholder="e.g. General checkup"
            required
        ><?= htmlspecialchars($reason) ?></textarea>

        <button type="submit">Confirm Booking</button>
    </form>

    <a class="back-link" href="doctors.php">← Back to Doctors</a>
</main>

</body>
</html>
