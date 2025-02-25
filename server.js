const express = require('express');
const db = require('./config/db');

const app = express();
const PORT = 3000;

app.get('/test-db', async (req, res) => {
    try {
        const [rows] = await db.query('SELECT 1 + 1 AS result');
        res.json({ success: true, result: rows[0].result });
    } catch (error) {
        console.error('Error conectando con MySQL:', error);
        res.status(500).json({ success: false, message: 'Error en la base de datos' });
    }
});

app.listen(PORT, () => console.log(`Servidor corriendo en http://localhost:${PORT}`));
