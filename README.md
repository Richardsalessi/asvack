# asvack
📌 Configuración de JWT_SECRET en .env
Antes de ejecutar el proyecto, debes asegurarte de tener una clave secreta configurada en el archivo .env.

Si no tienes una, puedes generar una nueva ejecutando este comando en la terminal:
Codigo bash
node -e "console.log(require('crypto').randomBytes(64).toString('hex'))"

📌 Esto generará una nueva clave segura.
Luego, copia la clave generada y agrégala en tu archivo .env:
JWT_SECRET=TU_CLAVE_SECRETA_GENERADA_AQUÍ

🔴 Importante:
Si cambias JWT_SECRET, los tokens generados anteriormente dejarán de ser válidos.
Los usuarios tendrán que volver a iniciar sesión para obtener un nuevo token.

✅ Una vez configurado, puedes iniciar el servidor con:
node server.js