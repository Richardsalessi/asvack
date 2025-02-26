const pool = require('./config/db'); // Conexión a la base de datos
const bcrypt = require('bcrypt'); // Para encriptar la contraseña

const crearUsuarios = async () => {
    try {
        // Definir las contraseñas en texto plano
        const passwordAdmin = 'admin123';
        const passwordTrabajador = 'trabajador123';

        // Encriptar las contraseñas antes de guardarlas en la base de datos
        const hashedPasswordAdmin = await bcrypt.hash(passwordAdmin, 10);
        const hashedPasswordTrabajador = await bcrypt.hash(passwordTrabajador, 10);

        // Insertar usuarios en la base de datos con sus roles
        await pool.query(
            `INSERT INTO usuarios (nombre, email, password, rol, created_at) VALUES 
            ('Administrador', 'admin@example.com', ?, 'admin', NOW()),
            ('Trabajador', 'trabajador@example.com', ?, 'trabajador', NOW())`,
            [hashedPasswordAdmin, hashedPasswordTrabajador]
        );

        console.log('✅ Admin y trabajador creados con éxito con contraseña encriptada.');
    } catch (error) {
        console.error('❌ Error al crear usuarios:', error);
    } finally {
        process.exit(); // Cerrar el proceso una vez terminado
    }
};

// Ejecutar la función
crearUsuarios();
