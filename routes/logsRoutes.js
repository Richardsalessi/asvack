const express = require('express');
const { obtenerLogs } = require('../controllers/logsController');
const verificarToken = require('../middleware/authMiddleware');

const router = express.Router();

// Ruta para obtener logs (solo para administradores)
router.get('/', verificarToken, async (req, res) => {
    if (req.usuario.rol !== 'admin') {
        return res.status(403).json({ success: false, error: 'No tienes permisos para ver los logs' });
    }
    obtenerLogs(req, res);
});

module.exports = router;
