const express = require('express');
const router = express.Router();
const productosController = require('../controllers/productosController');
const verificarToken = require('../middleware/authMiddleware'); // Protege las rutas

// Obtener todos los productos (requiere autenticación)
router.get('/', verificarToken, productosController.obtenerProductos);

// Obtener un producto por ID (requiere autenticación)
router.get('/:id', verificarToken, productosController.obtenerProductoPorId);

// Crear un producto (requiere autenticación)
router.post('/', verificarToken, productosController.crearProducto);

// Editar un producto por ID (requiere autenticación)
router.put('/:id', verificarToken, productosController.editarProducto);

// Eliminar un producto por ID (requiere autenticación)
router.delete('/:id', verificarToken, productosController.eliminarProducto);

module.exports = router;
