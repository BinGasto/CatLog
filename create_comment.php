<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$id_post_padre = isset($_GET['id_post_padre']) ? intval($_GET['id_post_padre']) : 0;
$id_commento_padre = isset($_GET['id_commento_padre']) ? intval($_GET['id_commento_padre']) : null;
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scrivi un commento - CatLog</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="form.css">
    <script>
        function updateFileName() {
            const fileInput = document.getElementById('contenuto_multimediale');
            const fileNameSpan = document.getElementById('file-name');
            if (fileInput.files.length > 0) {
                fileNameSpan.textContent = fileInput.files[0].name;
            } else {
                fileNameSpan.textContent = 'Nessun file selezionato';
            }
        }
    </script>
</head>
<body>
    <main>
        <div class="form-container">
            <h1>Scrivi un commento</h1>
            <form action="save_post.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id_post_padre" value="<?php echo $id_post_padre; ?>">
                <?php if ($id_commento_padre): ?>
                    <input type="hidden" name="id_commento_padre" value="<?php echo $id_commento_padre; ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="titolo">Titolo</label>
                    <input type="text" id="titolo" name="titolo" required>
                </div>
                <div class="form-group">
                    <label for="contenuto">Contenuto</label>
                    <textarea id="contenuto" name="contenuto" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label for="contenuto_multimediale">Carica un file</label>
                    <label for="contenuto_multimediale" class="custom-file-upload">Scegli file</label>
                    <input type="file" id="contenuto_multimediale" name="contenuto_multimediale" onchange="updateFileName()">
                    <span id="file-name" class="file-name">Nessun file selezionato</span>
                </div>
                <button type="submit" class="submit-button">Pubblica commento</button>
            </form>
            <a href="post_details.php?id_post=<?php echo $id_post_padre; ?>" class="register-button">Annulla</a>
        </div>
    </main>
</body>
</html>