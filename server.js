require('dotenv').config(); // Carga variables de entorno
const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const pool = require('./config/db'); // Importar la conexión a la base de datos

const app = express();

// Middlewares
app.use(cors());
app.use(bodyParser.json());

// Importar rutas
const productosRoutes = require('./routes/productosRoutes');
const usuariosRoutes = require('./routes/usuariosRoutes');

// Usar rutas
app.use('/api/productos', productosRoutes);
app.use('/api/usuarios', usuariosRoutes);

// Ruta principal
app.get('/', (req, res) => {
    res.send('API de Asvack funcionando 🚀');
});

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
app.listen(PORT, () => {
    console.log(`Servidor corriendo en http://localhost:${PORT}`);
});
