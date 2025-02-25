const io = require("socket.io-client");

// Conectarse al servidor WebSocket
const socket = io("http://localhost:3000");

// Confirmar conexión
socket.on("connect", () => {
    console.log("✅ Conectado al servidor de Socket.io");
});

// Escuchar notificaciones de nuevas compras
socket.on("nueva_compra", (data) => {
    console.log("🛒 Nueva compra recibida:", data);
});

// Manejar la desconexión
socket.on("disconnect", () => {
    console.log("❌ Desconectado del servidor de Socket.io");
});
