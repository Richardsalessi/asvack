const pool = require('../config/db');

// Obtener todos los logs
const obtenerLogs = async (req, res) => {
    try {
        const [logs] = await pool.query('SELECT * FROM logs_acciones ORDER BY created_at DESC');
        res.json({ success: true, logs });
    } catch (error) {
        console.error('Error al obtener logs:', error);
        res.status(500).json({ success: false, error: 'Error al obtener logs' });
    }
};

module.exports = { obtenerLogs };
