<?php
session_start();
require('conexao.php'); // se quiser salvar o nome da imagem no banco

if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
    $imagem = $_FILES['imagem'];

    // Pasta de destino
    $pastaDestino = '../Images/users/';

    // Cria nome único para a imagem
    $extensao = pathinfo($imagem['name'], PATHINFO_EXTENSION);
    $nomeUnico = uniqid('perfil_', true) . '.' . strtolower($extensao);

    // Caminho completo
    $caminhoCompleto = $pastaDestino . $nomeUnico;

    // Verificar se já existe uma imagem de perfil associada ao usuário
    if (isset($_SESSION['id_user'])) {
        $db = LigaDB();
        $id = $_SESSION['id_user'];

        // Buscar a imagem atual do banco de dados
        $stmt = $db->prepare("SELECT perfil_imagem FROM user WHERE id = :id");
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $user = $result->fetchArray(SQLITE3_ASSOC);

        // Se existir uma imagem de perfil no banco, excluir o arquivo antigo
        if ($user && !empty($user['perfil_imagem']) && file_exists($user['perfil_imagem'])) {
            unlink($user['perfil_imagem']); // Exclui o arquivo da imagem antiga
        }

        // Agora, move a nova imagem para a pasta
        if (move_uploaded_file($imagem['tmp_name'], $caminhoCompleto)) {
            echo "Imagem enviada com sucesso!";
            header("Location: configuracao.php");

            // Salvar o caminho da nova imagem no banco de dados
            $stmtUpdate = $db->prepare("UPDATE user SET perfil_imagem = :img WHERE id = :id");
            $stmtUpdate->bindValue(':img', $caminhoCompleto, SQLITE3_TEXT);
            $stmtUpdate->bindValue(':id', $id, SQLITE3_INTEGER);
            $stmtUpdate->execute();
        } else {
            echo "Erro ao salvar a imagem.";
        }
    }
} else {
    echo "Nenhuma imagem enviada ou erro no upload.";
}
?>
