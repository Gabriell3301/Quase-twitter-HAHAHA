<?php
session_start();
include("conexao.php");
$db = LigaDB(); 
    if ($_SERVER['REQUEST_METHOD'] === "POST")
    {
        $emailOuUsuario = trim($_POST["email_or_user"]);
        $senha = trim($_POST["senha"]);

        // Verifica se os campos não estão vazios
        if (empty($emailOuUsuario) || empty($senha)) {
            die("Preencha todos os campos!");
        }
        // Verifica se a entrada contém '@' para diferenciar entre e-mail e nome de usuário
        if (strpos($emailOuUsuario, '@') !== false) {
            // Trata como e-mail
            $stmt = $db->prepare("SELECT id, nome_user, senha FROM user WHERE email = :email");
            $stmt->bindValue(":email", $emailOuUsuario, SQLITE3_TEXT);
        } else {
            // Trata como nome de usuário
            $stmt = $db->prepare("SELECT id, nome_user, senha FROM user WHERE nome_user = :nome_user");
            $stmt->bindValue(":nome_user", $emailOuUsuario, SQLITE3_TEXT);
        }
        $resultado = $stmt->execute();
        $usuario = $resultado->fetchArray(SQLITE3_ASSOC);

        if ($usuario) {
            // Verifica se a senha inserida bate com a senha armazenada no banco (hash)
            if (password_verify($senha, $usuario["senha"])) {
                // Login bem-sucedido: armazena informações do usuário na sessão
                $_SESSION["id_user"] = $usuario["id"];
                $_SESSION["user_name"] = $usuario["nome_user"];
                header("Location: feed.php"); // Redireciona para a página principal
                exit();
            } else {
                echo "Senha incorreta!";
            }
        } else {
            echo "Usuário não encontrado!";
        }
    }
?>