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
            $row_user = $result_user->fetch_assoc();
            $id_utente = $row_user['id_utente'];

            $sql_check = "SELECT * FROM mipiace WHERE id_utente = ? AND id_post = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("ii", $id_utente, $id_post);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                $sql_delete = "DELETE FROM mipiace WHERE id_utente = ? AND id_post = ?";
                $stmt_delete = $conn->prepare($sql_delete);
                $stmt_delete->bind_param("ii", $id_utente, $id_post);
                if ($stmt_delete->execute()) {
                    $sql_decrement = "UPDATE post SET mipiace = mipiace - 1 WHERE id_post = ?";
                    $stmt_decrement = $conn->prepare($sql_decrement);
                    $stmt_decrement->bind_param("i", $id_post);
                    $stmt_decrement->execute();
                    echo "removed";
                }
            } else {
                $sql_insert = "INSERT INTO mipiace (id_utente, id_post) VALUES (?, ?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("ii", $id_utente, $id_post);
                if ($stmt_insert->execute()) {
                    $sql_increment = "UPDATE post SET mipiace = mipiace + 1 WHERE id_post = ?";
                    $stmt_increment = $conn->prepare($sql_increment);
                    $stmt_increment->bind_param("i", $id_post);
                    $stmt_increment->execute();
                    echo "added";
                }
            }
        }
    } else {
        echo "not_logged_in";
    }
}

$conn->close();
?>