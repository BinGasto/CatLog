<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db.php';

$sql = "SELECT post.id_post, post.titolo, post.contenuto, post.contenuto_multimediale, post.data_ora, 
               utenti.nome_utente, utenti.cognome_utente, utenti.foto_profilo, post.mipiace, post.id_utente
        FROM post
        JOIN utenti ON post.id_utente = utenti.id_utente
        WHERE post.id_post_padre IS NULL
        ORDER BY post.data_ora DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id_post = $row['id_post'];
        $titolo = htmlspecialchars($row['titolo']);
        $contenuto = htmlspecialchars($row['contenuto']);
        $data_ora = $row['data_ora'];
        $nome_utente = htmlspecialchars($row['nome_utente']);
        $cognome_utente = htmlspecialchars($row['cognome_utente']);
        $foto_profilo = htmlspecialchars($row['foto_profilo']);
        $mipiace = $row['mipiace'];
        $id_utente_post = $row['id_utente'];

        echo "<div class='post'>";
        echo "<div class='post-header'>";
        echo "<img src='$foto_profilo' alt='Foto profilo' class='post-author-photo'>";
        echo "<p class='post-author'><strong>$nome_utente $cognome_utente</strong></p>";
        echo "</div>";
        echo "<h3>$titolo</h3>";
        echo "<p>$contenuto</p>";
        
        if (!empty($row['contenuto_multimediale'])) {
            $ext = strtolower(pathinfo($row['contenuto_multimediale'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                echo "<img src='" . htmlspecialchars($row['contenuto_multimediale']) . "' alt='Allegato' style='max-width:300px; display:block; margin-top:0.5rem; border-radius:8px;'>";
            } elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                echo "<video controls style='max-width:300px; display:block; margin-top:0.5rem; border-radius:8px;'><source src='" . htmlspecialchars($row['contenuto_multimediale']) . "' type='video/$ext'>Il tuo browser non supporta il video.</video>";
            } else {
                echo "<a href='" . htmlspecialchars($row['contenuto_multimediale']) . "' target='_blank'>Visualizza allegato</a>";
            }
        }

        echo "<p class='post-date'><small>Pubblicato il $data_ora</small></p>";

        echo "<div class='post-actions'>";
        
        echo "<button class='like-button' onclick='likePost($id_post, this)'>";
        echo "Mi piace (<span class='like-count'>$mipiace</span>)";
        echo "</button>";

        echo "<a href='post_details.php?id_post=$id_post' class='like-button'>Commenti</a>";

        if (isset($_SESSION['username'])) {
            $username = $_SESSION['username'];
            $sql_user = "SELECT id_utente FROM utenti WHERE nickname_utente = '$username'";
            $result_user = $conn->query($sql_user);
            if ($result_user && $result_user->num_rows > 0) {
                $user_row = $result_user->fetch_assoc();
                $id_utente_session = $user_row['id_utente'];

                if ($id_utente_session == $id_utente_post) {
                    echo "<form action='delete_post.php' method='post' class='delete-post-form' onsubmit='showConfirmPopup(this); return false;'>";
                    echo "<input type='hidden' name='id_post' value='$id_post'>";
                    echo "<button type='submit' class='delete-post-button'>Elimina</button>";
                    echo "</form>";
                }
            }
        }
        
        echo "</div>";
        echo "</div>";
    }
} else {
    echo "<p>Nessun post disponibile.</p>";
}

$conn->close();
?>