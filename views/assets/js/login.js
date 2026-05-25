document.addEventListener("DOMContentLoaded", function () {
    const togglePassword = document.querySelector("#toggle-password");
    const passwordField = document.querySelector("#password-field");

    // UX: Alternar visibilidade da senha
    if (togglePassword && passwordField) {
        togglePassword.addEventListener("click", function () {
            const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
            passwordField.setAttribute("type", type);
            
            // Alterna o ícone do olho
            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
        });
    }
});