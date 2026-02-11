<?php
require_once __DIR__ . '/../../backend/session.php';
require_once __DIR__ . '/../../backend/config/db.php';

Session::start();

if (!Session::isLoggedIn()) {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = Session::get('user_id');

// Fetch user data
$stmt = $pdo->prepare("SELECT name, email, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$photo = $user['profile_picture'] ? '../../' . $user['profile_picture'] : '../../assets/img/default.jpg';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - Rentease</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/css/profil.css">
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container" style="margin-top: 80px; max-width: 600px;">
        <div class="card">
            <h2 style="text-align: center; margin-bottom: 20px;">Edit Profil</h2>
            
            <form id="editProfileForm" enctype="multipart/form-data">
                <div style="text-align: center; margin-bottom: 20px;">
                    <img id="previewFoto" src="<?= htmlspecialchars($photo) ?>" alt="Foto Profil" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #4CAF50;">
                </div>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="form-control">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="form-control">
                </div>

                <div class="form-group">
                    <label>Ganti Foto Profil</label>
                    <input type="file" name="profile_picture" id="fotoInput" accept="image/*" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <?php include '../partials/footer.php'; ?>

    <script>
        // Preview image
        document.getElementById('fotoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    document.getElementById('previewFoto').src = evt.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        // Submit form
        document.getElementById('editProfileForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerText;
            
            btn.disabled = true;
            btn.innerText = 'Menyimpan...';

            try {
                const response = await fetch('../../backend/api/users.php?action=update_profile', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.status === 'success') {
                    alert('Profil berhasil diperbarui!');
                    location.reload();
                } else {
                    alert('Gagal: ' + result.message);
                }
            } catch (error) {
                console.error(error);
                alert('Terjadi kesalahan.');
            } finally {
                btn.disabled = false;
                btn.innerText = originalText;
            }
        });
    </script>
</body>
</html>
