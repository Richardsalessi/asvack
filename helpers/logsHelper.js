const pool = require('../config/db');

const registrarLog = async (usuario_id, accion) => {
    try {
        await pool.query(
            'INSERT INTO logs_acciones (usuario_id, accion, created_at) VALUES (?, ?, NOW())',
            [usuario_id, accion]
        );
        console.log(`✅ Log registrado: ${accion}`);
    } catch (error) {
        console.error('❌ Error al registrar log:', error);
    }
};

module.exports = { registrarLog };
