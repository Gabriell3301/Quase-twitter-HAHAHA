<?php
session_start();

$erro = $_SESSION['erro'] ?? '';
$user = $_SESSION['old_user'] ?? '';

unset($_SESSION['erro']);
unset($_SESSION['old_user']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Images\Logos\MiniLogo.jfif" type="png">
    <link rel="stylesheet" href="../CSS/login.css">
    <title>Login</title>
</head>
<body>
    <div class="login-container">
        <div class="form-header">
            Login Form
        </div>
        
        <div class="form-body">
            <form action="../PHP/login.php" method="POST">
                <div class="form-group">
                    <input 
                        type="text" 
                        class="form-control" 
                        id="email_or_user" 
                        name="email_or_user" 
                        placeholder="Email or User Name"
                        value="<?php echo htmlspecialchars($user); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                </div>
                
                <?php if ($erro): ?>
                    <div style="color:red; text-align:center; margin-bottom:6px; font-size:14px;">
                        <?php echo htmlspecialchars($erro); ?>
                    </div>
                <?php endif; ?>

                <div class="form-check">
                    <input type="checkbox" id="lembrar" name="lembrar">
                    <label for="lembrar">Lembrar-me</label>
                </div>
                
                <button type="submit" class="login-btn">Login</button>
                
                <div class="signup-link">
                    Não tem uma conta? <a href="cadastro.html">Cadastre-se agora!</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="form-footer">
        © 2025 All rights reserved | Design by Gabriell Barbosa
    </div>
</body>
</html>