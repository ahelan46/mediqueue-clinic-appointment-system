
<?php
require_once __DIR__ . '/config/database.php';

$search = trim($_GET['search'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare(
        "SELECT * FROM doctors
         WHERE name LIKE :search
         OR specialization LIKE :search
         ORDER BY name"
    );
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt = $pdo->query(
        "SELECT * FROM doctors ORDER BY name"
    );
}

$doctors = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Doctors - MediQueue</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .doctor-container {
            max-width: 1100px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .doctor-container h1 {
            color: #075078;
            margin-bottom: 22px;
        }

        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-form input {
            flex: 1;
            min-width: 0;
            padding: 12px;
            border: 1px solid #d6e2ec;
            border-radius: 6px;
        }

        .search-form button,
        .add-button {
            background: #10a8e4;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .table-wrapper {
            overflow-x: auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px #0b527010;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th {
            background: #104b6b;
            color: white;
            text-align: left;
            padding: 14px 12px;
            font-size: 13px;
        }

        td {
            padding: 14px 12px;
            border-bottom: 1px solid #e5edf3;
            font-size: 14px;
        }

        tr:hover td {
            background: #f4fbff;
        }

        .specialty {
            color: #0877a7;
            background: #def5ff;
            padding: 5px 9px;
            border-radius: 12px;
            white-space: nowrap;
        }

        .empty {
            padding: 25px;
            text-align: center;
            color: #657786;
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

<main class="doctor-container">
    
    <?php if (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>
        <p style="background:#e4f9ec;color:#187442;padding:12px;border-radius:6px;">
            Doctor details updated successfully!
        </p>
    <?php endif; ?>

    <?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1'): ?>
        <p style="background:#e4f9ec;color:#187442;padding:12px;border-radius:6px;">
            Doctor deleted successfully!
        </p>
    <?php endif; ?>

    <?php if (isset($_GET['deleted']) && $_GET['deleted'] === '0'): ?>
        <p style="background:#fff3cd;color:#856404;padding:12px;border-radius:6px;">
            Doctor was not found.
        </p>
    <?php endif; ?>

    <?php if (isset($_GET['added']) && $_GET['added'] === '1'): ?>
        <div style="background:#e4f9ec;color:#187442;
                    padding:14px;border-radius:6px;margin-bottom:20px;">
            Doctor added successfully!
        </div>
    <?php endif; ?>
    <h1>Our Doctors</h1>

    <form class="search-form" method="GET" action="doctors.php">
        <input
            type="text"
            name="search"
            placeholder="Search doctor or specialization"
            value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
        >
        <button type="submit">Search</button>
    </form>

    <div style="margin-bottom: 18px;">
        <a href="add-doctor.php" class="add-button">+ Add Doctor</a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Doctor</th>
                    <th>Specialization</th>
                    <th>Experience</th>
                    <th>Fee</th>
                    <th>Available</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (count($doctors) > 0): ?>
                <?php foreach ($doctors as $doctor): ?>
                <tr>
                    <td><?= htmlspecialchars($doctor['name']) ?></td>
                    <td>
                        <span class="specialty">
                            <?= htmlspecialchars($doctor['specialization']) ?>
                        </span>
                    </td>
                    <td><?= (int)$doctor['experience'] ?> yrs</td>
                    <td>₹<?= number_format((float)$doctor['fee'], 0) ?></td>
                    <td><?= htmlspecialchars($doctor['available_days']) ?></td>
                    
                    <td>
                        
                        <a href="book-appointment.php?doctor_id=<?= (int)$doctor['id'] ?>"
                           style="display:inline-block;background:#10a8e4;color:white;
                                  padding:7px 10px;border-radius:5px;text-decoration:none;
                                  margin-right:4px;font-size:12px;">
                            Book
                        </a>

                        <a
                            href="edit-doctor.php?id=<?= (int)$doctor['id'] ?>"
                            style="background:#ffb52e;color:#fff;padding:7px 12px;
                                   border-radius:5px;text-decoration:none;"
                        >Edit</a>

                        <form
                            method="POST"
                            action="delete-doctor.php"
                            style="display:inline;"
                            onsubmit="return confirm('Are you sure you want to delete this doctor?');"
                        >
                            <input type="hidden" name="id"
                                   value="<?= (int)$doctor['id'] ?>">

                            <button
                                type="submit"
                                style="background:#e74c3c;color:white;border:0;
                                       padding:7px 12px;border-radius:5px;cursor:pointer;"
                            >Delete</button>
                        </form>
                    </td>

                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="empty">
                        No doctors found. Try another search.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
