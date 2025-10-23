<?php
include("conexao.php");
$db = LigaDB();
    if($_SERVER['REQUEST_METHOD'] === "POST")
    {
        $username = trim($_POST["user_n"]);
        $email = trim($_POST["user_Email"]);
        $senha = $_POST["user_pass"];
        $confirmar_senha = $_POST["repeat_pass"];

        if ($senha !== $confirmar_senha) {
            die("Erro: As senhas não coincidem!");
        }

        // Hash da senha para segurança
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // Verificar se o e-mail já existe no banco
        $verificar = $db->prepare("SELECT id FROM user WHERE email = :email");
        $verificar->bindValue(":email", $email, SQLITE3_TEXT);
        $resultado = $verificar->execute()->fetchArray();

        if ($resultado) {
            die("Erro: Este e-mail já está cadastrado!");
        }

        // Inserir os dados no banco
        $query = $db->prepare("INSERT INTO user (nome_user, email, senha) VALUES (:nome_user, :email, :senha)");
        $query->bindValue(":nome_user", $username, SQLITE3_TEXT);
        $query->bindValue(":email", $email, SQLITE3_TEXT);
        $query->bindValue(":senha", $senha_hash, SQLITE3_TEXT);

        if ($query->execute()) {
            echo "Cadastro realizado com sucesso!";
            header("refresh:2; url=../HTML/login_page.html"); // Redireciona após 2 segundos
        } else {
            echo "Erro ao cadastrar usuário!";
        }
    }
    $db->close();
?>