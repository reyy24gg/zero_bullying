<?php
include 'config.php';

// Proteksi: Jika sudah login, dilarang akses halaman login lagi
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } elseif ($_SESSION['role'] == 'guru') {
        header("Location: guru_dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password']; // Plain text 12345 dsb.

    // Query mencari user berdasarkan username dan password
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Set Session Data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];

        // Redirect Otomatis Berdasarkan Role
        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($user['role'] == 'guru') {
            header("Location: guru_dashboard.php");
        } else {
            header("Location: index.php"); // Untuk Role User (Siswa)
        }
        exit();
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Zero Bullying</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f0f2f5;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .brand-title {
            color: #0d6efd;
            font-weight: 800;
            text-align: center;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2 class="brand-title">ZERO BULLYING</h2>
    <p class="text-center text-muted small mb-4">Akses Panel Pengguna, Guru, atau Admin</p>

    <?php if ($error != ""): ?>
        <div class="alert alert-danger py-2 small text-center"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label small fw-bold">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Ketik username..." required>
        </div>
        
        <div class="mb-4">
            <label class="form-label small fw-bold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Ketik password..." required>
        </div>
        
        <button type="submit" name="login" class="btn btn-primary w-100 fw-bold shadow-sm py-2">MASUK</button>
    </form>
    
    <div class="text-center mt-4 pt-3 border-top">
        <span class="small text-muted">Belum punya akun siswa?</span><br>
        <a href="register.php" class="small fw-bold text-decoration-none">Daftar Akun Baru</a>
    </div>
</div>

</body>
</html>