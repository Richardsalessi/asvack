<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Compra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0;
            color: #444;
        }
        .header p {
            font-size: 14px;
            color: #666;
        }
        .content {
            margin-bottom: 20px;
        }
        .content p {
            font-size: 16px;
            margin: 6px 0;
        }
        .content p span {
            font-weight: bold;
        }
        .total {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado del recibo -->
        <div class="header">
            <h1>Recibo de Compra</h1>
            <p>Gracias por su compra en Asvack</p>
        </div>

        <!-- Detalles del cliente -->
        <div class="content">
            <p><span>Nombre del Cliente:</span> {{ $nombre_cliente }}</p>
            <p><span>Email:</span> {{ $email_cliente }}</p>
            <p><span>Teléfono:</span> +57 {{ $telefono }}</p>
        </div>

        <!-- Información del envío -->
        <div class="content">
            <h3>Detalles de Envío:</h3>
            <p><span>Ciudad:</span> {{ $ciudad }}</p>
            <p><span>Barrio:</span> {{ $barrio }}</p>
            <p><span>Dirección:</span> {{ $direccion }}</p>
        </div>

        <!-- Detalles del producto -->
        <div class="content">
            <h3>Detalles de la Compra:</h3>
            <p><span>Producto:</span> {{ $producto->nombre }}</p>
            <p><span>Cantidad:</span> {{ $cantidad }}</p>
            <p><span>Precio Unitario:</span> ${{ number_format($producto->precio, 2, ',', '.') }}</p>
            <p class="total"><span>Total a Pagar:</span> ${{ number_format($total, 2, ',', '.') }}</p>
        </div>

        <!-- Pie de página -->
        <div class="header">
            <p>Fecha de Compra: {{ now()->format('d-m-Y') }}</p>
            <p>¡Gracias por confiar en nosotros!</p>
        </div>
    </div>
</body>
</html>
