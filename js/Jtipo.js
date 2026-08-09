
/* Clase de funcionamiento recorrido */
class Principal {
    /* Contructor de l aclase */
    constructor() {
        this.apiUrl = 'api-tipo.php';
        this.dataTable = null; // Variable para almacenar la instancia de DataTable
        this.editar = null; //id de elemento a editar
        this.elementos = []; //Arreglo donde se guardan todos los eleemtos

        this.elements = {
            /* Tabla del recorrido */
            tabla: document.getElementById('tabla-almacen'),

            //form y botones de modal
            btnAgregar: document.getElementById('btnAgregar'),
            btnCerrarModal: document.getElementById('btnCerrarModal'),
            btnCancelar: document.getElementById('btnCancelar'),
            form: document.getElementById('formPrincipal'),

            //Modal eliminar 
            nombreEstacionEliminar: document.getElementById('nombreEstacionEliminar'),
            btnConfirmarEliminar: document.getElementById('btnConfirmarEliminar'),
            btnCancelarEliminar: document.getElementById('btnCancelarEliminar'),

            //Elemento de busqueda 
            checkbox: document.getElementById('tipo_existente_checkbox'),

            //Elementos de espera o rsultado
            loading: document.getElementById('loading'),
            sinResultados: document.getElementById('sinResultados'),
            toastContainer: document.getElementById('toastContainer'),
            centro_dato: document.getElementById('centro_dato'),
        };

        /* Llamada al metodo INIT */
        this.init();
    }

    /* Metodo para inicializar (se ejecuta al inicio) */
    init() {
        this.cargarTabla();
        this.attachEventListeners();
    }

    //Metodo de eventos 
    attachEventListeners() {
        // Modal events - usar event delegation para asegurar que siempre funcionen
        document.addEventListener('click', (e) => {
            //evento para el btn agregar recorrido
            if (e.target.id === 'btnAgregar' || e.target.closest('#btnAgregar')) { e.preventDefault(); this.abrirModal(); }

            //evento para el btn cerrar modal
            if (e.target.id === 'btnCerrarModal' || e.target.closest('#btnCerrarModal')) { e.preventDefault(); this.cerrarModal(); }

            //evento para el btn cancelar
            if (e.target.id === 'btnCancelar') { e.preventDefault(); this.cerrarModal(); }

        });

        //evento para el btn editar
        $(document).on('click', '.btn-editar', (e) => { const id = $(e.currentTarget).data('id'); this.MEditar(id); });

        //evento para el btn eliminar
        $(document).on('click', '.btn-eliminar', (e) => { const id = $(e.currentTarget).data('id'); this.MEliminar(id); });

        // Modal confirmación
        this.elements.btnCancelarEliminar.addEventListener('click', () => this.cerrarModalConfirmacion());
        this.elements.btnConfirmarEliminar.addEventListener('click', () => this.confirmarEliminar());




        this.elements.checkbox.addEventListener('change', function () {
            const tipoExistenteDiv = document.getElementById('tipo_existente_div');
            const nombreTipoDiv = document.getElementById('nombre_tipo_div');
            if (this.checked) {
                tipoExistenteDiv.classList.remove('hidden'); // Mostrar "Tipo ya existente"
                nombreTipoDiv.classList.add('hidden'); // Ocultar "Nombre del tipo"
                document.getElementById('ya_existe').value = '';

                document.getElementById('nombreNuevo_tipo').removeAttribute('required');
                document.getElementById('ya_existe').setAttribute('required', true);


            } else {
                tipoExistenteDiv.classList.add('hidden'); // Ocultar "Tipo ya existente"
                nombreTipoDiv.classList.remove('hidden'); // Mostrar "Nombre del tipo"
                document.getElementById('nombreNuevo_tipo').value = '';

                document.getElementById('ya_existe').removeAttribute('required');
                document.getElementById('nombreNuevo_tipo').setAttribute('required', true);
            }
        });


        // Form submit - mantener el event listener directo
        this.elements.form.addEventListener('submit', (e) => { e.preventDefault(); this.MGuardar(e); });
    }

