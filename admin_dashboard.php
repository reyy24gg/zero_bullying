<?php
include 'config.php';
checkLogin('admin');

$success_msg = "";
$error_msg = "";

// 1. Logika Tambah Guru BK
if (isset($_POST['add_guru'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $no_telp  = mysqli_real_escape_string($conn, $_POST['no_telp']);

    $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $error_msg = "Username sudah digunakan!";
    } else {
        mysqli_query($conn, "INSERT INTO users (fullname, username, password, role, no_telp) VALUES ('$fullname', '$username', '$password', 'guru', '$no_telp')");
        $success_msg = "Akun Guru berhasil dibuat!";
    }
}

// 2. Logika Approve/Reject Request
if (isset($_GET['action']) && isset($_GET['req_id'])) {
    $status = ($_GET['action'] == 'approve') ? 'approved' : 'rejected';
    $rid = $_GET['req_id'];
    mysqli_query($conn, "UPDATE access_requests SET status='$status' WHERE id='$rid'");
    header("Location: admin_dashboard.php");
    exit();
}

// 3. Ambil Postingan & Interaksi
$query_posts = "SELECT p.*, u.fullname, u.no_telp,
                (SELECT COUNT(*) FROM interactions WHERE post_id = p.id AND type = 'agree') as agree_count,
                (SELECT COUNT(*) FROM interactions WHERE post_id = p.id AND type = 'disagree') as disagree_count
                FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC";
$result_posts = mysqli_query($conn, $query_posts);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f4f7f6; }
        .card { border-radius: 12px; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .comment-box { background: #f8f9fa; padding: 10px; border-radius: 8px; font-size: 0.85rem; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark px-4 mb-4">
    <span class="navbar-brand fw-bold">Zero Bullying Admin</span>
    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
</nav>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card p-3 mb-4">
                <h6 class="fw-bold">TAMBAH GURU BK</h6>
                <?php if($success_msg) echo "<small class='text-success'>$success_msg</small>"; ?>
                <form method="POST">
                    <input type="text" name="fullname" class="form-control form-control-sm mb-2" placeholder="Nama Lengkap" required>
                    <input type="text" name="username" class="form-control form-control-sm mb-2" placeholder="Username" required>
                    <input type="password" name="password" class="form-control form-control-sm mb-2" placeholder="Password" required>
                    <input type="text" name="no_telp" class="form-control form-control-sm mb-3" placeholder="No Telp" required>
                    <button name="add_guru" class="btn btn-success btn-sm w-100">Simpan</button>
                </form>
            </div>

            <div class="card p-3">
                <h6 class="fw-bold text-primary">PERMINTAAN AKSES</h6>
                <table class="table table-sm small">
                    <?php 
                    $q_req = mysqli_query($conn, "SELECT ar.*, u.fullname FROM access_requests ar JOIN users u ON ar.guru_id = u.id WHERE ar.status='pending'");
                    while($r = mysqli_fetch_assoc($q_req)): ?>
                    <tr>
                        <td><?= $r['fullname'] ?> <br><small class="text-muted">Post #<?= $r['post_id'] ?></small></td>
                        <td class="text-end">
                            <a href="?action=approve&req_id=<?= $r['id'] ?>" class="btn btn-success btn-xs py-0 px-1">✔</a>
                            <a href="?action=reject&req_id=<?= $r['id'] ?>" class="btn btn-danger btn-xs py-0 px-1">✖</a>
                        </td>
                    </tr>
                    <?php endwhile; if(mysqli_num_rows($q_req)==0) echo "<tr><td class='text-muted'>Tidak ada permintaan.</td></tr>"; ?>
                </table>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card p-4">
                <h5 class="fw-bold mb-4">Monitoring Postingan & Interaksi</h5>
                <?php while($post = mysqli_fetch_assoc($result_posts)): ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-primary"><?= $post['fullname'] ?> <small class="text-muted">(<?= $post['no_telp'] ?>)</small></span>
                            <small class="text-muted"><?= $post['created_at'] ?></small>
                        </div>
                        <p class="my-2">"<?= $post['content'] ?>"</p>
                        <div class="mb-2">
                            <span class="badge bg-success">Setuju: <?= $post['agree_count'] ?></span>
                            <span class="badge bg-danger">Tidak Setuju: <?= $post['disagree_count'] ?></span>
                        </div>
                        <div class="comment-box">
                            <strong>Komentar:</strong><br>
                            <?php 
                            $pid = $post['id'];
                            $q_c = mysqli_query($conn, "SELECT c.*, u.fullname FROM comments c JOIN users u ON c.user_id=u.id WHERE post_id='$pid'");
                            while($c = mysqli_fetch_assoc($q_c)) echo "<b>".$c['fullname'].":</b> ".$c['comment']."<br>";
                            ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>