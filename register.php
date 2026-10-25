
<?php
require_once __DIR__ . '/config/database.php';

$error = '';
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid name and email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password)
                 VALUES (?, ?, ?)"
            );
            $stmt->execute([$name, $email, $hash]);

            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - MediQueue</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-card {
            max-width: 440px;
            margin: 45px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px #164e6315;
        }
        .auth-card h2 {
            color: #075078;
            margin-bottom: 22px;
        }
        .auth-card label {
            display: block;
            margin: 15px 0 7px;
        }
        .auth-card input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d4e2eb;
            border-radius: 6px;
        }
        .auth-card button {
            width: 100%;
            margin-top: 22px;
            padding: 13px;
            background: #10a8e4;
            color: white;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }
        .auth-card .error {
            color: #a52222;
            background: #fff0f0;
            padding: 12px;
            border-radius: 6px;
        }
        .auth-card a {
            color: #0877b4;
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
    </nav>
</header>

<main class="auth-card">
    <h2>Create Your Account</h2>

    <?php if ($error !== ''): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="name">Full Name</label>
        <input id="name" name="name" maxlength="100"
               required value="<?= htmlspecialchars($name) ?>">

        <label for="email">Email Address</label>
        <input id="email" type="email" name="email" maxlength="150"
               required value="<?= htmlspecialchars($email) ?>">

        <label for="password">Password</label>
        <input id="password" type="password" name="password"
               minlength="8" required>

        <label for="confirm_password">Confirm Password</label>
        <input id="confirm_password" type="password"
               name="confirm_password" minlength="8" required>

        <button type="submit">Create Account</button>
    </form>

    <p style="margin-top:18px;">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</main>
</body>
</html>
