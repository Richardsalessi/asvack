const express = require('express');
const router = express.Router();
const carritoController = require('../controllers/carritoController');
const verificarToken = require('../middleware/authMiddleware'); // Middleware para proteger las rutas

// Obtener el carrito del usuario autenticado
router.get('/', verificarToken, carritoController.obtenerCarrito);

// Agregar un producto al carrito
router.post('/', verificarToken, carritoController.agregarAlCarrito);

// Editar la cantidad de un producto en el carrito
router.put('/:id', verificarToken, carritoController.editarCarrito);

// Eliminar un producto del carrito
router.delete('/:id', verificarToken, carritoController.eliminarDelCarrito);

module.exports = router;
