const express = require('express');
const {
    registrarUsuario,
    iniciarSesion,
    crearUsuarioConRol,
    obtenerTrabajadores,
    obtenerClientes,
    obtenerCompras
} = require('../controllers/usuariosController');

const verificarToken = require('../middleware/authMiddleware'); // Middleware para autenticación
const pool = require('../config/db');

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

// Ruta para obtener compras
router.get('/compras', verificarToken, obtenerCompras);

// Nueva ruta para obtener el perfil del usuario autenticado
router.get('/perfil', verificarToken, async (req, res) => {
    try {
        const usuario_id = req.usuario.id;
        const [usuario] = await pool.query(
            'SELECT id, nombre, email, telefono, rol, created_at FROM usuarios WHERE id = ?',
            [usuario_id]
        );

        if (usuario.length === 0) {
            return res.status(404).json({ error: 'Usuario no encontrado' });
        }

        res.json(usuario[0]); // Retorna la info del usuario
    } catch (error) {
        console.error('❌ Error al obtener el perfil:', error);
        res.status(500).json({ error: 'Error al obtener el perfil' });
    }
});

module.exports = router;
