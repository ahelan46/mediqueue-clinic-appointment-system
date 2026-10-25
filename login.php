
<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = isset($_GET['registered'])
    ? 'Account created successfully. Please log in.'
    : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT id, name, email, password, role
         FROM users WHERE email = ?"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid email address or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MediQueue</title>
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
        .message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        .error {
            background: #fff0f0;
            color: #a52222;
        }
        .success {
            background: #e4f9ec;
            color: #187442;
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
        <a href="register.php">Sign Up</a>
    </nav>
</header>

<main class="auth-card">
    <h2>Welcome Back</h2>

    <?php if ($success !== ''): ?>
        <p class="message success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <p class="message error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="email">Email Address</label>
        <input id="email" type="email" name="email"
               maxlength="150" required>

        <label for="password">Password</label>
        <input id="password" type="password"
               name="password" required>

        <button type="submit">Login</button>
    </form>

    <p style="margin-top:18px;">
        New to MediQueue? <a href="register.php">Create an account</a>
    </p>
</main>
</body>
</html>
