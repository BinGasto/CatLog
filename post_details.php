<?php
session_start();
include 'db.php';

$nome_utente = '';
$cognome_utente = '';
$foto_profilo = 'user_photos/LoginRegister.png';
$id_utente_session = null;

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $sql_user = "SELECT id_utente, nome_utente, cognome_utente, foto_profilo FROM utenti WHERE nickname_utente = '$username'";
    $result_user = $conn->query($sql_user);

    if ($result_user && $result_user->num_rows > 0) {
        $row_user = $result_user->fetch_assoc();
        $id_utente_session = $row_user['id_utente'];
        $nome_utente = $row_user['nome_utente'];
        $cognome_utente = $row_user['cognome_utente'];
        $foto_profilo = $row_user['foto_profilo'];
    } else {
        $id_utente_session = null;
        $nome_utente = '';
        $cognome_utente = '';
        $foto_profilo = 'user_photos/LoginRegister.png';
    }
}

$id_post = isset($_GET['id_post']) ? intval($_GET['id_post']) : 0;

$sql_post = "SELECT post.titolo, post.contenuto, post.contenuto_multimediale, post.data_ora, utenti.nome_utente, utenti.cognome_utente, utenti.foto_profilo
             FROM post
             JOIN utenti ON post.id_utente = utenti.id_utente
             WHERE post.id_post = ?";
$stmt_post = $conn->prepare($sql_post);
$stmt_post->bind_param("i", $id_post);
$stmt_post->execute();
$result_post = $stmt_post->get_result();

$post = null;
if ($result_post && $result_post->num_rows > 0) {
    $post = $result_post->fetch_assoc();
}

