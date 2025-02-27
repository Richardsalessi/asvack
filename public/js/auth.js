document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");
  const registerForm = document.getElementById("registerForm");

  if (loginForm) {
      loginForm.addEventListener("submit", async (e) => {
          e.preventDefault();
          const email = document.getElementById("loginEmail").value;
          const password = document.getElementById("loginPassword").value;

          const response = await fetch("/api/usuarios/login", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ email, password }),
          });

          const data = await response.json();
          if (response.ok) {
              localStorage.setItem("token", data.token);
              window.location.href = "dashboard.html";
          } else {
              alert(data.error || "Error al iniciar sesión");
          }
      });
  }

  if (registerForm) {
      registerForm.addEventListener("submit", async (e) => {
          e.preventDefault();
          const nombre = document.getElementById("registerNombre").value;
          const email = document.getElementById("registerEmail").value;
          const password = document.getElementById("registerPassword").value;

          const response = await fetch("/api/usuarios/registro", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ nombre, email, password }),
          });

          const data = await response.json();
          if (response.ok) {
              alert("Registro exitoso. Ahora puedes iniciar sesión.");
              window.location.href = "login.html";
          } else {
              alert(data.error || "Error al registrarse");
          }
      });
  }
});
