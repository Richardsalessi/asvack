const pool = require('../config/db');
const { registrarLog } = require('../helpers/logsHelper');

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
        console.error('❌ Error al obtener el carrito:', error);
        res.status(500).json({ error: 'Error al obtener el carrito' });
    }
};

// Agregar un producto al carrito con validación de stock
const agregarAlCarrito = async (req, res) => {
    try {
        const usuario_id = req.usuario.id;
        const { producto_id, cantidad } = req.body;

        if (!producto_id || !cantidad) {
            return res.status(400).json({ error: 'Producto y cantidad son obligatorios' });
        }

        // Verificar si el producto tiene stock disponible antes de agregarlo
        const [producto] = await pool.query('SELECT stock FROM productos WHERE id = ?', [producto_id]);

        if (!producto.length) {
            return res.status(404).json({ error: 'Producto no encontrado' });
        }

        if (producto[0].stock <= 0) {
            return res.status(400).json({ error: 'No se puede agregar al carrito, el producto no tiene stock disponible' });
        }

        await pool.query(
            'INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)',
            [usuario_id, producto_id, cantidad]
        );

        // Registrar en logs
        await registrarLog(usuario_id, `Agregó el producto ${producto_id} al carrito`);

        res.json({ mensaje: 'Producto agregado al carrito' });
    } catch (error) {
        console.error('❌ Error al agregar producto al carrito:', error);
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

        // Registrar en logs
        await registrarLog(usuario_id, `Modificó la cantidad del producto en el carrito (ID: ${id}) a ${cantidad}`);

        res.json({ mensaje: 'Cantidad actualizada en el carrito' });
    } catch (error) {
        console.error('❌ Error al actualizar la cantidad:', error);
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

        // Registrar en logs
        await registrarLog(usuario_id, `Eliminó el producto del carrito (ID: ${id})`);

        res.json({ mensaje: 'Producto eliminado del carrito' });
    } catch (error) {
        console.error('❌ Error al eliminar producto del carrito:', error);
        res.status(500).json({ error: 'Error al eliminar producto del carrito' });
    }
};

module.exports = {
    obtenerCarrito,
    agregarAlCarrito,
    editarCarrito,
    eliminarDelCarrito
};
