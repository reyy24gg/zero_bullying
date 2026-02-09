<?php
include 'config.php';

// Proteksi Halaman: Jika belum login, lempar ke login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Pastikan hanya role 'user' (siswa) yang bisa akses feed utama ini
// Jika admin atau guru nyasar ke sini, mereka tetap bisa melihat tapi tidak bisa posting
$uid = $_SESSION['user_id'];

// Logika Posting Cerita
if (isset($_POST['send_post'])) {
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    
    if (!empty($content)) {
        $query_post = "INSERT INTO posts (user_id, content) VALUES ('$uid', '$content')";
        mysqli_query($conn, $query_post);
        // Refresh halaman agar postingan muncul dan mencegah double post saat refresh browser
        header("Location: index.php");
        exit();
    }
}

// Ambil data postingan beserta jumlah reaksi Agree & Disagree
$query_get_posts = "SELECT p.*, 
    (SELECT COUNT(*) FROM interactions WHERE post_id = p.id AND type = 'agree') as agree_count,
    (SELECT COUNT(*) FROM interactions WHERE post_id = p.id AND type = 'disagree') as disagree_count
    FROM posts p 
    ORDER BY p.created_at DESC";

$result_posts = mysqli_query($conn, $query_get_posts);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zero Bullying - Timeline</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .post-card { border-radius: 15px; border: none; }
        .anonymous-name { font-weight: bold; color: #6c757d; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">Zero Bullying</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3">Halo, <?= htmlspecialchars($_SESSION['fullname']); ?></span>
            <a href="logout.php" class="btn btn-light btn-sm text-primary fw-bold">Logout</a>
        </div>
    </div>
</nav>

<div class="container" style="max-width: 700px;">
    <div class="card post-card shadow-sm p-4 mb-4">
        <h5>Bagikan Ceritamu secara Anonim</h5>
        <form method="POST">
            <textarea name="content" class="form-control mb-3" rows="3" placeholder="Apa yang kamu alami hari ini? Identitasmu akan tetap rahasia..." required></textarea>
            <button type="submit" name="send_post" class="btn btn-primary px-4">Kirim Cerita</button>
        </form>
    </div>

    <hr class="my-4">

    <?php if (mysqli_num_rows($result_posts) > 0): ?>
        <?php while($row = mysqli_fetch_assoc($result_posts)): ?>
            <div class="card post-card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="anonymous-name">Anonymous User</span>
                        <small class="text-muted"><?= date('d M Y, H:i', strtotime($row['created_at'])); ?></small>
                    </div>
                    <p class="card-text text-dark"><?= nl2br(htmlspecialchars($row['content'])); ?></p>
                    
                    <div class="d-flex gap-2">
                        <a href="interact.php?id=<?= $row['id']; ?>&type=agree" class="btn btn-sm btn-outline-success">
                            Setuju (<?= $row['agree_count']; ?>)
                        </a>
                        <a href="interact.php?id=<?= $row['id']; ?>&type=disagree" class="btn btn-sm btn-outline-danger">
                            Tidak Setuju (<?= $row['disagree_count']; ?>)
                        </a>
                        
                        <?php if($row['user_id'] == $uid): ?>
                            <div class="ms-auto">
                                <a href="edit_post.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete_post.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Hapus postingan ini?')">Hapus</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-3 border-top pt-2">
                        <form action="post_comment.php" method="POST" class="d-flex gap-2">
                            <input type="hidden" name="post_id" value="<?= $row['id']; ?>">
                            <input type="text" name="comment" class="form-control form-control-sm" placeholder="Tulis komentar..." required>
                            <button type="submit" class="btn btn-sm btn-primary">Kirim</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-center text-muted mt-5">Belum ada cerita yang dibagikan.</p>
    <?php endif; ?>
</div>

</body>
</html>