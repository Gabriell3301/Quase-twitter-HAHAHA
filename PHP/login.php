<?php
session_start();
include("conexao.php");

$db = LigaDB();

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $emailOuUsuario = trim($_POST["email_or_user"]);
    $senha = trim($_POST["senha"]);

    // Guarda o user pra não perder no form
    $_SESSION['old_user'] = $emailOuUsuario;

    // Validação básica
    if (empty($emailOuUsuario) || empty($senha)) {
        $_SESSION['erro'] = "Preencha todos os campos";
        header("Location: ../HTML/login_page.php");
        exit();
    }

    // Query única (evita diferença de comportamento)
    if (strpos($emailOuUsuario, '@') !== false) {
    $stmt = $db->prepare("
        SELECT id, nome_user, senha 
        FROM user 
        WHERE email = :value COLLATE NOCASE
    ");
    }
    else {
        $stmt = $db->prepare("
            SELECT id, nome_user, senha 
            FROM user 
            WHERE nome_user = :value COLLATE NOCASE
        ");
    }

    $stmt->bindValue(":value", $emailOuUsuario, SQLITE3_TEXT);
    $resultado = $stmt->execute();
    $usuario = $resultado->fetchArray(SQLITE3_ASSOC);

    // Hash fake pra evitar timing attack
    $fakeHash = '$2y$10$usesomesillystringforexample';

    $senhaValida = false;

    if ($usuario) {
        $senhaValida = password_verify($senha, $usuario["senha"]);
    } else {
        // simula verificação mesmo sem usuário
        password_verify($senha, $fakeHash);
    }

    // Verificação final (sem revelar o erro)
    if ($usuario && $senhaValida) {

        $_SESSION["id_user"] = $usuario["id"];
        $_SESSION["user_name"] = $usuario["nome_user"];

        // limpa dados temporários
        unset($_SESSION['erro']);
        unset($_SESSION['old_user']);

        header("Location: feed.php");
        exit();

    } else {

        // Mensagem genérica (ANTI ENUMERAÇÃO)
        $_SESSION['erro'] = "Credenciais inválidas";

        // Pequeno delay (anti brute force)
        sleep(1);

        header("Location: ../HTML/login_page.php");
        exit();
    }
}
?>