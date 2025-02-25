const express = require('express');
const router = express.Router();
const productosController = require('../controllers/productosController');
const verificarToken = require('../middleware/authMiddleware');
const upload = require('../config/multer'); // Importar configuración de subida

// Obtener todos los productos (requiere autenticación)
router.get('/', verificarToken, productosController.obtenerProductos);

// Obtener un producto por ID (requiere autenticación)
router.get('/:id', verificarToken, productosController.obtenerProductoPorId);

// Crear un producto con imagen/video (requiere autenticación)
router.post('/', verificarToken, upload.single('archivo'), productosController.crearProducto);

// Editar un producto (requiere autenticación)
router.put('/:id', verificarToken, productosController.editarProducto);

// Eliminar un producto (requiere autenticación)
router.delete('/:id', verificarToken, productosController.eliminarProducto);

module.exports = router;
