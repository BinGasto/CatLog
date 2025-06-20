<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crea un nuovo post - CatLog</title>
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
            <h1>Crea un nuovo post</h1>
            <form action="save_post.php" method="post" enctype="multipart/form-data">
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
                <button type="submit" class="submit-button">Pubblica</button>
            </form>
        </div>
    </main>
</body>
</html>