function mostraCommenti($id_post_padre, $id_commento_padre = null, $conn, $livello = 0, $id_utente_session = null) {
    $sql_comments = "SELECT post.id_post, post.titolo, post.contenuto, post.contenuto_multimediale, post.data_ora, utenti.nome_utente, utenti.cognome_utente, utenti.foto_profilo, post.mipiace, post.id_utente
                     FROM post
                     JOIN utenti ON post.id_utente = utenti.id_utente
                     WHERE post.id_post_padre = ? AND " . ($id_commento_padre ? "post.id_commento_padre = ?" : "post.id_commento_padre IS NULL") . "
                     ORDER BY post.data_ora ASC";
    $stmt_comments = $conn->prepare($sql_comments);
    if ($id_commento_padre) {
        $stmt_comments->bind_param("ii", $id_post_padre, $id_commento_padre);
    } else {
        $stmt_comments->bind_param("i", $id_post_padre);
    }
    $stmt_comments->execute();
    $result_comments = $stmt_comments->get_result();

    while ($comment = $result_comments->fetch_assoc()) {
        echo "<div class='post comment' style='margin-left: " . ($livello * 20) . "px;'>";
        echo "<div class='post-header'>";
        echo "<img src='" . htmlspecialchars($comment['foto_profilo']) . "' alt='Foto profilo' class='post-author-photo'>";
        echo "<p class='post-author'><strong>" . htmlspecialchars($comment['nome_utente'] . ' ' . $comment['cognome_utente']) . "</strong></p>";
        echo "</div>";
        
        if (!empty($comment['titolo'])) {
            echo "<h4 style='color:#ffa500; margin-bottom:0.5rem;'>" . htmlspecialchars($comment['titolo']) . "</h4>";
        }
        echo "<p>" . htmlspecialchars($comment['contenuto']) . "</p>";
        
        if (!empty($comment['contenuto_multimediale'])) {
            $ext = strtolower(pathinfo($comment['contenuto_multimediale'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                echo "<img src='" . htmlspecialchars($comment['contenuto_multimediale']) . "' alt='Allegato' style='max-width:200px; display:block; margin-top:0.5rem; border-radius:8px;'>";
            } elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                echo "<video controls style='max-width:200px; display:block; margin-top:0.5rem; border-radius:8px;'><source src='" . htmlspecialchars($comment['contenuto_multimediale']) . "' type='video/$ext'>Il tuo browser non supporta il video.</video>";
            } else {
                echo "<a href='" . htmlspecialchars($comment['contenuto_multimediale']) . "' target='_blank'>Visualizza allegato</a>";
            }
        }
        
        echo "<p class='post-date'><small>Pubblicato il " . $comment['data_ora'] . "</small></p>";
        echo "<div class='post-actions'>";
        
        echo "<button class='like-button' onclick='likePost(" . $comment['id_post'] . ", this)'>";
        echo "Mi piace (<span class='like-count'>" . $comment['mipiace'] . "</span>)";
        echo "</button>";

        echo "<a href='create_comment.php?id_post_padre=$id_post_padre&id_commento_padre=" . $comment['id_post'] . "' class='like-button'>Rispondi</a>";

        if (isset($_SESSION['username']) && $comment['id_utente'] == $id_utente_session) {
            echo "<form action='delete_post.php' method='post' class='delete-post-form' onsubmit='showConfirmPopup(this); return false;'>";
            echo "<input type='hidden' name='id_post' value='" . $comment['id_post'] . "'>";
            echo "<button type='submit' class='delete-post-button'>Elimina</button>";
            echo "</form>";
        }
        echo "</div>";

        mostraCommenti($id_post_padre, $comment['id_post'], $conn, $livello + 1, $id_utente_session);
        echo "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettagli del Post - CatLog</title>
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
                   <?php echo isset($_SESSION['username']) ? 'style="pointer-events: none;"' : ''; ?>>
                    <img src="<?php echo htmlspecialchars($foto_profilo); ?>" alt="Foto profilo" class="profile-photo">
                </a>
            </div>
        </div>
    </header>
    <main>
        <section class="feed">
            <?php if ($post): ?>
                <div class="post">
                    <div class="post-header">
                        <img src="<?php echo htmlspecialchars($post['foto_profilo']); ?>" alt="Foto profilo" class="post-author-photo">
                        <p class="post-author"><strong><?php echo htmlspecialchars($post['nome_utente'] . ' ' . $post['cognome_utente']); ?></strong></p>
                    </div>
                    <h3><?php echo htmlspecialchars($post['titolo']); ?></h3>
                    <p><?php echo htmlspecialchars($post['contenuto']); ?></p>
                    <?php
                    if (!empty($post['contenuto_multimediale'])) {
                        $ext = strtolower(pathinfo($post['contenuto_multimediale'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            echo "<img src='" . htmlspecialchars($post['contenuto_multimediale']) . "' alt='Allegato' style='max-width:300px; display:block; margin-top:0.5rem; border-radius:8px;'>";
                        } elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                            echo "<video controls style='max-width:300px; display:block; margin-top:0.5rem; border-radius:8px;'><source src='" . htmlspecialchars($post['contenuto_multimediale']) . "' type='video/$ext'>Il tuo browser non supporta il video.</video>";
                        } else {
                            echo "<a href='" . htmlspecialchars($post['contenuto_multimediale']) . "' target='_blank'>Visualizza allegato</a>";
                        }
                    }
                    ?>
                    <p class="post-date"><small>Pubblicato il <?php echo $post['data_ora']; ?></small></p>
                </div>
                <h2>Commenti</h2>
                <?php mostraCommenti($id_post, null, $conn, 0, $id_utente_session); ?>
                <div class="action-box">
                    <a href="create_comment.php?id_post_padre=<?php echo $id_post; ?>" class="like-button">Commenta</a>
                </div>
            <?php else: ?>
                <p>Post non trovato.</p>
            <?php endif; ?>
        </section>
    </main>

    <!-- Pop-up di conferma -->
    <div id="confirm-popup" class="popup hidden">
        <div class="popup-content">
            <p>Sei sicuro di voler eliminare il commento?</p>
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
                }
            })
            .catch(error => {
                console.error('Errore:', error);
            });
        }
    </script>
</body>
</html>