<?php
session_start();
// Conectar ao banco de dados
require("conexao.php");
$db = LigaDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_SESSION['id_user'])) {
        die("Erro: Usuário não autenticado.");
    }
    $id_user = $_SESSION['id_user'];

    // Verificar se estamos marcando uma notificação como lida
    if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
        $notif_id = $_GET['mark_read'];
        $mark_read = $db->prepare("UPDATE notificacoes SET lida = 1 WHERE id = ? AND user_id = ?");
        $mark_read->bindValue(1, $notif_id, SQLITE3_INTEGER);
        $mark_read->bindValue(2, $id_user, SQLITE3_INTEGER);
        $mark_read->execute();
        
        // Se houver um post associado, redirecionar para ele
        if (isset($_GET['post_id']) && is_numeric($_GET['post_id'])) {
            header("Location: big_post.php?id=" . $_GET['post_id']);
            exit;
        }
    }

    // Buscar as notificações do usuário incluindo o status de lida
    $stmt = $db->prepare("SELECT id, mensagem, data_criada, id_post, lida FROM notificacoes WHERE user_id = ? ORDER BY data_criada DESC");
    $stmt->bindValue(1, $id_user, SQLITE3_INTEGER);
    $result = $stmt->execute();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificações</title>
    <link rel="stylesheet" href="../CSS/notification.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="header">
        <button onclick="window.location.href = 'feed.php';" class="header-back">← Voltar</button>
        <h2>Notificações</h2>
    </div>

    <div class="notifications-container">
        <?php 
        $hasNotifications = false;
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) : 
            $hasNotifications = true;
            $isUnread = $row['lida'] == 0 ? true : false;
        ?>
            <div class="notification <?= isset($row['id_post']) ? 'notification-clickable' : '' ?> <?= $isUnread ? 'notification-unread' : '' ?>">
                <?php if(isset($row['id_post'])): ?>
                <a href="notification.php?mark_read=<?= $row['id'] ?>&post_id=<?= $row['id_post'] ?>" class="notification-link">
                <?php endif; ?>
                    <div class="notification-icon">
                        <?php if($isUnread): ?>
                            <i class="fas fa-bell-on"></i>
                        <?php else: ?>
                            <i class="fas fa-bell"></i>
                        <?php endif; ?>
                    </div>
                    <div class="notification-content">
                        <div class="notification-text"><?= htmlspecialchars($row['mensagem']) ?></div>
                        <div class="notification-time"><?= date('d/m/Y H:i', strtotime($row['data_criada'])) ?></div>
                    </div>
                    <?php if($isUnread): ?>
                        <div class="notification-badge"></div>
                    <?php endif; ?>
                    <?php if(isset($row['id_post'])): ?>
                    <div class="notification-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    <?php endif; ?>
                <?php if(isset($row['id_post'])): ?>
                </a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
        
        <?php if (!$hasNotifications) : ?>
            <div class="no-notifications">
                <i class="fas fa-inbox"></i>
                <p>Você não tem notificações no momento</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
}
?>