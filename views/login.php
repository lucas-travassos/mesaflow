<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MesaFlow | Login</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="views/assets/css/login.css">
</head>
<body class="login-page">

    <main class="login-container">

        <section class="login-brand">
            <div class="brand-icon">
                <i class="fa-solid fa-utensils"></i>
            </div>
            <h1>MesaFlow</h1>
            <p>HOSPITALITY COMMAND CENTER</p>
        </section>

        <section class="login-card">
            <h2>Staff Login</h2>
            <p class="login-subtitle">Access your station dashboard</p>

            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger bg-dark text-danger border-secondary text-center small mb-3 py-2" role="alert" style="border-radius: 8px;">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i> <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>

            <form action="index.php?action=login" method="POST">
                <div class="form-group">
                    <input 
                        type="email" 
                        name="email"
                        class="form-control login-input" 
                        placeholder="Staff Email"
                        required
                    >
                </div>

                <div class="form-group password-group">
                    <input 
                        type="password" 
                        name="senha"
                        id="password-field"
                        class="form-control login-input" 
                        placeholder="Password"
                        required
                    >
                    <i class="fa-regular fa-eye password-icon" id="toggle-password" style="cursor: pointer;"></i>
                </div>

                <div class="login-options">
                    <div class="biometric-option">
                        <i class="fa-solid fa-fingerprint"></i>
                        <span>Enable Biometrics</span>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="remember_me">
                        <span class="slider"></span>
                    </label>
                </div>

                <button type="submit" class="btn btn-login">
                    Secure Entry
                </button>
            </form>

            <a href="#" class="forgot-link">Forgot your credentials?</a>
        </section>

    </main>

    <script src="views/assets/js/login.js"></script>
</body>
</html>