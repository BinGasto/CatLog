<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_post'])) {
    $id_post = intval($_POST['id_post']);

    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $sql_user = "SELECT id_utente FROM utenti WHERE nickname_utente = '$username'";
        $result_user = $conn->query($sql_user);

        if ($result_user && $result_user->num_rows > 0) {
            $user_row = $result_user->fetch_assoc();
            $id_utente_session = $user_row['id_utente'];

            $sql_check = "SELECT id_utente, id_post_padre FROM post WHERE id_post = $id_post";
            $result_check = $conn->query($sql_check);

            if ($result_check && $result_check->num_rows > 0) {
                $post_row = $result_check->fetch_assoc();
                $id_post_padre = $post_row['id_post_padre'];

                if ($post_row['id_utente'] == $id_utente_session) {
                    $sql_delete_comments = "DELETE FROM post WHERE id_post_padre = $id_post";
                    $conn->query($sql_delete_comments);

                    $sql_delete = "DELETE FROM post WHERE id_post = $id_post";
                    if ($conn->query($sql_delete)) {
                        if ($id_post_padre) {
                            header("Location: post_details.php?id_post=$id_post_padre");
                        } else {
                            header("Location: index.php");
                        }
                        exit();
                    } else {
                        echo "Errore durante l'eliminazione del post: " . $conn->error;
                    }
                } else {
                    echo "Non sei autorizzato a eliminare questo post.";
                }
            } else {
                echo "Post non trovato.";
            }
        }
    }
}

$conn->close();
?>