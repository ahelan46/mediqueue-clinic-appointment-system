
<?php
require_once __DIR__ . '/config/database.php';

$success = '';
$error = '';

$name = '';
$specialization = '';
$qualification = '';
$experience = '';
$fee = '';
$available_days = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $experience = $_POST['experience'] ?? '';
    $fee = $_POST['fee'] ?? '';
    $available_days = trim($_POST['available_days'] ?? '');

    if (
        $name === '' ||
        $specialization === '' ||
        $qualification === '' ||
        $available_days === '' ||
        $experience === '' ||
        $fee === ''
    ) {
        $error = 'Please fill in all fields.';
    } elseif (
        !is_numeric($experience) ||
        (int)$experience < 0 ||
        (int)$experience != $experience
    ) {
        $error = 'Enter a valid number of experience years.';
    } elseif (!is_numeric($fee) || (float)$fee < 0) {
        $error = 'Enter a valid consultation fee.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO doctors
                (name, specialization, qualification, experience,
                 fee, available_days)
                VALUES
                (:name, :specialization, :qualification, :experience,
                 :fee, :available_days)"
            );

            $stmt->execute([
                'name' => $name,
                'specialization' => $specialization,
                'qualification' => $qualification,
                'experience' => (int)$experience,
                'fee' => (float)$fee,
                'available_days' => $available_days
            ]);

            header('Location: doctors.php?added=1');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Unable to add the doctor. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Doctor - MediQueue</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        .form-container {
            width: 100%;
            max-width: 520px;
            margin: 45px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 65, 100, 0.10);
        }

        .form-container h2 {
            color: #075078;
            margin-bottom: 22px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            color: #34566b;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d4e2eb;
            border-radius: 6px;
            font-size: 14px;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #10a8e4;
        }

        .submit-button {
            width: 100%;
            padding: 13px;
            background: #10a8e4;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
        }

        .submit-button:hover {
            background: #078dc5;
        }

        .back-link {
            display: inline-block;
            margin-top: 18px;
            color: #0877b4;
            text-decoration: none;
        }

        .message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 18px;
        }

        .error {
            background: #fff0f0;
            color: #a52222;
        }

        @media (max-width: 600px) {
            .form-container {
                width: calc(100% - 30px);
                padding: 22px;
                margin: 25px auto;
            }
        }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">MediQueue</a>
    <nav>
        <a href="index.php">Home</a>
        <a href="doctors.php">Doctors</a>
        <a href="login.php">Login</a>
        <a href="register.php" class="signup">Sign Up</a>
    </nav>
</header>

<main class="form-container">
    <h2>Add New Doctor</h2>

    <?php if ($error !== ''): ?>
        <div class="message error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="add-doctor.php">
        <div class="form-group">
            <label for="name">Doctor Name</label>
            <input type="text" id="name" name="name"
                   maxlength="100" required
                   value="<?= htmlspecialchars($name) ?>">
        </div>

        <div class="form-group">
            <label for="specialization">Specialization</label>
            <input type="text" id="specialization"
                   name="specialization" maxlength="100"
                   required value="<?= htmlspecialchars($specialization) ?>">
        </div>

        <div class="form-group">
            <label for="qualification">Qualification</label>
            <input type="text" id="qualification"
                   name="qualification" maxlength="100"
                   required value="<?= htmlspecialchars($qualification) ?>">
        </div>

        <div class="form-group">
            <label for="experience">Years of Experience</label>
            <input type="number" id="experience"
                   name="experience" min="0" max="100"
                   step="1" required
                   value="<?= htmlspecialchars((string)$experience) ?>">
        </div>

        <div class="form-group">
            <label for="fee">Consultation Fee (₹)</label>
            <input type="number" id="fee" name="fee"
                   min="0" max="99999999.99"
                   step="0.01" required
                   value="<?= htmlspecialchars((string)$fee) ?>">
        </div>

        <div class="form-group">
            <label for="available_days">Available Days</label>
            <input type="text" id="available_days"
                   name="available_days"
                   placeholder="e.g. Mon - Fri"
                   maxlength="100" required
                   value="<?= htmlspecialchars($available_days) ?>">
        </div>

        <button type="submit" class="submit-button">
            Add Doctor
        </button>
    </form>

    <a href="doctors.php" class="back-link">
        ← Back to Doctors
    </a>
</main>

</body>
</html>
