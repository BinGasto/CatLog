<?php
session_start();
include 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = isset($_POST['titolo']) ? $conn->real_escape_string($_POST['titolo']) : null;
    $contenuto = $conn->real_escape_string($_POST['contenuto']);
    $id_post_padre = isset($_POST['id_post_padre']) ? intval($_POST['id_post_padre']) : null;
    $id_commento_padre = isset($_POST['id_commento_padre']) ? intval($_POST['id_commento_padre']) : null;
    $nickname = $_SESSION['username'];

    $sql_user = "SELECT id_utente FROM utenti WHERE nickname_utente = '$nickname'";
    $result_user = $conn->query($sql_user);

    if ($result_user && $result_user->num_rows > 0) {
        $row_user = $result_user->fetch_assoc();
        $id_utente = $row_user['id_utente'];

        $sql = "INSERT INTO post (titolo, contenuto, contenuto_multimediale, data_ora, mipiace, repost, id_utente, id_post_padre, id_commento_padre)
                VALUES (?, ?, NULL, NOW(), 0, 0, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiii", $titolo, $contenuto, $id_utente, $id_post_padre, $id_commento_padre);

        if ($stmt->execute()) {
            $id_post = $conn->insert_id;

            if (isset($_FILES['contenuto_multimediale']) && $_FILES['contenuto_multimediale']['error'] === UPLOAD_ERR_OK) {
                $file_extension = pathinfo($_FILES['contenuto_multimediale']['name'], PATHINFO_EXTENSION);
                $file_path = "post_photos/{$id_utente}-{$id_post}.{$file_extension}";

                if (move_uploaded_file($_FILES['contenuto_multimediale']['tmp_name'], $file_path)) {
                    $sql_update = "UPDATE post SET contenuto_multimediale = ? WHERE id_post = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("si", $file_path, $id_post);
                    $stmt_update->execute();
                }
            }

            if ($id_post_padre) {
                header("Location: post_details.php?id_post=$id_post_padre");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            echo "Errore durante la creazione del post: " . $stmt->error;
        }
    } else {
        echo "Errore: utente non trovato.";
    }
}

$conn->close();
?>