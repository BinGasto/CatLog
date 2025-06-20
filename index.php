<?php
session_start();
include 'db.php';

$nome_utente = '';
$cognome_utente = '';
$foto_profilo = 'user_photos/LoginRegister.png';

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $sql = "SELECT nome_utente, cognome_utente, foto_profilo FROM utenti WHERE nickname_utente = '$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $nome_utente = $row['nome_utente'];
        $cognome_utente = $row['cognome_utente'];
        $foto_profilo = $row['foto_profilo'];
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CatLog</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <header>
        <div class="header-container">
            <h1>CatLog</h1>
            <div class="user-info-container">
                <?php if (!empty($nome_utente) && !empty($cognome_utente)): ?>
                    <span class="user-name"><?php echo htmlspecialchars($nome_utente . ' ' . $cognome_utente); ?></span>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="logout.php" class="logout-button">Logout</a>
                <?php endif; ?>
                
                <a href="<?php echo isset($_SESSION['username']) ? '#' : 'login.php'; ?>" 
                   class="login-button" 
                   <?php echo isset($_SESSION['username']) ? 'style="pointer-events: none; "' : ''; ?>>
                    <img src="<?php echo htmlspecialchars($foto_profilo); ?>" alt="Foto profilo" class="profile-photo">
                </a>
            </div>
        </div>
    </header>
    <main>
        <section class="feed">
            <h2>Feed</h2>
            <?php include 'fetch_feed.php'; ?>
        </section>
    </main>

    <?php if (isset($_SESSION['username'])): ?>
        <a href="create_post.php" class="create-post-button">+</a>
    <?php endif; ?>

    <div id="confirm-popup" class="popup hidden">
        <div class="popup-content">
            <p>Sei sicuro di voler eliminare il post?</p>
            <div class="popup-buttons">
                <button id="confirm-yes" class="popup-button">Sì</button>
                <button id="confirm-no" class="popup-button">No</button>
            </div>
        </div>
    </div>

    <script>
        let formToSubmit = null;

        function showConfirmPopup(form) {
            formToSubmit = form;
            document.getElementById('confirm-popup').classList.remove('hidden');
        }

        document.getElementById('confirm-yes').addEventListener('click', function () {
            if (formToSubmit) {
                formToSubmit.submit();
            }
        });

        document.getElementById('confirm-no').addEventListener('click', function () {
            document.getElementById('confirm-popup').classList.add('hidden');
            formToSubmit = null;
        });
    </script>
    <script>
        function likePost(idPost, buttonElement) {
            fetch('like_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id_post=${idPost}`
            })
            .then(response => response.text())
            .then(data => {
                if (data === 'added') {
                    const likeCountSpan = buttonElement.querySelector('.like-count');
                    likeCountSpan.textContent = parseInt(likeCountSpan.textContent) + 1;
                } else if (data === 'removed') {
                    const likeCountSpan = buttonElement.querySelector('.like-count');
                    likeCountSpan.textContent = parseInt(likeCountSpan.textContent) - 1;
                } else if (data === 'not_logged_in') {
                    alert('Devi effettuare il login per mettere Mi piace.');
                }
            })
            .catch(error => {
                console.error('Errore:', error);
            });
        }
    </script>
</body>
</html>