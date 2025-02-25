const pool = require('../config/db');
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');

const registrarUsuario = async (req, res) => {
    try {
        const { nombre, email, password } = req.body;

        console.log('Intentando registrar usuario:', { nombre, email });

        // Hashear la contraseña
        const hashedPassword = await bcrypt.hash(password, 10);

        // Insertar en la base de datos
        await pool.query('INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)', [nombre, email, hashedPassword]);

        console.log('Usuario registrado con éxito:', email);
        res.json({ mensaje: 'Usuario registrado con éxito' });
    } catch (error) {
        console.error('Error al registrar usuario:', error);
        res.status(500).json({ error: 'Error al registrar usuario', detalle: error.message });
    }
};

const iniciarSesion = async (req, res) => {
    try {
        const { email, password } = req.body;

        console.log('Intentando iniciar sesión con:', email);

        // Buscar usuario en la base de datos
        const [rows] = await pool.query('SELECT * FROM usuarios WHERE email = ?', [email]);

        console.log('Resultado de la consulta:', rows);

        // Verificar si el usuario existe
        if (!rows || rows.length === 0) {
            console.log('Usuario no encontrado:', email);
            return res.status(401).json({ error: 'Credenciales incorrectas' });
        }

        const usuario = rows[0];

        // Comparar contraseñas
        const match = await bcrypt.compare(password, usuario.password);
        console.log('Comparación de contraseñas:', match);

        if (!match) {
            console.log('Contraseña incorrecta para:', email);
            return res.status(401).json({ error: 'Credenciales incorrectas' });
        }

        // Generar token JWT
        const token = jwt.sign({ id: usuario.id, email: usuario.email }, process.env.JWT_SECRET, { expiresIn: '1h' });

        console.log('Inicio de sesión exitoso:', email);
        res.json({ token });

    } catch (error) {
        console.error('Error en el inicio de sesión:', error);
        res.status(500).json({ error: 'Error en el inicio de sesión', detalle: error.message });
    }
};

module.exports = { registrarUsuario, iniciarSesion };
