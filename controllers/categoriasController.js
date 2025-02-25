const pool = require('../config/db');

// Obtener todas las categorías
const obtenerCategorias = async (req, res) => {
    try {
        const [rows] = await pool.query('SELECT * FROM categorias');
        res.json(rows);
    } catch (error) {
        res.status(500).json({ error: 'Error al obtener categorías' });
    }
};

// Obtener una categoría por ID
const obtenerCategoriaPorId = async (req, res) => {
    try {
        const { id } = req.params;
        const [rows] = await pool.query('SELECT * FROM categorias WHERE id = ?', [id]);

        if (rows.length === 0) {
            return res.status(404).json({ error: 'Categoría no encontrada' });
        }

        res.json(rows[0]);
    } catch (error) {
        res.status(500).json({ error: 'Error al obtener la categoría' });
    }
};

// Crear una nueva categoría
const crearCategoria = async (req, res) => {
    try {
        const { nombre } = req.body;

        if (!nombre) {
            return res.status(400).json({ error: 'El nombre es obligatorio' });
        }

        await pool.query('INSERT INTO categorias (nombre) VALUES (?)', [nombre]);

        res.json({ mensaje: 'Categoría creada con éxito' });
    } catch (error) {
        res.status(500).json({ error: 'Error al crear la categoría' });
    }
};

// Editar una categoría por ID
const editarCategoria = async (req, res) => {
    try {
        const { id } = req.params;
        const { nombre } = req.body;

        const [result] = await pool.query(
            'UPDATE categorias SET nombre = ? WHERE id = ?',
            [nombre, id]
        );

        if (result.affectedRows === 0) {
            return res.status(404).json({ error: 'Categoría no encontrada' });
        }

        res.json({ mensaje: 'Categoría actualizada con éxito' });
    } catch (error) {
        res.status(500).json({ error: 'Error al actualizar la categoría' });
    }
};

// Eliminar una categoría por ID
const eliminarCategoria = async (req, res) => {
    try {
        const { id } = req.params;

        const [result] = await pool.query('DELETE FROM categorias WHERE id = ?', [id]);

        if (result.affectedRows === 0) {
            return res.status(404).json({ error: 'Categoría no encontrada' });
        }

        res.json({ mensaje: 'Categoría eliminada con éxito' });
    } catch (error) {
        res.status(500).json({ error: 'Error al eliminar la categoría' });
    }
};

module.exports = {
    obtenerCategorias,
    obtenerCategoriaPorId,
    crearCategoria,
    editarCategoria,
    eliminarCategoria
};
