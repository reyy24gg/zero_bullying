<?php
include 'config.php';
checkLogin('guru');

// Logika Kirim Request Akses ke Admin
if (isset($_GET['request_id'])) {
    $pid = $_GET['request_id'];
    $gid = $_SESSION['user_id'];
    
    // Cek apakah sudah pernah meminta akses untuk postingan ini
    $cek = mysqli_query($conn, "SELECT id FROM access_requests WHERE post_id='$pid' AND guru_id='$gid'");
    if (mysqli_num_rows($cek) == 0) {
        mysqli_query($conn, "INSERT INTO access_requests (post_id, guru_id) VALUES ('$pid', '$gid')");
        header("Location: guru_dashboard.php");
        exit();
    }
}

// Ambil data postingan dan jumlah interaksi
$query_posts = "SELECT p.*, 
                (SELECT COUNT(*) FROM interactions WHERE post_id = p.id AND type = 'agree') as agree_count,
                (SELECT COUNT(*) FROM interactions WHERE post_id = p.id AND type = 'disagree') as disagree_count
                FROM posts p ORDER BY p.created_at DESC";
$result_posts = mysqli_query($conn, $query_posts);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guru Dashboard - Zero Bullying</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background: #f0f4f8; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .post-card { border: none; border-left: 5px solid #17a2b8; border-radius: 10px; transition: transform 0.2s; }
        .post-card:hover { transform: translateY(-2px); }
        .comment-area { background: #f8f9fa; border-radius: 8px; padding: 15px; margin-top: 15px; font-size: 0.9rem; }
        .comment-item { border-bottom: 1px solid #eee; padding: 8px 0; }
        .comment-item:last-child { border-bottom: none; }
        .role-badge { font-size: 0.7rem; padding: 2px 6px; border-radius: 10px; margin-left: 5px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary px-4 mb-4">
    <div class="container d-flex justify-content-between">
        <span class="navbar-brand fw-bold">ZERO BULLYING <span class="badge bg-info">GURU BK</span></span>
        <div class="text-white">
            <small class="me-3">Halo, <?= htmlspecialchars($_SESSION['fullname']) ?></small>
            <a href="logout.php" class="btn btn-light btn-sm fw-bold text-primary">Logout</a>
        </div>
    </div>
</nav>

<div class="container" style="max-width: 800px;">
    <h4 class="mb-4 text-secondary fw-bold">Daftar Laporan Masuk</h4>

    <?php while($row = mysqli_fetch_assoc($result_posts)): ?>
        <div class="card post-card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <span class="text-dark fw-bold">Anonymous User</span>
                        <br><small class="text-muted">ID Post: #<?= $row['id'] ?></small>
                    </div>
                    <small class="text-muted"><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></small>
                </div>

                <p class="fs-5 text-dark" style="line-height: 1.6;">"<?= nl2br(htmlspecialchars($row['content'])) ?>"</p>
                
                <div class="mb-3">
                    <span class="badge bg-success rounded-pill me-2">👍 Setuju: <?= $row['agree_count'] ?></span>
                    <span class="badge bg-danger rounded-pill">👎 Tidak Setuju: <?= $row['disagree_count'] ?></span>
                </div>

                <div class="p-3 bg-light rounded border mb-3">
                    <?php 
                    $gid = $_SESSION['user_id']; 
                    $pid = $row['id'];
                    $req_q = mysqli_query($conn, "SELECT status FROM access_requests WHERE post_id='$pid' AND guru_id='$gid'");
                    $req = mysqli_fetch_assoc($req_q);

                    if (!$req): ?>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="small text-muted italic">Identitas siswa tersembunyi.</span>
                            <a href="?request_id=<?= $pid ?>" class="btn btn-warning btn-sm fw-bold">Minta Akses ke Admin</a>
                        </div>
                    <?php elseif ($req['status'] == 'pending'): ?>
                        <span class="badge bg-secondary w-100 p-2">Menunggu Persetujuan Admin...</span>
                    <?php elseif ($req['status'] == 'approved'): 
                        $owner = mysqli_query($conn, "SELECT u.no_telp, u.fullname FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id='$pid'");
                        $data_siswa = mysqli_fetch_assoc($owner); ?>
                        <div class="alert alert-info py-2 small mb-0">
                            <strong>Akses Diberikan:</strong><br>
                            Nama Siswa: <?= $data_siswa['fullname'] ?><br>
                            No. Telepon: <a href="https://wa.me/<?= $data_siswa['no_telp'] ?>" target="_blank"><?= $data_siswa['no_telp'] ?></a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger py-2 small mb-0 text-center fw-bold">Akses Ditolak Admin</div>
                    <?php endif; ?>
                </div>

                <div class="comment-area border-top">
                    <h6 class="fw-bold mb-3 text-secondary" style="font-size: 0.8rem;">DISKUSI & TANGGAPAN</h6>
                    <?php 
                    $q_c = mysqli_query($conn, "SELECT c.*, u.fullname, u.role FROM comments c JOIN users u ON c.user_id=u.id WHERE post_id='$pid' ORDER BY c.created_at ASC");
                    
                    if(mysqli_num_rows($q_c) > 0):
                        while($c = mysqli_fetch_assoc($q_c)): 
                            // Logika: Jika role user, maka nama disembunyikan
                            $isGuru = ($c['role'] == 'guru');
                            $name = $isGuru ? htmlspecialchars($c['fullname']) : "Anonymous";
                            $color = $isGuru ? "text-primary" : "text-muted";
                        ?>
                        <div class="comment-item">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold <?= $color ?>">
                                    <?= $name ?>
                                    <?php if($isGuru) echo '<span class="badge bg-primary role-badge">GURU BK</span>'; ?>
                                </span>
                                <small class="text-muted" style="font-size: 0.7rem;"><?= date('H:i', strtotime($c['created_at'])) ?></small>
                            </div>
                            <div class="text-dark mt-1"><?= htmlspecialchars($c['comment']) ?></div>
                        </div>
                        <?php endwhile; 
                    else: ?>
                        <p class="text-muted fst-italic small">Belum ada tanggapan.</p>
                    <?php endif; ?>

                    <form action="post_comment.php" method="POST" class="d-flex gap-2 mt-3">
                        <input type="hidden" name="post_id" value="<?= $pid ?>">
                        <input type="text" name="comment" class="form-control form-control-sm border-primary" placeholder="Tulis bimbingan atau solusi..." required>
                        <button class="btn btn-primary btn-sm px-4 fw-bold">Kirim</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<footer class="text-center py-4 text-muted small">
    &copy; 2026 Zero Bullying System - Menjaga Privasi & Keamanan Siswa
</footer>

</body>
</html>