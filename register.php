<?php
include 'config.php';

if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $password = $_POST['password']; // Plain text
    $kelas    = $_POST['kelas'];
    $jurusan  = $_POST['jurusan'];
    $gender   = $_POST['gender'];
    $no_telp  = $_POST['no_telp'];

    $query = "INSERT INTO users (fullname, username, password, role, kelas, jurusan, gender, no_telp) 
              VALUES ('$fullname', '$username', '$password', 'user', '$kelas', '$jurusan', '$gender', '$no_telp')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Berhasil Daftar! Silahkan Login'); window.location='login.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Zero Bullying</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light p-5">
    <div class="container bg-white p-4 shadow-sm mx-auto" style="max-width: 500px; border-radius: 10px;">
        <h3 class="text-center">Daftar Akun Baru</h3>
        <form method="POST">
            <input type="text" name="fullname" class="form-control mb-2" placeholder="Nama Lengkap" required>
            <input type="text" name="username" class="form-control mb-2" placeholder="Username" required>
            <input type="password" name="password" class="form-control mb-2" placeholder="Password" required>
            <div class="row">
                <div class="col"><input type="text" name="kelas" class="form-control mb-2" placeholder="Kelas" required></div>
                <div class="col"><input type="text" name="jurusan" class="form-control mb-2" placeholder="Jurusan" required></div>
            </div>
            <select name="gender" class="form-control mb-2">
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
            </select>
            <input type="text" name="no_telp" class="form-control mb-2" placeholder="No Telepon (Aktif)" required>
            <button type="submit" name="register" class="btn btn-primary w-100 mt-2">Daftar Sekarang</button>
            <p class="text-center mt-3">Sudah punya akun? <a href="login.php">Login</a></p>
        </form>
    </div>
</body>
</html>