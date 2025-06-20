<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $conn->real_escape_string($_POST['nome']);
    $cognome = $conn->real_escape_string($_POST['cognome']);
    $nickname = $conn->real_escape_string($_POST['nickname']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $conn->real_escape_string($_POST['password']);
    $biografia = $conn->real_escape_string($_POST['biografia']);

    $sql = "INSERT INTO utenti (nome_utente, cognome_utente, nickname_utente, email_utente, password_utente, biografia_utente)
            VALUES ('$nome', '$cognome', '$nickname', '$email', '$password', '$biografia')";

    if ($conn->query($sql) === TRUE) {
        $id_utente = $conn->insert_id;

        if (isset($_FILES['foto_profilo']) && $_FILES['foto_profilo']['error'] === UPLOAD_ERR_OK) {
            $file_extension = pathinfo($_FILES['foto_profilo']['name'], PATHINFO_EXTENSION);
            $file_path = "user_photos/$id_utente.$file_extension";

            if (move_uploaded_file($_FILES['foto_profilo']['tmp_name'], $file_path)) {
                $sql_update = "UPDATE utenti SET foto_profilo = '$file_path' WHERE id_utente = $id_utente";
                $conn->query($sql_update);
            }
        }

        header("Location: login.php");
        exit();
    } else {
        echo "Errore durante la registrazione: " . $conn->error;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrati - CatLog</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="base.css">
    <link rel="stylesheet" href="form.css">
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.getElementById('toggle-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.textContent = 'Nascondi';
            } else {
                passwordInput.type = 'password';
                toggleButton.textContent = 'Mostra';
            }
        }

        function updateFileName() {
            const fileInput = document.getElementById('foto_profilo');
            const fileName = document.getElementById('file-name');
            fileName.textContent = fileInput.files.length > 0 ? fileInput.files[0].name : 'Nessun file selezionato';
        }
    </script>
</head>
<body>
    <main>
        <div class="form-container">
            <h1>Registrati</h1>
            <form action="register.php" method="post" enctype="multipart/form-data">
                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" required>
                
                <label for="cognome">Cognome</label>
                <input type="text" id="cognome" name="cognome" required>
                
                <label for="nickname">Nickname</label>
                <input type="text" id="nickname" name="nickname" required>
                
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                
                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required>
                    <button type="button" id="toggle-password" class="toggle-password-button" onclick="togglePasswordVisibility()">Mostra</button>
                </div>
                
                <label for="biografia">Biografia</label>
                <textarea id="biografia" name="biografia" rows="5" required></textarea>
                
                <div class="form-group">
                    <label for="foto_profilo">Carica una foto profilo</label>
                    <label for="foto_profilo" class="custom-file-upload">Scegli file</label>
                    <input type="file" id="foto_profilo" name="foto_profilo" onchange="updateFileName()">
                    <span id="file-name" class="file-name">Nessun file selezionato</span>
                </div>
                
                <button type="submit">Registrati</button>
            </form>
            <a href="login.php" class="register-button">Torna al login</a>
        </div>
    </main>
</body>
</html>