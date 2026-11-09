<?php
require_once __DIR__ . '/config/session.php';
lf_session_start();
if (!empty($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) ?>">
    <title>Login | LabFlow</title>
    <link rel="icon" type="image/png" href="logo-sekolah.png">
    <link rel="stylesheet" href="assets/css/login.css?v=<?= @filemtime(__DIR__ . '/assets/css/login.css') ?>">
</head>
<body>

    <!-- BACKGROUND -->
    <div class="background">
        <!-- Animated diagonal lines -->
        <div class="lines"></div>
        <!-- Glow -->
        <div class="glow glow-1"></div>
        <div class="glow glow-2"></div>
        <!-- Particles -->
        <div class="particles"></div>
    </div>

    <!-- LOGIN CARD -->
    <main class="login-wrapper">
        <section class="login-card">
            <form class="login-content" id="loginForm" novalidate>
                <?php if (is_file(__DIR__ . '/logo-sekolah.png')): ?>
                <img class="login-logo" src="logo-sekolah.png" alt="Logo SMK Bina Informatika">
                <?php endif; ?>
                <h1>Masuk ke LabFlow</h1>
                <p class="subtitle">Koordinator Laboratorium &middot; SMK Bina Informatika</p>

                <div class="form-error" id="formError" role="alert" hidden></div>

                <!-- USERNAME -->
                <div class="input-group">
                    <label for="username">Username</label>
                    <div class="input-box">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"></circle>
                            <path d="M4 21a8 8 0 0 1 16 0"></path>
                        </svg>
                        <input type="text" id="username" placeholder="Masukkan username" autocomplete="username" autocapitalize="none" spellcheck="false" autofocus>
                    </div>
                </div>

                <!-- PASSWORD -->
                <div class="input-group">
                    <label for="password">Password</label>
                    <div class="input-box">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="5" y="10" width="14" height="11" rx="2"></rect>
                            <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                        </svg>
                        <input type="password" id="password" placeholder="Masukkan password" autocomplete="current-password">
                        <!-- Eye -->
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Tampilkan password">
                            <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                                <circle cx="12" cy="12" r="2.5"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- REMEMBER -->
                <label class="remember">
                    <input type="checkbox" id="remember">
                    <span class="custom-checkbox"></span>
                    <span>Simpan sesi</span>
                </label>

                <!-- LOGIN -->
                <button type="submit" class="login-button" id="loginButton">
                    <span class="btn-label">Login</span>
                    <span class="arrow">&rarr;</span>
                </button>
            </form>

            <!-- FOOTER -->
            <footer class="card-footer">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 3 5 6v5c0 4.5 3 8 7 10 4-2 7-5.5 7-10V6l-7-3Z"></path>
                    <path d="m9.5 12 1.7 1.7 3.5-3.5"></path>
                </svg>
                <span>SMK Bina Informatika &middot; Coordinator Lab</span>
            </footer>
        </section>
    </main>

    <script src="assets/js/login.js?v=<?= @filemtime(__DIR__ . '/assets/js/login.js') ?>"></script>
</body>
</html>