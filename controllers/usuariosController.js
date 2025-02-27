const pool = require('../config/db');
const bcrypt = require('bcryptjs'); // Usar bcryptjs en lugar de bcrypt
const jwt = require('jsonwebtoken');

// Registrar usuario (cliente por defecto)
const registrarUsuario = async (req, res) => {
    try {
        const { nombre, email, telefono, password } = req.body;

        console.log('Intentando registrar usuario:', { nombre, email, telefono });

        // Verificar si el usuario ya existe
        const [usuarioExistente] = await pool.query('SELECT id FROM usuarios WHERE email = ?', [email]);
        if (usuarioExistente.length > 0) {
            return res.status(400).json({ success: false, error: 'El email ya está registrado' });
        }

        // Encriptar la contraseña antes de guardarla
        const hashedPassword = await bcrypt.hash(password, 10);

        // Insertar nuevo usuario como cliente por defecto
        await pool.query(
            'INSERT INTO usuarios (nombre, email, telefono, password, rol, created_at) VALUES (?, ?, ?, ?, "cliente", NOW())',
            [nombre, email, telefono, hashedPassword]
        );

        console.log('✅ Usuario registrado con éxito:', email);
        res.json({ success: true, mensaje: 'Usuario registrado correctamente' });

    } catch (error) {
        console.error('❌ Error al registrar usuario:', error);
        res.status(500).json({ success: false, error: 'Error al registrar usuario' });
    }
};

// Iniciar sesión y generar token JWT
const iniciarSesion = async (req, res) => {
    try {
        const { email, password } = req.body;

        console.log('Intentando iniciar sesión con:', email);

        // Buscar usuario en la base de datos
        const [rows] = await pool.query('SELECT * FROM usuarios WHERE email = ?', [email]);

        if (rows.length === 0) {
            return res.status(401).json({ error: 'Credenciales incorrectas' });
        }

        const usuario = rows[0];

        // Comparar contraseñas
        const match = await bcrypt.compare(password, usuario.password);
        if (!match) {
            return res.status(401).json({ error: 'Credenciales incorrectas' });
        }

        // Generar token JWT con el rol del usuario
        const token = jwt.sign(
            { id: usuario.id, email: usuario.email, telefono: usuario.telefono, rol: usuario.rol }, 
            process.env.JWT_SECRET, 
            { expiresIn: '1h' }
        );

        console.log('✅ Inicio de sesión exitoso:', email);
        res.json({ token });

    } catch (error) {
        console.error('❌ Error en el inicio de sesión:', error);
        res.status(500).json({ error: 'Error en el inicio de sesión' });
    }
};

// Crear usuario con rol (Solo Admin puede crear Admins o Trabajadores)
const crearUsuarioConRol = async (req, res) => {
    try {
        const { nombre, email, telefono, password, rol } = req.body;
        const usuarioAdmin = req.usuario; // Usuario autenticado que hace la petición

        if (usuarioAdmin.rol !== 'admin') {
            return res.status(403).json({ success: false, error: 'No tienes permisos para crear este usuario' });
        }

        if (!['admin', 'trabajador'].includes(rol)) {
            return res.status(400).json({ success: false, error: 'Rol inválido' });
        }

        // Verificar si el email ya está registrado
        const [usuarioExistente] = await pool.query('SELECT id FROM usuarios WHERE email = ?', [email]);
        if (usuarioExistente.length > 0) {
            return res.status(400).json({ success: false, error: 'El email ya está en uso' });
        }

        const hashedPassword = await bcrypt.hash(password, 10);

        // Insertar usuario con el rol especificado
        await pool.query(
            'INSERT INTO usuarios (nombre, email, telefono, password, rol, created_at) VALUES (?, ?, ?, ?, ?, NOW())',
            [nombre, email, telefono, hashedPassword, rol]
        );

        console.log(`✅ Usuario ${rol} creado correctamente:`, email);
        res.json({ success: true, mensaje: `Usuario ${rol} creado correctamente` });

    } catch (error) {
        console.error('❌ Error al crear usuario:', error);
        res.status(500).json({ success: false, error: 'Error al crear usuario' });
    }
};

// Obtener lista de trabajadores (Solo Admin puede verlos)
const obtenerTrabajadores = async (req, res) => {
    try {
        if (req.usuario.rol !== 'admin') {
            return res.status(403).json({ success: false, error: 'No tienes permisos para ver trabajadores' });
        }

        const [trabajadores] = await pool.query(
            'SELECT id, nombre, email, telefono, created_at FROM usuarios WHERE rol = "trabajador"'
        );

        res.json({ success: true, trabajadores });

    } catch (error) {
        console.error('❌ Error al obtener trabajadores:', error);
        res.status(500).json({ success: false, error: 'Error al obtener trabajadores' });
    }
};

// Obtener lista de clientes (Solo Admin puede verlos)
const obtenerClientes = async (req, res) => {
    try {
        if (req.usuario.rol !== 'admin') {
            return res.status(403).json({ success: false, error: 'No tienes permisos para ver clientes' });
        }

        const [clientes] = await pool.query(
            'SELECT id, nombre, email, telefono, created_at FROM usuarios WHERE rol = "cliente"'
        );

        res.json({ success: true, clientes });

    } catch (error) {
        console.error('❌ Error al obtener clientes:', error);
        res.status(500).json({ success: false, error: 'Error al obtener clientes' });
    }
};

// Obtener historial de compras (Cliente solo ve las suyas, Admin ve todas)
const obtenerCompras = async (req, res) => {
    try {
        let compras;

        if (req.usuario.rol === 'admin') {
            // Admin ve todas las compras
            [compras] = await pool.query('SELECT * FROM compras');
        } else {
            // Cliente solo ve sus compras
            [compras] = await pool.query('SELECT * FROM compras WHERE usuario_id = ?', [req.usuario.id]);
        }

        res.json({ success: true, compras });

    } catch (error) {
        console.error('❌ Error al obtener compras:', error);
        res.status(500).json({ success: false, error: 'Error al obtener compras' });
    }
};

// Proteger la ruta de cambio de rol
const cambiarRolUsuario = async (req, res) => {
    return res.status(403).json({ success: false, error: 'No puedes cambiar tu rol manualmente' });
};

module.exports = {
    registrarUsuario,
    iniciarSesion,
    crearUsuarioConRol,
    cambiarRolUsuario,
    obtenerTrabajadores,
    obtenerClientes,
    obtenerCompras
};
