<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['nickname']);
    $password = $conn->real_escape_string($_POST['password']);

    $sql = "SELECT * FROM utenti WHERE nickname_utente = '$username' AND password_utente = '$password'";
    $result = $conn->query($sql);

    if (!$result) {
        die("Errore nella query: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        session_start();
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit();
    } else {
        echo "Nickname o password errati.";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CatLog</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="form.css">
</head>
<body>
    <main>
        <div class="form-container">
            <h1>Login</h1>
            <form action="login.php" method="post">
                <label for="nickname">Nickname</label>
                <input type="text" id="nickname" name="nickname" required>
                <br>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <br>
                <button type="submit">Accedi</button>
            </form>
            <br>
            <a href="register.php" class="register-button">Registrati</a>
        </div>
    </main>
</body>
</html>