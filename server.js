require('dotenv').config();
const cors = require('cors');
const bodyParser = require('body-parser');
const { app, server } = require('./socket'); // Ahora importamos `app` y `server`
const pool = require('./config/db');

// Middleware
app.use(cors());
app.use(bodyParser.json());

// Importar rutas
const productosRoutes = require('./routes/productosRoutes');
const usuariosRoutes = require('./routes/usuariosRoutes');
const categoriasRoutes = require('./routes/categoriasRoutes');
const carritoRoutes = require('./routes/carritoRoutes');
const comprasRoutes = require('./routes/comprasRoutes');

// Usar rutas
app.use('/api/productos', productosRoutes);
app.use('/api/usuarios', usuariosRoutes);
app.use('/api/categorias', categoriasRoutes);
app.use('/api/carrito', carritoRoutes);
app.use('/api/compras', comprasRoutes);

// Ruta para probar conexión a la base de datos
app.get('/test-db', async (req, res) => {
    try {
        const [rows] = await pool.query('SELECT 1 + 1 AS result');
        res.json({ success: true, message: 'Conexión a la base de datos exitosa', result: rows[0] });
    } catch (error) {
        console.error('Error conectando a la base de datos:', error);
        res.status(500).json({ success: false, error: 'No se pudo conectar a la base de datos' });
    }
});

// Iniciar servidor
const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`🚀 Servidor corriendo en http://localhost:${PORT}`);
});
