const pool = require('../config/db');

// Obtener todos los productos
const obtenerProductos = async (req, res) => {
    try {
        const [rows] = await pool.query('SELECT * FROM productos');
        res.json(rows);
    } catch (error) {
        res.status(500).json({ error: 'Error al obtener productos' });
    }
};

// Obtener un producto por ID
const obtenerProductoPorId = async (req, res) => {
    try {
        const { id } = req.params;
        const [rows] = await pool.query('SELECT * FROM productos WHERE id = ?', [id]);

        if (rows.length === 0) {
            return res.status(404).json({ error: 'Producto no encontrado' });
        }

        res.json(rows[0]);
    } catch (error) {
        res.status(500).json({ error: 'Error al obtener el producto' });
    }
};

// Crear un producto con imagen
const crearProducto = async (req, res) => {
    try {
        const { nombre, descripcion, precio, stock, categoria_id } = req.body;
        const archivo = req.file ? `/uploads/${req.file.filename}` : null;

        if (!nombre || !precio || !stock || !categoria_id) {
            return res.status(400).json({ error: 'Todos los campos son obligatorios' });
        }

        await pool.query('INSERT INTO productos (nombre, descripcion, precio, stock, categoria_id, archivo) VALUES (?, ?, ?, ?, ?, ?)', 
            [nombre, descripcion, precio, stock, categoria_id, archivo]);

        res.json({ mensaje: 'Producto agregado con éxito', archivo });
    } catch (error) {
        res.status(500).json({ error: 'Error al agregar producto' });
    }
};

// Editar un producto
const editarProducto = async (req, res) => {
    try {
        const { id } = req.params;
        const { nombre, descripcion, precio, stock, categoria_id } = req.body;

        const [result] = await pool.query(
            'UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, categoria_id = ? WHERE id = ?',
            [nombre, descripcion, precio, stock, categoria_id, id]
        );

        if (result.affectedRows === 0) {
            return res.status(404).json({ error: 'Producto no encontrado' });
        }

        res.json({ mensaje: 'Producto actualizado con éxito' });
    } catch (error) {
        res.status(500).json({ error: 'Error al actualizar producto' });
    }
};

// Eliminar un producto
const eliminarProducto = async (req, res) => {
    try {
        const { id } = req.params;

        const [result] = await pool.query('DELETE FROM productos WHERE id = ?', [id]);

        if (result.affectedRows === 0) {
            return res.status(404).json({ error: 'Producto no encontrado' });
        }

        res.json({ mensaje: 'Producto eliminado con éxito' });
    } catch (error) {
        res.status(500).json({ error: 'Error al eliminar producto' });
    }
};

module.exports = {
    obtenerProductos,
    obtenerProductoPorId,
    crearProducto,
    editarProducto,
    eliminarProducto
};
