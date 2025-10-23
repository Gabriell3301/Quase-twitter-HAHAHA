<?php
session_start();
require('conexao.php');

if (isset($_SESSION['id_user'])) {
    $db = LigaDB();
    $id_user = $_SESSION['id_user'];
    
    // Preparar consulta para obter dados do usuário
    $query = $db->prepare("SELECT nome_user, perfil_imagem FROM user WHERE id = :id_user");
    $query->bindValue(':id_user', $id_user, SQLITE3_INTEGER);
    $result = $query->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC); 

    if ($user) {
        $user_nome = $user['nome_user'];
        $perfil_imagem = $user['perfil_imagem'];
    } else {
        echo "Usuário não encontrado!";
    }

    // Atualizar nome do usuário
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome_user'])) {
        $new_name = trim($_POST['nome_user']);
        if (!empty($new_name)) {
            $updateQuery = $db->prepare("UPDATE user SET nome_user = :nome_user WHERE id = :id_user");
            $updateQuery->bindValue(':nome_user', $new_name, SQLITE3_TEXT);
            $updateQuery->bindValue(':id_user', $id_user, SQLITE3_INTEGER);
            $updateQuery->execute();
            header("Location: configuracao.php"); // Recarrega a página após salvar o nome
        }
    
    }
} else {
    echo "Você precisa estar logado para acessar esta página.";
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/configuração.css">
    <title>Configurações - <?php echo htmlspecialchars($user_nome); ?></title>
</head>
<body>
    <div class="settings-container">
        <div class="form-header">
            <h1>Configurações de Perfil</h1>
        </div>
        
        <!-- Formulário para atualizar o nome -->
        <form action="configuracao.php" method="POST" class="form-body">
            <div class="form-group">
                <label for="nome_user" class="form-label">Nome de usuário:</label>
                <input type="text" name="nome_user" id="nome_user" value="<?php echo htmlspecialchars($user_nome); ?>" class="form-control" required>
            </div>
            <button type="submit" class="save-btn">Atualizar Nome</button>
        </form>
        <!-- Formulário para atualizar a foto de perfil -->
        <form action="save_img.php" method="POST" enctype="multipart/form-data" class="form-body">
            <div class="form-group profile-image-container">
                <img id="preview_image" src="<?php echo $perfil_imagem ? $perfil_imagem : 'default-avatar.png'; ?>" alt="Foto de Perfil" class="profile-image">
                <label for="imagem" class="image-upload-label">Escolher Imagem</label>
                <input type="file" name="imagem" id="imagem" class="image-upload" accept="image/*" required onchange="previewImage(event)">
            </div>
            <button type="submit" class="save-btn">Atualizar Foto</button>
        </form>
    <!-- Botão para voltar ao Feed -->
    <form action="feed.php">
            <button type="submit" class="back-btn">Voltar ao Feed</button>
        </form>
    </div>

    <!-- Script JavaScript -->
    <script>
        function previewImage(event) {
            const file = event.target.files[0]; // Obtém o arquivo selecionado
            const reader = new FileReader();

            reader.onload = function(e) {
                const preview = document.getElementById('preview_image');
                preview.src = e.target.result; // Atualiza a imagem exibida na tela
            }

            if (file) {
                reader.readAsDataURL(file); // Lê o arquivo como URL de dados
            }
        }
    </script>
</body>
</html>