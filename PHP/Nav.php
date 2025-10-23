<?php
// Notificações
    include_once("conexao.php");
    $db = LigaDB();
    // Exemplo: buscar notificações não lidas do usuário logado
    $id_user = $_SESSION['id_user'];
    $query = $db->prepare("SELECT COUNT(*) as total FROM notificacoes WHERE user_id = :id AND lida = 0");
    $query->bindValue(":id", $id_user, SQLITE3_INTEGER);
    $result = $query->execute(); 
    $notificacao = $result->fetchArray(SQLITE3_ASSOC);
    $nao_lidas = $notificacao['total'];
    $result->finalize();
?>

<nav class='nav-bottom'>
        <a href="feed.php" class='nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'feed.php' ? 'active' : ''; ?>'><span class='icon'>🏠</span></a>
        <a href="pesquisa.php" class='nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'pesquisa.php' ? 'active' : ''; ?>'><span class='icon'>🔍</span></a>
        <a href="notification.php" class="nav-item notificacao-btn <?php echo basename($_SERVER['PHP_SELF']) == 'notification.php' ? 'active' : ''; ?>">
            <span class="icon">✉️</span>
                <?php if ($nao_lidas > 0): ?>
                    <span class="notificacao-badge">
                        <?php echo $nao_lidas > 9 ? '9+' : $nao_lidas; ?>
                    </span>
                <?php endif; ?>
        </a>
    </nav>