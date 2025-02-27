require('dotenv').config();
const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const path = require('path');
const { app, server } = require('./socket'); // Importamos `app` y `server`
const pool = require('./config/db');

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(express.static(path.join(__dirname, 'public'))); // 🔥 Servir archivos estáticos (Frontend)

// Importar rutas
const productosRoutes = require('./routes/productosRoutes');
const usuariosRoutes = require('./routes/usuariosRoutes');
const categoriasRoutes = require('./routes/categoriasRoutes');
const carritoRoutes = require('./routes/carritoRoutes');
const comprasRoutes = require('./routes/comprasRoutes');
const logsRoutes = require('./routes/logsRoutes'); // 🔥 Nueva ruta para logs

// Usar rutas
app.use('/api/productos', productosRoutes);
app.use('/api/usuarios', usuariosRoutes);
app.use('/api/categorias', categoriasRoutes);
app.use('/api/carrito', carritoRoutes);
app.use('/api/compras', comprasRoutes);
app.use('/api/logs', logsRoutes); // 🔥 Ruta para ver logs

// Ruta principal para servir `index.html`
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'view', 'index.html'));
});

// Ruta para servir cualquier archivo HTML dentro de `public/view/`
app.get('/:page', (req, res) => {
    const page = req.params.page;
    const filePath = path.join(__dirname, 'public', 'view', page);
    res.sendFile(filePath, (err) => {
        if (err) {
            res.status(404).send('Página no encontrada');
        }
    });
});

// Ruta para probar conexión a la base de datos
app.get('/test-db', async (req, res) => {
    try {
        const [rows] = await pool.query('SELECT 1 + 1 AS result');
        res.json({ success: true, message: 'Conexión a la base de datos exitosa', result: rows[0] });
    } catch (error) {
        console.error('❌ Error conectando a la base de datos:', error);
        res.status(500).json({ success: false, error: 'No se pudo conectar a la base de datos' });
    }
});

// Manejar rutas inexistentes
app.use((req, res) => {
    res.status(404).send('Ruta no encontrada');
});

// Iniciar servidor
const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`🚀 Servidor corriendo en http://localhost:${PORT}`);
});
