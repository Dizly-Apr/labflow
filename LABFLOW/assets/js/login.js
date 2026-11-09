document.addEventListener("DOMContentLoaded", () => {

    /* =========================
       CREATE PARTICLES
    ========================= */

    const particleContainer = document.querySelector(".particles");

    if (particleContainer) {
        const particleCount = 25;

        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement("span");
            particle.classList.add("particle");

            // Random position
            particle.style.left = Math.random() * 100 + "%";
            particle.style.top = Math.random() * 100 + "%";

            // Random size
            const size = Math.random() * 4 + 2;
            particle.style.width = size + "px";
            particle.style.height = size + "px";

            // Random movement
            particle.style.setProperty("--move-x", (Math.random() * 120 - 60) + "px");
            particle.style.setProperty("--move-y", (Math.random() * 120 - 60) + "px");

            // Random animation duration
            particle.style.setProperty("--duration", (Math.random() * 6 + 6) + "s");
            particle.style.setProperty("--blink", (Math.random() * 3 + 2) + "s");

            // Random animation delay
            particle.style.animationDelay = "-" + (Math.random() * 8) + "s";

            particleContainer.appendChild(particle);
        }
    }


    /* =========================
       SHOW / HIDE PASSWORD
    ========================= */

    const password = document.getElementById("password");
    const togglePassword = document.getElementById("togglePassword");
    const eyeIcon = document.getElementById("eyeIcon");

    if (togglePassword && password) {
        togglePassword.addEventListener("click", () => {
            const isPassword = password.type === "password";
            password.type = isPassword ? "text" : "password";

            togglePassword.setAttribute(
                "aria-label",
                isPassword ? "Sembunyikan password" : "Tampilkan password"
            );

            if (isPassword) {
                eyeIcon.innerHTML = `
                    <path d="M3 3l18 18"></path>
                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                    <path d="M9.9 5.2A10.5 10.5 0 0 1 12 5 c6 0 9.5 7 9.5 7 a17.5 17.5 0 0 1-3.2 4.1"></path>
                    <path d="M6.3 6.3C3.9 8 2.5 12 2.5 12 s3.5 6 9.5 6 c1.5 0 2.8-.3 4-.8"></path>
                `;
            } else {
                eyeIcon.innerHTML = `
                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6 -3.5 6-9.5 6 -9.5-6-9.5-6Z"></path>
                    <circle cx="12" cy="12" r="2.5"></circle>
                `;
            }
        });
    }


    /* =========================
       LOGIN (submit form)
    ========================= */

    const form = document.getElementById("loginForm");
    const loginButton = document.getElementById("loginButton");
    const usernameInput = document.getElementById("username");
    const errorBox = document.getElementById("formError");

    function showError(msg, field) {
        if (errorBox) { errorBox.textContent = msg; errorBox.hidden = false; }
        document.querySelectorAll(".input-box").forEach(b => b.classList.remove("invalid"));
        if (field) { field.closest(".input-box").classList.add("invalid"); field.focus(); }
        shakeCard();
    }

    function clearError() {
        if (errorBox) errorBox.hidden = true;
        document.querySelectorAll(".input-box").forEach(b => b.classList.remove("invalid"));
    }

    [usernameInput, password].forEach(el => el && el.addEventListener("input", clearError));

    if (form) {
        form.addEventListener("submit", (event) => {
            event.preventDefault();
            if (loginButton.disabled) return;

            const username = usernameInput.value.trim();
            const passwordValue = password.value.trim();

            if (!username) return showError("Username wajib diisi.", usernameInput);
            if (!passwordValue) return showError("Password wajib diisi.", password);

            clearError();
            loginButton.disabled = true;
            loginButton.classList.add("loading");
            loginButton.querySelector(".btn-label").textContent = "Memeriksa...";

            const reset = () => {
                loginButton.disabled = false;
                loginButton.classList.remove("loading");
                loginButton.querySelector(".btn-label").textContent = "Login";
            };

            // Verifikasi ke database
            fetch("config/login_process.php", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-Token": (document.querySelector('meta[name="csrf-token"]') || {}).content || "" },
                body: JSON.stringify({
                    username: username,
                    password: passwordValue,
                    remember: document.getElementById("remember").checked
                })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    loginButton.querySelector(".btn-label").textContent = "Berhasil! Mengalihkan...";
                    window.location.href = res.redirect;
                } else {
                    reset();
                    showError(res.message || "Login gagal.", password);
                    password.select();
                }
            })
            .catch(() => {
                reset();
                showError("Gagal menghubungi server. Periksa koneksi lalu coba lagi.");
            });
        });
    }


    /* =========================
       SHAKE CARD
    ========================= */

    function shakeCard() {
        const card = document.querySelector(".login-card");
        if (card) {
            card.animate(
                [
                    { transform: "translateX(0)" },
                    { transform: "translateX(-6px)" },
                    { transform: "translateX(6px)" },
                    { transform: "translateX(-4px)" },
                    { transform: "translateX(4px)" },
                    { transform: "translateX(0)" }
                ],
                {
                    duration: 350,
                    easing: "ease-in-out"
                }
            );
        }
    }

});
