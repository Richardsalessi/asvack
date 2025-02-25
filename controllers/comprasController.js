const pool = require('../config/db');
const { io } = require('../socket'); // Importamos `io` desde socket.js

// Obtener todas las compras del usuario autenticado
const obtenerCompras = async (req, res) => {
    try {
        const usuario_id = req.usuario.id;
        const [compras] = await pool.query('SELECT * FROM compras WHERE usuario_id = ?', [usuario_id]);
        res.json(compras);
    } catch (error) {
        res.status(500).json({ error: 'Error al obtener compras' });
    }
};

// Obtener detalles de una compra específica
const obtenerDetalleCompra = async (req, res) => {
    try {
        const { id } = req.params;
        const usuario_id = req.usuario.id;

        // Verificar que la compra le pertenece al usuario
        const [compra] = await pool.query('SELECT * FROM compras WHERE id = ? AND usuario_id = ?', [id, usuario_id]);
        if (compra.length === 0) {
            return res.status(404).json({ error: 'Compra no encontrada' });
        }

        // Obtener detalles de los productos comprados
        const [detalles] = await pool.query(`
            SELECT d.producto_id, p.nombre, d.cantidad, d.precio, d.subtotal 
            FROM detalles_compra d
            INNER JOIN productos p ON d.producto_id = p.id
            WHERE d.compra_id = ?
        `, [id]);

        res.json({ compra: compra[0], detalles });
    } catch (error) {
        res.status(500).json({ error: 'Error al obtener los detalles de la compra' });
    }
};

// Crear una nueva compra con productos del carrito
const crearCompra = async (req, res) => {
    try {
        const usuario_id = req.usuario.id;

        // Obtener productos en el carrito del usuario
        const [carrito] = await pool.query(
            `SELECT c.producto_id, c.cantidad, p.precio, p.stock 
            FROM carrito c 
            INNER JOIN productos p ON c.producto_id = p.id 
            WHERE c.usuario_id = ?`,
            [usuario_id]
        );

        if (carrito.length === 0) {
            return res.status(400).json({ error: 'El carrito está vacío' });
        }

        let total = 0;

        // Verificar stock antes de procesar la compra
        for (const item of carrito) {
            if (item.stock < item.cantidad) {
                return res.status(400).json({ error: `Stock insuficiente para el producto ID ${item.producto_id}` });
            }
            total += item.cantidad * item.precio;
        }

        // Crear la compra
        const [compraResult] = await pool.query(
            'INSERT INTO compras (usuario_id, total) VALUES (?, ?)',
            [usuario_id, total]
        );
        const compra_id = compraResult.insertId;

        // Insertar en detalles_compra y actualizar stock
        for (const item of carrito) {
            await pool.query(
                'INSERT INTO detalles_compra (compra_id, producto_id, cantidad, precio, subtotal) VALUES (?, ?, ?, ?, ?)',
                [compra_id, item.producto_id, item.cantidad, item.precio, item.cantidad * item.precio]
            );

            await pool.query(
                'UPDATE productos SET stock = stock - ? WHERE id = ?',
                [item.cantidad, item.producto_id]
            );
        }

        // Vaciar el carrito
        await pool.query('DELETE FROM carrito WHERE usuario_id = ?', [usuario_id]);

        // 🔴 Emitir evento de compra realizada en `Socket.io`
        io.emit('nueva_compra', { compra_id, usuario_id, total });

        res.json({ mensaje: 'Compra realizada con éxito', compra_id, total });
    } catch (error) {
        res.status(500).json({ error: 'Error al procesar la compra' });
    }
};

// Eliminar una compra
const eliminarCompra = async (req, res) => {
    try {
        const { id } = req.params;
        const usuario_id = req.usuario.id;

        // Verificar que la compra le pertenece al usuario
        const [result] = await pool.query('DELETE FROM compras WHERE id = ? AND usuario_id = ?', [id, usuario_id]);

        if (result.affectedRows === 0) {
            return res.status(404).json({ error: 'Compra no encontrada' });
        }

        res.json({ mensaje: 'Compra eliminada con éxito' });
    } catch (error) {
        res.status(500).json({ error: 'Error al eliminar la compra' });
    }
};

module.exports = {
    obtenerCompras,
    obtenerDetalleCompra,
    crearCompra,
    eliminarCompra
};
