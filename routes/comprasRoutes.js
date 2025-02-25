const express = require('express');
const router = express.Router();
const comprasController = require('../controllers/comprasController');
const verificarToken = require('../middleware/authMiddleware'); // Middleware para proteger las rutas

// Obtener todas las compras del usuario autenticado
router.get('/', verificarToken, comprasController.obtenerCompras);

// Obtener los detalles de una compra
router.get('/:id', verificarToken, comprasController.obtenerDetalleCompra);

// Crear una nueva compra desde el carrito
router.post('/', verificarToken, comprasController.crearCompra);

// Eliminar una compra (esto también elimina sus detalles por ON DELETE CASCADE)
router.delete('/:id', verificarToken, comprasController.eliminarCompra);

module.exports = router;