    // Cargar los datos de la tabla desde la API
    async cargarTabla() {
        try {
            // Mostrar indicador de carga y ocultar otros elementos
            this.elements.loading.classList.remove('hidden');
            this.elements.sinResultados.classList.add('hidden');
            this.elements.centro_dato.classList.add('hidden');

            // Realizar petición a la API usando el ID actual
            const response = await fetch(`${this.apiUrl}`);
            const result = await response.json();

            // Ocultar el indicador de carga
            this.elements.loading.classList.add('hidden');

            // Si se encontraron datos
            if (result.success && result.data.length > 0) {
                // Mostrar el contenedor de datos y renderizar tabla
                this.elements.centro_dato.classList.remove('hidden');
                this.renderTabla(result.data);
                this.renderSelect(result.data);
                this.elementos = result.data; // Guardar datos en la propiedad
                //this.mostrarToast(result.message, 'info');
            } else {
                // Mostrar mensaje de "sin resultados"
                this.elements.sinResultados.classList.remove('hidden');
                this.mostrarToast(result.message || 'No se encontraron registros', 'info');
            }
        } catch (error) {
            // En caso de error, ocultar carga, mostrar mensaje y detalles del error
            this.elements.loading.classList.add('hidden');
            this.elements.sinResultados.classList.remove('hidden');
            this.elements.centro_dato.classList.remove('hidden');

            this.mostrarToast('Error al cargar datos: ' + error.message, 'error');
        }
    }

