
<?php
require_once __DIR__ . '/config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit('Invalid doctor ID.');
}

$stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
$stmt->execute([$id]);
$doctor = $stmt->fetch();

if (!$doctor) {
    exit('Doctor not found.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $experience = $_POST['experience'] ?? '';
    $fee = $_POST['fee'] ?? '';
    $available_days = trim($_POST['available_days'] ?? '');

    if (
        $name === '' || $specialization === '' ||
        $qualification === '' || $available_days === '' ||
        $experience === '' || $fee === ''
    ) {
        $error = 'Please fill in all fields.';
    } elseif (
        !ctype_digit((string)$experience) ||
        (int)$experience > 100
    ) {
        $error = 'Experience must be between 0 and 100 years.';
    } elseif (
        !is_numeric($fee) || (float)$fee < 0 ||
        (float)$fee > 99999999.99
    ) {
        $error = 'Enter a valid consultation fee.';
    } else {
        $stmt = $pdo->prepare(
            "UPDATE doctors SET
                name = ?,
                specialization = ?,
                qualification = ?,
                experience = ?,
                fee = ?,
                available_days = ?
             WHERE id = ?"
        );

        $stmt->execute([
            $name,
            $specialization,
            $qualification,
            (int)$experience,
            (float)$fee,
            $available_days,
            $id
        ]);

        header('Location: doctors.php?updated=1');
        exit;
    }

    $doctor['name'] = $name;
    $doctor['specialization'] = $specialization;
    $doctor['qualification'] = $qualification;
    $doctor['experience'] = $experience;
    $doctor['fee'] = $fee;
    $doctor['available_days'] = $available_days;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor - MediQueue</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .edit-box {
            max-width: 520px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 18px #164e6315;
        }
        .edit-box h2 {
            color: #075078;
            margin-bottom: 22px;
        }
        .edit-box label {
            display: block;
            margin: 14px 0 6px;
            font-size: 14px;
        }
        .edit-box input {
            width: 100%;
            padding: 11px;
            border: 1px solid #d4e2eb;
            border-radius: 6px;
        }
        .save-button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            color: white;
            background: #10a8e4;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }
        .back-link {
            display: inline-block;
            margin-top: 16px;
            color: #0877b4;
            text-decoration: none;
        }
        .error {
            color: #a52222;
            background: #fff0f0;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<header class="navbar">
    <a href="index.php" class="logo">MediQueue</a>
    <nav>
        <a href="index.php">Home</a>
        <a href="doctors.php">Doctors</a>
    </nav>
</header>

<main class="edit-box">
    <h2>Edit Doctor</h2>

    <?php if ($error !== ''): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="name">Doctor Name</label>
        <input id="name" name="name" required maxlength="100"
               value="<?= htmlspecialchars($doctor['name']) ?>">

        <label for="specialization">Specialization</label>
        <input id="specialization" name="specialization"
               required maxlength="100"
               value="<?= htmlspecialchars($doctor['specialization']) ?>">

        <label for="qualification">Qualification</label>
        <input id="qualification" name="qualification"
               required maxlength="100"
               value="<?= htmlspecialchars($doctor['qualification']) ?>">

        <label for="experience">Years of Experience</label>
        <input id="experience" type="number" name="experience"
               min="0" max="100" step="1" required
               value="<?= htmlspecialchars((string)$doctor['experience']) ?>">

        <label for="fee">Consultation Fee (₹)</label>
        <input id="fee" type="number" name="fee"
               min="0" max="99999999.99" step="0.01" required
               value="<?= htmlspecialchars((string)$doctor['fee']) ?>">

        <label for="available_days">Available Days</label>
        <input id="available_days" name="available_days"
               maxlength="100" required
               value="<?= htmlspecialchars($doctor['available_days']) ?>">

        <button class="save-button" type="submit">Save Changes</button>
    </form>

    <a class="back-link" href="doctors.php">← Back to Doctors</a>
</main>
</body>
</html>
