const pool = require('../config/db');

// Obtener los productos en el carrito del usuario autenticado
const obtenerCarrito = async (req, res) => {
    try {
        const usuario_id = req.usuario.id;
        const [rows] = await pool.query(
            'SELECT c.id, p.nombre, p.precio, c.cantidad FROM carrito c INNER JOIN productos p ON c.producto_id = p.id WHERE c.usuario_id = ?',
            [usuario_id]
        );
        res.json(rows);
    } catch (error) {
        res.status(500).json({ error: 'Error al obtener el carrito' });
    }
};

// Agregar un producto al carrito
const agregarAlCarrito = async (req, res) => {
    try {
        const usuario_id = req.usuario.id;
        const { producto_id, cantidad } = req.body;

        if (!producto_id || !cantidad) {
            return res.status(400).json({ error: 'Producto y cantidad son obligatorios' });
        }

        await pool.query(
            'INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)',
            [usuario_id, producto_id, cantidad]
        );

        res.json({ mensaje: 'Producto agregado al carrito' });
    } catch (error) {
        res.status(500).json({ error: 'Error al agregar producto al carrito' });
    }
};

// Editar la cantidad de un producto en el carrito
const editarCarrito = async (req, res) => {
    try {
        const { id } = req.params;
        const { cantidad } = req.body;
        const usuario_id = req.usuario.id;

        const [result] = await pool.query(
            'UPDATE carrito SET cantidad = ? WHERE id = ? AND usuario_id = ?',
            [cantidad, id, usuario_id]
        );

        if (result.affectedRows === 0) {
            return res.status(404).json({ error: 'Producto en el carrito no encontrado' });
        }

        res.json({ mensaje: 'Cantidad actualizada en el carrito' });
    } catch (error) {
        res.status(500).json({ error: 'Error al actualizar la cantidad' });
    }
};

// Eliminar un producto del carrito
const eliminarDelCarrito = async (req, res) => {
    try {
        const { id } = req.params;
        const usuario_id = req.usuario.id;

        const [result] = await pool.query(
            'DELETE FROM carrito WHERE id = ? AND usuario_id = ?',
            [id, usuario_id]
        );

        if (result.affectedRows === 0) {
            return res.status(404).json({ error: 'Producto en el carrito no encontrado' });
        }

        res.json({ mensaje: 'Producto eliminado del carrito' });
    } catch (error) {
        res.status(500).json({ error: 'Error al eliminar producto del carrito' });
    }
};

module.exports = {
    obtenerCarrito,
    agregarAlCarrito,
    editarCarrito,
    eliminarDelCarrito
};
