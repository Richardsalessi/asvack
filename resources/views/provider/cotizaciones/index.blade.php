@extends('layouts.app')

@section('content')
<div class="container mx-auto p-8 bg-white dark:bg-gray-800 shadow rounded-lg">
    <h1 class="text-3xl font-semibold text-gray-800 dark:text-white mb-6">Mensajería de Cotizaciones</h1>
    <div class="flex">
        <!-- Panel de Lista de Cotizaciones -->
        <div class="w-1/4 bg-gray-100 dark:bg-gray-900 p-4 rounded-lg mr-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Cotizaciones</h2>
            <div class="mb-4">
                <h3 class="font-medium text-gray-700 dark:text-gray-300">Pendientes</h3>
                <div id="pendientes-container">
                    @foreach($cotizacionesPendientes as $cotizacion)
                        <div class="p-2 border-b border-gray-300 dark:border-gray-700 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-800 text-gray-800 dark:text-gray-200"
                             onclick="cargarChat({{ $cotizacion->id }})" id="cotizacion-{{ $cotizacion->id }}">
                            {{ $cotizacion->cliente->name }}
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mb-4">
                <h3 class="font-medium text-gray-700 dark:text-gray-300">En Proceso</h3>
                <div id="en-proceso-container">
                    @foreach($cotizacionesEnProceso as $cotizacion)
                        <div class="p-2 border-b border-gray-300 dark:border-gray-700 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-800 text-gray-800 dark:text-gray-200"
                             onclick="cargarChat({{ $cotizacion->id }})" id="cotizacion-{{ $cotizacion->id }}">
                            {{ $cotizacion->cliente->name }}
                        </div>
                    @endforeach
                </div>
            </div>
            <div>
                <h3 class="font-medium text-gray-700 dark:text-gray-300">Finalizadas</h3>
                <div id="finalizadas-container">
                    @foreach($cotizacionesFinalizadas as $cotizacion)
                        <div class="p-2 border-b border-gray-300 dark:border-gray-700 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-800 text-gray-800 dark:text-gray-200"
                             onclick="cargarChat({{ $cotizacion->id }})" id="cotizacion-{{ $cotizacion->id }}">
                            {{ $cotizacion->cliente->name }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Panel de Chat de Cotización -->
        <div class="flex-grow bg-gray-200 dark:bg-gray-700 p-6 rounded-lg" id="chat-panel">
            <div class="flex items-center justify-center h-full" id="select-chat-message">
                <p class="text-gray-600 dark:text-gray-300">Selecciona una cotización a la izquierda para comenzar a chatear.</p>
            </div>
            <div id="chat-content" class="hidden">
                <div class="flex flex-col justify-between mb-4">
                    <h2 class="text-2xl font-semibold text-gray-800 dark:text-white" id="chat-title"></h2>
                    <div id="chat-detail" class="mt-2 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
                        <p class="font-semibold text-gray-800 dark:text-gray-200">Detalle de la Cotización:</p>
                        <p class="text-gray-600 dark:text-gray-300" id="detalle-cotizacion"></p>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg mb-4 overflow-y-auto" id="messages-container" style="height: 600px; display: flex; flex-direction: column;"></div>
                <div class="flex justify-between mb-4">
                    <button id="en-proceso-btn" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-700 hidden" onclick="marcarEnProceso()">
                        Marcar como En Proceso
                    </button>
                    <button id="finalizado-btn" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-700 hidden" onclick="marcarFinalizado()">
                        Marcar como Finalizado
                    </button>
                </div>
                <form id="responder-form">
                    @csrf
                    <div class="flex">
                        <input type="text" name="mensaje" placeholder="Escribe tu mensaje..." class="flex-grow p-4 border rounded dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600" id="mensaje">
                        <button type="submit" class="ml-4 bg-blue-500 text-white px-5 py-3 rounded hover:bg-blue-700">
                            Enviar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js"></script>

<script>
    moment.locale('es'); // Configurar el idioma de moment.js a español

    let currentCotizacionId = null;

    function cargarChat(cotizacionId) {
        currentCotizacionId = cotizacionId;

        // Ocultar el mensaje de selección y mostrar el panel de chat
        document.getElementById('select-chat-message').classList.add('hidden');
        document.getElementById('chat-content').classList.remove('hidden');

        // Hacer la petición AJAX para obtener los mensajes de la cotización
        actualizarMensajes();
    }

    function actualizarMensajes() {
        if (!currentCotizacionId) return;

        fetch(`/provider/cotizaciones/${currentCotizacionId}`)
            .then(response => response.json())
            .then(data => {
                // Actualizar el título del chat con el nombre del cliente
                document.getElementById('chat-title').innerText = `Cotización de ${data.cliente} - ${moment(data.fecha).format('DD-MM-YYYY HH:mm')}`;

                // Actualizar el detalle de la cotización
                document.getElementById('detalle-cotizacion').innerText = data.detalle;

                // Mostrar u ocultar botones según el estado
                document.getElementById('en-proceso-btn').style.display = data.estado === 'pendiente' ? 'block' : 'none';
                document.getElementById('finalizado-btn').style.display = data.estado === 'en_proceso' ? 'block' : 'none';

                // Limpiar el contenedor de mensajes y agregar los nuevos mensajes
                const messagesContainer = document.getElementById('messages-container');
                messagesContainer.innerHTML = '';

                // Agregar los mensajes
                data.mensajes.reverse().forEach(mensaje => {
                    const messageDiv = document.createElement('div');
                    const alignClass = mensaje.es_proveedor ? 'text-right' : 'text-left';
                    const bgColorClass = mensaje.es_proveedor ? 'bg-yellow-500' : 'bg-blue-500';
                    const senderName = mensaje.nombre;

                    messageDiv.classList.add(alignClass, 'mb-4');
                    messageDiv.innerHTML = `
                        <div class="${bgColorClass} text-white p-4 rounded-lg inline-block max-w-xs">
                            <p class="font-semibold">${senderName}</p>
                            ${mensaje.contenido}
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 message-time" data-time="${mensaje.created_at}">
                            ${moment(mensaje.created_at).fromNow() === 'hace unos segundos' ? 'Justo ahora' : moment(mensaje.created_at).fromNow()}
                        </p>
                    `;
                    messagesContainer.appendChild(messageDiv);
                });

                actualizarTiempos();
            });
    }

    document.getElementById('responder-form').onsubmit = function (event) {
        event.preventDefault();
        const mensaje = document.getElementById('mensaje').value;

        fetch(`/provider/cotizaciones/${currentCotizacionId}/responder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ mensaje })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const messagesContainer = document.getElementById('messages-container');
                const newMessageDiv = document.createElement('div');
                newMessageDiv.classList.add('text-right', 'mb-4');
                newMessageDiv.innerHTML = `
                    <div class="bg-yellow-500 text-white p-4 rounded-lg inline-block max-w-xs">
                        <p class="font-semibold">${data.mensaje.nombre}</p>
                        ${data.mensaje.contenido}
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 message-time" data-time="${data.mensaje.created_at}">
                        Justo ahora
                    </p>
                `;
                messagesContainer.insertBefore(newMessageDiv, messagesContainer.firstChild);
                document.getElementById('mensaje').value = '';
            }
        });
    };

    function marcarEnProceso() {
        fetch(`/provider/cotizaciones/${currentCotizacionId}/en-proceso`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                moverCotizacion(currentCotizacionId, 'en-proceso-container');
                actualizarMensajes();
            }
        });
    }

    function marcarFinalizado() {
        fetch(`/provider/cotizaciones/${currentCotizacionId}/finalizado`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                moverCotizacion(currentCotizacionId, 'finalizadas-container');
                actualizarMensajes();
            }
        });
    }

    function moverCotizacion(cotizacionId, nuevoContenedorId) {
        const cotizacionDiv = document.getElementById(`cotizacion-${cotizacionId}`);
        if (cotizacionDiv) {
            document.getElementById(nuevoContenedorId).appendChild(cotizacionDiv);
        }
    }

    // Refrescar el chat cada 5 segundos para mantenerlo actualizado
    setInterval(actualizarMensajes, 5000);

    // Actualizar tiempos de los mensajes cada minuto
    setInterval(actualizarTiempos, 60000);

    function actualizarTiempos() {
        document.querySelectorAll('.message-time').forEach(element => {
            const time = element.getAttribute('data-time');
            const formattedTime = moment(time).fromNow();
            element.innerText = formattedTime === 'hace unos segundos' ? 'Justo ahora' : formattedTime;
        });
    }
</script>
@endsection
