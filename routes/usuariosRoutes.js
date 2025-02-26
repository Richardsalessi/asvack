const express = require('express');
const {
    registrarUsuario,
    iniciarSesion,
    crearUsuarioConRol,
    obtenerTrabajadores,
    obtenerClientes
} = require('../controllers/usuariosController');

const verificarToken = require('../middleware/authMiddleware'); // Middleware para autenticación

const router = express.Router();

// Rutas de autenticación
router.post('/registro', registrarUsuario);
router.post('/login', iniciarSesion);

// Ruta para que un admin cree usuarios con roles (admin o trabajador)
router.post('/crear', verificarToken, crearUsuarioConRol);

// Ruta para que el admin vea los trabajadores
router.get('/trabajadores', verificarToken, obtenerTrabajadores);

// Ruta para que el admin vea los clientes
router.get('/clientes', verificarToken, obtenerClientes);

module.exports = router;
