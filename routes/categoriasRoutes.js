const express = require('express');
const router = express.Router();
const categoriasController = require('../controllers/categoriasController');
const verificarToken = require('../middleware/authMiddleware'); // Middleware para autenticación

// Obtener todas las categorías (Requiere autenticación)
router.get('/', verificarToken, categoriasController.obtenerCategorias);

// Obtener una categoría por ID (Requiere autenticación)
router.get('/:id', verificarToken, categoriasController.obtenerCategoriaPorId);

// Crear una nueva categoría (Requiere autenticación)
router.post('/', verificarToken, categoriasController.crearCategoria);

// Editar una categoría por ID (Requiere autenticación)
router.put('/:id', verificarToken, categoriasController.editarCategoria);

// Eliminar una categoría por ID (Requiere autenticación)
router.delete('/:id', verificarToken, categoriasController.eliminarCategoria);

module.exports = router;
