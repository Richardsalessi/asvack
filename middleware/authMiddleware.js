const jwt = require('jsonwebtoken');

const verificarToken = (req, res, next) => {
    const token = req.header('Authorization'); // El token se envía en los headers

    if (!token) {
        return res.status(401).json({ error: 'Acceso denegado. No hay token.' });
    }

    try {
        const tokenLimpio = token.replace('Bearer ', ''); // Si viene con "Bearer ", lo eliminamos
        const verificado = jwt.verify(tokenLimpio, process.env.JWT_SECRET); // Verifica el token con la clave secreta
        req.usuario = verificado; // Agrega la info del usuario al request
        next(); // Permite continuar con la petición
    } catch (error) {
        res.status(401).json({ error: 'Token inválido' });
    }
};

module.exports = verificarToken;
