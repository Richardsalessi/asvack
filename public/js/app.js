document.addEventListener("DOMContentLoaded", () => {
  const token = localStorage.getItem("token");

  if (token) {
      document.getElementById("logoutBtn").addEventListener("click", () => {
          localStorage.removeItem("token");
          window.location.href = "login.html";
      });
  } else {
      if (!window.location.pathname.includes("login") && !window.location.pathname.includes("registro")) {
          window.location.href = "login.html";
      }
  }
});