    /* Metodo para Renderizar el contenido de la tabla  */
    renderTabla(data) {
        // Destruir DataTable existente si existe
        if (this.dataTable) {
            this.dataTable.destroy();
            this.dataTable = null;
        }

        const tbody = this.elements.tabla.querySelector('tbody');
        tbody.innerHTML = data.map((fila, index) => `
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${index + 1}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fila.tipo}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fila.nombre}</td>
                        
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2 justify-center items-center">

                                <!-- Botón Editar -->
                                <button data-id="${fila.id_articulo}"
                                  class="btn-editar w-8 h-8 bg-neutral-300 rounded-md flex items-center justify-center hover:bg-blue-400 hover:scale-105 transition-transform duration-300">
                                  <img src="../../svg/Editar.svg" class="w-7 h-7">
                                </button>

                                
                                <!-- Botón Eliminar -->
                                <button data-id="${fila.id_articulo}"
                                    class="btn-eliminar w-8 h-8 bg-neutral-300 rounded-md flex items-center justify-center hover:bg-red-400 hover:scale-105 transition-transform duration-300">
                                    <img src="../../svg/Eliminar.svg" class="w-7 h-7">
                                </button>

                            </div>
                        </td >

                    </tr >
    `).join('');

        // Inicializar DataTable solo si está disponible y no existe ya
        if (typeof DataTable !== 'undefined') {
            try {
                this.dataTable = new DataTable('#tabla-almacen', {
                    language: {
                        url: '../../js/es-MX.json'
                    },
                    scrollX: true,
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, "Todos"]
                    ],
                    order: [
                        [0, 'asc']
                    ], // Ordenar por número
                    columnDefs: [{
                        orderable: false,
                        targets: -1
                    } // Deshabilitar ordenamiento en columna de acciones
                    ]
                });
            } catch (error) {
                console.error('Error al inicializar DataTable:', error);
            }
        }
    }

    /* MEtodo para renderizar las opciones del select Ya existe */
    renderSelect(data) {
        const select = document.getElementById('ya_existe');
        select.innerHTML = '<option selected value="">Mostrar todas</option>';

        const tiposUnicos = new Set();

        data.forEach(item => {
            const tipo = item.tipo.trim().toLowerCase();

            if (!tiposUnicos.has(tipo)) {
                tiposUnicos.add(tipo);

                const option = document.createElement('option');
                option.value = tipo;
                option.textContent = tipo.charAt(0).toUpperCase() + tipo.slice(1);
                select.appendChild(option);
            }
        });

    }





    // Insertar o editar un elemento
    async MGuardar(e) {
        e.preventDefault(); // Evita el envío por defecto del formulario

        const btnGuardar = document.getElementById('btnGuardar');
        if (btnGuardar.disabled) return; // Si el botón está desactivado, no hace nada

        try {
            // Cambiar texto del botón a "Guardando..." y deshabilitarlo
            const btnText = btnGuardar.querySelector('.btn-text');
            const originalText = btnText.textContent;
            btnText.textContent = 'Guardando...';
            btnGuardar.disabled = true;

            // Obtener y estructurar los datos del formulario
            const datos = {
                existenteTipo: document.getElementById('ya_existe').value.trim(),
                nuevoTipo: document.getElementById('nombreNuevo_tipo').value.trim(),
                checkbox: document.getElementById('tipo_existente_checkbox').checked,
                articulo: document.getElementById('articuloNuevo').value.trim(),

            };


            // Determinar si es una operación de inserción o edición
            let method = 'POST';
            if (this.editar) {
                method = 'PUT';           // Si se está editando, usar método PUT
                datos.id = this.editar;   // Agregar el ID del registro a modificar
            }

            // Enviar los datos al servidor con fetch
            const response = await fetch(`${this.apiUrl}`, {
                method: method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datos)
            });

            // Verificar si la respuesta fue exitosa
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Error HTTP ${response.status}: ${errorText} `);
            }

            const result = await response.json();

            // Mostrar mensaje según resultado del servidor
            if (result.success) {
                this.mostrarToast(result.message || "Registro guardado correctamente", "success");
                this.cerrarModal();
                setTimeout(() => this.cargarTabla(), 300); // Recargar la tabla
            } else {
                this.mostrarToast(result.error || result.message || "Error al guardar el registro", "error");
                this.cerrarModal();
            }

        } catch (error) {
            // Mostrar error si la solicitud falla
            this.mostrarToast(`Error jnnkjk: ${error.message} `, "error");
            this.cerrarModal();
        } finally {
            // Restaurar el estado del botón al finalizar
            const btnText = btnGuardar.querySelector('.btn-text');
            if (btnText && btnGuardar) {
                btnText.textContent = 'Guardar Registro';
                btnGuardar.disabled = false;
                this.editar = null; // Reiniciar el modo edición
                this.cerrarModal();
            }
        }
    }

    //Obtener elementos a editar 
    async MEditar(id) {
        try {
            // Realizar petición a la API usando el ID actual
            const response = await fetch(`${this.apiUrl}?id2=${id}`);
            const data = await response.json();


            // Verefica el resultado delservidor
            if (data.success && data.data.length > 0) {
                this.editar = id;
                this.abrirModal(data.data[0]);

            } else {
                this.mostrarToast('No se encontro el registro del recorrido ', 'error');
            }

        } catch (error) {
            this.mostrarToast(' Error al cargar datos de la estación al editar', 'error');
        }
    }


    //cargar elementos al modal y abrir
    abrirModal(datos = null) {



        // Limpiar estado anterior
        this.elements.form.reset();

        if (datos) {
            // Obtener y estructurar los datos del formulario  EDITAR 
            document.getElementById('tituloModal').textContent = 'Editar elemento';
            document.getElementById('Id').value = datos.id_articulo;
            document.getElementById('ya_existe').value = datos.tipo;
            document.getElementById('articuloNuevo').value = datos.nombre;
            document.querySelector('#btnGuardar .btn-text').textContent = 'Actualizar Registro';

            document.getElementById('tipo_existente_div').classList.remove('hidden');
            document.getElementById('nombre_tipo_div').classList.add('hidden');
            document.getElementById('checkbox_div').classList.add('hidden');
            document.getElementById('nombreNuevo_tipo').removeAttribute('required');

            document.getElementById('ya_existe').setAttribute('required', true);

        } else {
            // Modo creación
            document.getElementById('tituloModal').textContent = 'Nuevo elemento';
            document.querySelector('#btnGuardar .btn-text').textContent = 'Guardar Registro';

            document.getElementById('checkbox_div').classList.remove('hidden');
            document.getElementById('tipo_existente_div').classList.add('hidden');
            document.getElementById('nombre_tipo_div').classList.remove('hidden');
            document.getElementById('nombreNuevo_tipo').setAttribute('required', true);

            document.getElementById('ya_existe').removeAttribute('required');
        }



        // Asegurar que el botón esté habilitado
        const btnGuardar = document.getElementById('btnGuardar');
        btnGuardar.disabled = false;

        // Abrir modal
        try {
            $('#modalPrincipal').modal();
            console.log('Modal abierto correctamente');
        } catch (error) {
            console.error('Error abriendo modal:', error);
        }
    }

    //Cerrar modal
    cerrarModal() {
        try {
            $.modal.close();
            this.editar = null;
        } catch (error) {
            console.log('Error cerrando modal:', error);
        }

        // Limpiar formulario
        this.elements.form.reset();

        // Limpiar campos hidden
        document.getElementById('Id').value = '';

        // Restaurar texto del botón
        const btnText = document.querySelector('#btnGuardar .btn-text');
        if (btnText) {
            btnText.textContent = 'Guardar Registro';
        }

        // Habilitar botón si estaba deshabilitado
        const btnGuardar = document.getElementById('btnGuardar');
        if (btnGuardar) {
            btnGuardar.disabled = false;
        }
    }

    /* Eliminar */
    MEliminar(id) {
        const elementos = this.elementos.find(e => e.id_articulo == id);
        if (!elementos) {
            this.mostrarToast('No se encontró al registro a eliminar en el array', 'error');
            return;
        }

        this.editar = id;
        this.elements.nombreEstacionEliminar.textContent = elementos.tipo + " " + elementos.nombre || 'Elemento sin nombre';
        $('#modalConfirmacion').modal();
    }

    //Confirmar y eliminar un registro seleccionado
    async confirmarEliminar() {
        // Si no hay un ID de edición activo, no hacer nada
        if (!this.editar) return;

        try {
            // Enviar solicitud DELETE a la API para eliminar el registro
            const response = await fetch(this.apiUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: this.editar // ID del elemento a eliminar
                })
            });

            const data = await response.json();

            // Si la eliminación fue exitosa
            if (data.success) {
                this.mostrarToast('Elemento eliminado exitosamente', 'success');
                await this.cargarTabla(); // Recargar tabla para reflejar cambios
                $.modal.close(); // Cerrar modal (usa jQuery modal)
            } else {
                $.modal.close();
                if (data.error && data.error.includes("23000")) {
                    this.mostrarToast('Error al eliminar Tipo: Hay elementos registrados en el almacen con este tipo', 'error');
                    setTimeout(() => this.mostrarToast('Para eliminar esta tipo, primero elimine los elementos registrados en el almacen', 'info'), 1000);

                } else {
                    this.mostrarToast('Error al eliminar estación', 'error');
                }
            }
        } catch (error) {
            // Si ocurre un error en la solicitud
            this.mostrarToast('Error de conexión con la API...', 'error');
        } finally {
            // Reiniciar el modo edición
            this.editar = null;
        }
    }

    cerrarModalConfirmacion() {
        $.modal.close();
        this.editar = null;
    }


    /* Toast */
    mostrarToast(mensaje, tipo = 'success') {
        const toast = document.createElement('div');
        const colores = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };

        const iconos = {
            success: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>`,
            error: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>`,
            warning: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>`,
            info: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>`
        };

        toast.className = `${colores[tipo]} text-white px-6 py-3 rounded-xl shadow-lg transform transition-all duration-300 translate-x-full flex items-center space-x-2`;
        toast.innerHTML = `
                ${iconos[tipo]}
                <span>${mensaje}</span>
            `;

        this.elements.toastContainer.appendChild(toast);

        setTimeout(() => toast.classList.remove('translate-x-full'), 100);
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    /* // Método de debug para verificar el estado
    debugEstado() {
        console.log('Estado actual:', {
            id: this.id,
            dataTable: this.dataTable ? 'Inicializado' : 'No inicializado',
            modalVisible: $('#modalRecorrido').is(':visible'),
            btnAgregarDisponible: document.getElementById('btnAgregarRecorrido') ? 'Sí' : 'No'
        });
    } */

}

// Inicializar la aplicación
let principal;
document.addEventListener('DOMContentLoaded', () => {
    principal = new Principal();
});
