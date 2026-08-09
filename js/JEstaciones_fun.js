class SistemaEstaciones {
    constructor() {
        this.apiUrl = 'estaciones/api.php'; // URL de la API
        this.userLevel = nivelphp; //Nivel del usuario al iniciar secion
        this.estacionesOriginales = [];
        this.estacionesFiltradas = [];
        this.filtros = {
            tipo: '',
            estado: '',
            busqueda: ''
        };
        this.estacionEditando = null;

        this.initializeElements();
        this.attachEventListeners();
        this.cargarEstaciones();

    }

    initializeElements() {
        this.elements = {
            // Filtros
            filtroTipo: document.getElementById('filtroTipo'),
            filtroEstado: document.getElementById('filtroEstado'),
            barraBusqueda: document.getElementById('barraBusqueda'),
            limpiarBusqueda: document.getElementById('limpiarBusqueda'),
            limpiarFiltros: document.getElementById('limpiarFiltros'),
            limpiarTodo: document.getElementById('limpiarTodo'),

            // Contenido
            gridEstaciones: document.getElementById('gridEstaciones'),
            loading: document.getElementById('loading'),
            sinResultados: document.getElementById('sinResultados'),
            filtrosActivos: document.getElementById('filtrosActivos'),
            resultadosBusqueda: document.getElementById('resultadosBusqueda'),
            textoResultados: document.getElementById('textoResultados'),

            // Estadísticas
            totalEstaciones: document.getElementById('totalEstaciones'),
            estacionesActivas: document.getElementById('estacionesActivas'),
            estacionesInactivas: document.getElementById('estacionesInactivas'),
            estacionesMantenimiento: document.getElementById('estacionesMantenimiento'),

            // Modal principal
            btnAgregarEstacion: document.getElementById('btnAgregarEstacion'),
            btnCerrarModal: document.getElementById('btnCerrarModal'),
            btnCancelar: document.getElementById('btnCancelar'),
            tituloModal: document.getElementById('tituloModal'),
            btnGuardar: document.getElementById('btnGuardar'),

            // Formulario
            formEstacion: document.getElementById('formEstacion'),
            estacionId: document.getElementById('estacionId'),
            nombreEstacion: document.getElementById('nombreEstacion'),
            descripcionEstacion: document.getElementById('descripcionEstacion'),
            tipoEstacion: document.getElementById('tipoEstacion'),
            imgLogoEstacion: document.getElementById('imgLogoEstacion'),
            //equiposEstacion: document.getElementById('equiposEstacion'),

            // Modal confirmación
            nombreEstacionEliminar: document.getElementById('nombreEstacionEliminar'),
            btnConfirmarEliminar: document.getElementById('btnConfirmarEliminar'),
            btnCancelarEliminar: document.getElementById('btnCancelarEliminar'),

            // Otros
            toastContainer: document.getElementById('toastContainer')
        };
    }

    attachEventListeners() {
        // Filtros
        this.elements.filtroTipo.addEventListener('change', () => this.aplicarFiltros());
        this.elements.filtroEstado.addEventListener('change', () => this.aplicarFiltros());
        this.elements.barraBusqueda.addEventListener('input', () => this.aplicarFiltros());
        this.elements.limpiarFiltros.addEventListener('click', () => this.limpiarFiltros());
        this.elements.limpiarBusqueda.addEventListener('click', () => this.limpiarBusqueda());

        // Modal principal
        this.elements.btnAgregarEstacion.addEventListener('click', () => this.abrirModalAgregar());
        this.elements.btnCerrarModal.addEventListener('click', () => this.cerrarModal());
        this.elements.btnCancelar.addEventListener('click', () => this.cerrarModal());
        this.elements.formEstacion.addEventListener('submit', (e) => this.guardarEstacion(e));

        // Modal confirmación
        this.elements.btnCancelarEliminar.addEventListener('click', () => this.cerrarModalConfirmacion());
        this.elements.btnConfirmarEliminar.addEventListener('click', () => this.confirmarEliminar());

        // Botón limpiar todo
        if (this.elements.limpiarTodo) {
            this.elements.limpiarTodo.addEventListener('click', () => this.limpiarTodo());
        }

        // Mostrar/ocultar botón limpiar búsqueda
        this.elements.barraBusqueda.addEventListener('input', (e) => {
            if (e.target.value.length > 0) {
                this.elements.limpiarBusqueda.classList.remove('hidden');
            } else {
                this.elements.limpiarBusqueda.classList.add('hidden');
            }
        });

        // Event delegation para botones dinámicos
        $(document).on('click', '.btn-editar', (e) => {
            const id = $(e.currentTarget).data('id');
            this.editarEstacion(id);
        });

        $(document).on('click', '.btn-eliminar', (e) => {
            const id = $(e.currentTarget).data('id');
            this.eliminarEstacion(id);
        });

        // Event delegation para botones de remover filtros individuales
        $(document).on('click', '.btn-remover-filtro', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const tipo = $(e.currentTarget).data('tipo');
            this.removerFiltro(tipo);
        });
    }

    async cargarEstaciones() {
        try {

            this.elements.loading.classList.remove('hidden');
            this.elements.gridEstaciones.innerHTML = '';

            // LLAMADA A API.PHP - GET para obtener todas las estaciones
            const response = await fetch(this.apiUrl);
            const data = await response.json();

            if (data.success) {
                this.estacionesOriginales = data.data.map(estacion => ({
                    ...estacion,
                    nombre_lower: estacion.nombre.toLowerCase(),
                    descripcion_lower: estacion.descripcion.toLowerCase()
                }));

                this.renderizarEstaciones();
                this.actualizarEstadisticas();
                this.aplicarFiltros();
            } else {
                this.mostrarToast('Error al cargar estaciones', 'error');
                // Mostrar datos de ejemplo si hay error
                this.mostrarDatosEjemplo();
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarToast('Sin elementos', 'warning');
        } finally {
            this.elements.loading.classList.add('hidden');
        }
    }

    renderizarEstaciones() {
        this.elements.gridEstaciones.innerHTML = '';

        this.estacionesOriginales.forEach((estacion, index) => {
            const elemento = this.crearElementoEstacion(estacion);
            elemento.style.animationDelay = `${index * 0.1}s`;
            this.elements.gridEstaciones.appendChild(elemento);
        });
    }

    crearElementoEstacion(estacion) {
        const div = document.createElement('div');

        //alert(estacion.nombre + " - " + estacion.tipo + " - " + estacion.estado + " - " + estacion.imgLogo); // Log de depuración para verificar datos


        // Determinar el tamaño y color según el tipo
        let clasesTamaño = 'p-6';
        let clasesColor = 'from-indigo-500 to-indigo-700';

        if (estacion.tipo === 'Principal') {
            clasesColor = 'from-blue-600 to-blue-800 text-white';
        } else if (estacion.tipo === 'Secundaria') {
            clasesColor = 'from-sky-200 to-sky-400 text-black';
        } else if (estacion.tipo === 'Emergencia') {
            clasesColor = 'from-red-500 to-red-700 text-white';
        } else if (estacion.tipo === 'Comunicaciones') {
            clasesColor = 'from-amber-500 to-amber-700 text-white';
        } else if (estacion.tipo === 'Laboratorio') {
            clasesColor = 'from-purple-500 to-purple-700 text-white';
        }

        if (estacion.estado === 'Inactiva') {
            clasesColor = 'from-gray-400 to-gray-600';
            div.style.opacity = '0.75';
        }

        div.className = `estacion-card bg-gradient-to-br ${clasesColor} rounded-2xl shadow-xl ${clasesTamaño} transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl cursor-pointer animate-fade-in`;
        div.dataset.id = estacion.id;

        const estadoColor = {
            'Activa': 'bg-green-400',
            'Inactiva': 'bg-red-400',
            'Mantenimiento': 'bg-yellow-300'
        };

        const estadoAnimation = estacion.estado === 'Mantenimiento' ? 'animate-pulse' : '';

        div.innerHTML = `
                <div class="flex flex-col h-full">
                    <div class="flex justify-between items-start mb-4">
                        <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3 flex-grow mr-3">
                            <h3 class="text-lg font-bold mb-1">${estacion.nombre}</h3>
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 ${estadoColor[estacion.estado]} rounded-full ${estadoAnimation}"></div>
                                <span class="text-xs">${estacion.estado === 'Activa' && estacion.tipo === 'Principal' ? 'Operativa' : estacion.estado}</span>
                            </div>
                        </div>
                        <div class="flex space-x-1">



                        ${this.userLevel == 'USUARIO'? '' : ` 
                            <button class="btn-editar bg-white/20 hover:bg-white/30 p-2 rounded-lg transition-colors" data-id="${estacion.id}" title="Editar estación">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button class="btn-eliminar bg-red-500/20 hover:bg-red-500/30 p-2 rounded-lg transition-colors" data-id="${estacion.id}" title="Eliminar estación">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        `} 
                              

                        </div>
                    </div>
                    
                    <div class="flex-grow">
                        <p class="text-sm leading-relaxed opacity-80 mb-4">
                            ${estacion.descripcion}
                        </p>
                        
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <div class="bg-white/10 rounded-lg p-2">
                                <p class="text-xs opacity-75">Fecha de Actualización</p>
                                <p class="text-sm font-semibold ">${estacion.fecha}</p>
                            </div>
                            <div class="bg-white/10 rounded-lg p-2">
                                <p class="text-xs opacity-75">Equipos</p>
                                <p class="text-sm font-semibold ">${estacion.equipos}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-medium">${estacion.tipo}</span>
                        <a href="estaciones/recorrido.php?id=${estacion.id}&nom_estacion=${estacion.nombre}&imgLogoEstacion=${estacion.imgLogo}"><span class="bg-white/20 px-4 py-2 rounded-full text-sm font-semibold text-white shadow hover:bg-white/30 transition">Recorrido</span></a>
                    </div>
                </div>
            
            `;
        return div;
    }



    // CRUD Operations
    abrirModalAgregar() {
        this.estacionEditando = null;
        this.elements.tituloModal.textContent = 'Nueva Estación';
        this.elements.btnGuardar.querySelector('.btn-text').textContent = 'Crear Estación';
        this.limpiarFormulario();
        $('#modalEstacion').modal();
    }

    // Abre el modal de edición y carga los datos de la estación seleccionada
    async editarEstacion(id) {
        try {
            // Mostrar "loading" en el botón editar mientras carga
            const btnEditar = document.querySelector(`.btn-editar[data-id="${id}"]`);
            if (btnEditar) {
                btnEditar.disabled = true;
                btnEditar.innerHTML = '<div class="loading"></div>';
            }

            this.estacionEditando = id;
            this.elements.tituloModal.textContent = 'Editar Estación';
            this.elements.btnGuardar.querySelector('.btn-text').textContent = 'Guardar Cambios';

            // Buscar estación en los datos ya cargados (sin llamar a la API)
            let estacion = this.estacionesOriginales.find(e => e.id == id);

            if (estacion) {
                this.llenarFormulario(estacion);
            } else {
                // Si no está en cache, obtener datos desde API
                const response = await fetch(`${this.apiUrl}?id=${id}`);
                const data = await response.json();

                if (data.success && data.data) {
                    this.llenarFormulario(data.data);
                } else {
                    throw new Error(data.error || 'Error al obtener datos de la estación');
                }
            }

            $('#modalEstacion').modal(); // Mostrar modal

        } catch (error) {
            console.error('Error:', error);
            this.mostrarToast('Error al cargar datos de la estación: ' + error.message, 'error');
        } finally {
            // Restaurar ícono de edición del botón
            const btnEditar = document.querySelector(`.btn-editar[data-id="${id}"]`);
            if (btnEditar) {
                btnEditar.disabled = false;
                btnEditar.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            `;
            }
        }
    }


    // Llena los campos del formulario con los datos de una estación
    llenarFormulario(estacion) {
        this.elements.estacionId.value = estacion.id || '';
        this.elements.nombreEstacion.value = estacion.nombre || '';
        this.elements.descripcionEstacion.value = estacion.descripcion || '';
        this.elements.tipoEstacion.value = estacion.tipo || '';


        // Marcar el estado correspondiente (Activa, Inactiva, Mantenimiento)
        const estado = estacion.estado || 'Activa';
        const radioEstado = document.querySelector(`input[name="estado"][value="${estado}"]`);
        if (radioEstado) {
            radioEstado.checked = true;
            this.actualizarRadioButton(radioEstado); // Estilo visual
        }

        console.log('Datos cargados en el formulario:', estacion); // Log de depuración
    }


    // Abre el modal de confirmación para eliminar una estación
    eliminarEstacion(id) {
        const estacion = this.estacionesOriginales.find(e => e.id == id);
        if (!estacion) {
            this.mostrarToast('No se encontró la estación', 'error');
            return;
        }

        this.estacionEditando = id; // Guardar ID de estación para confirmar
        this.elements.nombreEstacionEliminar.textContent = estacion.nombre || 'Estación sin nombre';
        $('#modalConfirmacion').modal(); // Mostrar modal de confirmación
    }


    // Confirma y ejecuta la eliminación de una estación
    async confirmarEliminar() {
        if (!this.estacionEditando) return;

        try {
            this.mostrarLoading(this.elements.btnConfirmarEliminar); // Mostrar spinner

            // Llamada DELETE a la API
            const response = await fetch(this.apiUrl, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: this.estacionEditando })
            });

            const data = await response.json();

            if (data.success) {
                this.mostrarToast('Estación eliminada exitosamente', 'success');
                await this.cargarEstaciones(); // Recargar datos
                $.modal.close(); // Cerrar modal
            } else {

                if (data.error && data.error.includes("23000")) {
                    this.mostrarToast('Error al eliminar estación: Hay equipos registrados en esta estación', 'error');
                    setTimeout(() => this.mostrarToast('Para eliminar esta estación, primero elimine los eqipos registrados en ella', 'info'), 1000);

                } else {
                    this.mostrarToast('Error al eliminar estación', 'error');
                }

                /* this.mostrarToast(data.error || 'Error al eliminar estación', 'error'); */
            }
        } catch (error) {
            console.error('Error:', error);
            this.mostrarToast('Error de conexión', 'error');
        } finally {
            this.ocultarLoading(this.elements.btnConfirmarEliminar); // Quitar spinner
            this.estacionEditando = null;
            $.modal.close();
        }
    }


    // Guarda una nueva estación o actualiza una existente
    async guardarEstacion(e) {
        e.preventDefault();

        const archivoLogo = this.elements.imgLogoEstacion.files[0];
        const nombre = this.elements.nombreEstacion.value.trim();
        const descripcion = this.elements.descripcionEstacion.value.trim();
        const tipo = this.elements.tipoEstacion.value;
        const estado = document.querySelector('input[name="estado"]:checked').value;

        // Validaciones básicas
        if (nombre.length < 3) {
            this.mostrarToast('El nombre debe tener al menos 3 caracteres', 'error');
            return;
        }

        if (descripcion.length < 10) {
            this.mostrarToast('La descripción debe tener al menos 10 caracteres', 'error');
            return;
        }

        if (!this.validarImagen(archivoLogo)) {
            return;
        }

        try {
            this.mostrarLoading(this.elements.btnGuardar); // Mostrar loading en botón

            const formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('descripcion', descripcion);
            formData.append('tipo', tipo);
            formData.append('estado', estado);
            if (archivoLogo) {
                formData.append('imgLogoEstacion', archivoLogo);
            }

            let url = this.apiUrl;
            let method = 'POST';

            // Si está editando, enviar método override porque estamos usando FormData
            if (this.estacionEditando) {
                formData.append('_method', 'PUT');
                formData.append('id', this.estacionEditando);
            }

            // Llamada a la API
            const response = await fetch(url, {
                method,
                body: formData
            });

            const responseText = await response.text();
            let data;

            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('Respuesta inválida del servidor:', response.status, response.statusText, responseText);
                throw new Error('Respuesta inválida del servidor: ' + responseText.substring(0, 200));
            }

            if (response.ok && data.success) {
                this.mostrarToast(data.message, 'success');
                await this.cargarEstaciones(); // Recargar tabla
                $.modal.close(); // Cerrar modal
            } else {
                console.error('Error de API al guardar estación:', response.status, responseText);
                this.mostrarToast(data.error || 'Error al guardar estación', 'error');
            }

        } catch (error) {
            console.error('Error:', error);
            this.mostrarToast('Error de conexión: ' + (error.message || 'no se pudo conectar'), 'error');
        } finally {
            this.ocultarLoading(this.elements.btnGuardar); // Quitar loading
        }
    }


    //Validar extencion de la imagen
    validarImagen(file) {
        if (!file) {
            return true; // No es obligatorio en edición si no se selecciona nueva imagen
        }

        const extensionesPermitidas = ['jpeg', 'png'];
        const extension = file.name.split('.').pop().toLowerCase();

        if (file.size > 2 * 1024 * 1024) { // Limite de 2MB
            this.mostrarToast('El archivo de imagen excede el tamaño máximo permitido (2MB)', 'error');
            return false;
        }

        if (!extensionesPermitidas.includes(extension)) {
            this.mostrarToast('Archivo no permitido. Solo se permiten imágenes JPEG y PNG.', 'error');
            return false;
        }

        return true;
    }



    // Modal Management
    cerrarModal() {
        $.modal.close();
        this.limpiarFormulario();
        this.estacionEditando = null;
    }

    cerrarModalConfirmacion() {
        $.modal.close();
        this.estacionEditando = null;
    }

    limpiarFormulario() {
        this.elements.formEstacion.reset();
        this.elements.estacionId.value = '';

        // Resetear radio buttons
        const radioActiva = document.querySelector('input[name="estado"][value="Activa"]');
        if (radioActiva) {
            radioActiva.checked = true;
            this.actualizarRadioButton(radioActiva);
        }
    }



    //Actualiza visualmente el botón de radio seleccionado
    actualizarRadioButton(radio) {
        // Elimina estilos de todos los labels que contengan input de tipo radio con name="estado"
        document.querySelectorAll('label').forEach(label => {
            if (label.querySelector('input[name="estado"]')) {
                label.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50'); // Elimina estilos de selección
            }
        });

        // Agrega los estilos al label del radio actualmente seleccionado
        radio.closest('label').classList.add('ring-2', 'ring-blue-500', 'bg-blue-50');
    }

    //Muestra un spinner y desactiva el botón mientras se realiza una acción
    mostrarLoading(button) {
        const loading = button.querySelector('.loading');   // Obtiene el spinner dentro del botón
        const text = button.querySelector('.btn-text');     // Obtiene el texto del botón

        if (loading && text) {
            loading.classList.remove('hidden'); // Muestra el spinner
            button.disabled = true;             // Desactiva el botón para evitar múltiples clics
        }
    }

    //Oculta el spinner y reactiva el botón
    ocultarLoading(button) {
        const loading = button.querySelector('.loading');   // Obtiene el spinner dentro del botón
        const text = button.querySelector('.btn-text');     // Obtiene el texto del botón

        if (loading && text) {
            loading.classList.add('hidden'); // Oculta el spinner
            button.disabled = false;         // Reactiva el botón
        }
    }

    //Muestra una notificación tipo toast con estilo y mensaje según el tipo
    mostrarToast(mensaje, tipo = 'success') {
        const toast = document.createElement('div'); // Crea el contenedor del toast

        // Colores según el tipo de mensaje
        const colores = {
            success: 'bg-green-500',     // Verde para éxito
            error: 'bg-red-500',         // Rojo para error
            warning: 'bg-yellow-500',    // Amarillo para advertencia
            info: 'bg-blue-500'          // Azul para información
        };

        // Íconos SVG según el tipo de mensaje
        const iconos = {
            success: `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>`,
            error: `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>`,
            warning: `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4
                c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>`,
            info: `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01
                M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>`
        };

        // Asigna las clases de estilo y contenido HTML del toast
        toast.className = `${colores[tipo]} text-white px-6 py-3 rounded-xl shadow-lg transform transition-all duration-300 translate-x-full flex items-center space-x-2`;

        toast.innerHTML = `
        ${iconos[tipo]}
        <span>${mensaje}</span>
    `;

        // Agrega el toast al contenedor en el DOM
        this.elements.toastContainer.appendChild(toast);

        // Hace visible el toast después de un pequeño retraso para activar animación de entrada
        setTimeout(() => toast.classList.remove('translate-x-full'), 100);

        // Después de 3 segundos, oculta y elimina el toast con animación
        setTimeout(() => {
            toast.classList.add('translate-x-full'); // Animación de salida
            setTimeout(() => toast.remove(), 300);    // Remover del DOM
        }, 5000);
    }





    //Aplica los filtros seleccionados y actualiza las estaciones mostradas
    aplicarFiltros() {
        // Obtener valores actuales de filtros desde los elementos HTML
        this.filtros.tipo = this.elements.filtroTipo.value;
        this.filtros.estado = this.elements.filtroEstado.value;
        this.filtros.busqueda = this.elements.barraBusqueda.value.toLowerCase().trim();

        // Filtrar estaciones según los criterios
        this.estacionesFiltradas = this.estacionesOriginales.filter(estacion => {
            // Filtrar por tipo
            if (this.filtros.tipo && estacion.tipo !== this.filtros.tipo) return false;

            // Filtrar por estado
            if (this.filtros.estado && estacion.estado !== this.filtros.estado) return false;

            // Filtrar por texto de búsqueda
            if (this.filtros.busqueda) {
                const enNombre = estacion.nombre_lower.includes(this.filtros.busqueda);
                const enDescripcion = estacion.descripcion_lower.includes(this.filtros.busqueda);
                const enTipo = estacion.tipo.toLowerCase().includes(this.filtros.busqueda);
                const enEstado = estacion.estado.toLowerCase().includes(this.filtros.busqueda);

                if (!enNombre && !enDescripcion && !enTipo && !enEstado) return false;
            }

            return true; // Si pasa todos los filtros
        });

        // Actualizar la UI
        this.mostrarResultados();
        this.actualizarIndicadores();
        //this.actualizarEstadisticasFiltradas();
    }

    //Muestra los resultados filtrados en el grid
    mostrarResultados() {
        // Ocultar todas las tarjetas de estación
        const todas = this.elements.gridEstaciones.querySelectorAll('.estacion-card');
        todas.forEach(el => {
            el.style.display = 'none';
            el.classList.remove('animate-fade-in');
        });

        if (this.estacionesFiltradas.length === 0) {
            // Mostrar mensaje si no hay resultados
            this.elements.sinResultados.classList.remove('hidden');
        } else {
            this.elements.sinResultados.classList.add('hidden');

            // Mostrar tarjetas filtradas con animación
            this.estacionesFiltradas.forEach((estacion, index) => {
                const el = this.elements.gridEstaciones.querySelector(`[data-id="${estacion.id}"]`);
                if (el) {
                    setTimeout(() => {
                        el.style.display = 'block';
                        el.classList.add('animate-fade-in');
                    }, index * 100); // retraso para animación secuencial
                }
            });
        }
    }

    //Actualiza la visibilidad del resumen de filtros activos y resultados de búsqueda
    actualizarIndicadores() {
        const hayFiltros = this.filtros.tipo || this.filtros.estado || this.filtros.busqueda;

        // Mostrar filtros activos si existen
        if (hayFiltros) {
            this.elements.filtrosActivos.classList.remove('hidden');
            this.actualizarFiltrosActivos();
        } else {
            this.elements.filtrosActivos.classList.add('hidden');
        }

        // Mostrar resultados de búsqueda si se ha ingresado texto
        if (this.filtros.busqueda) {
            this.elements.resultadosBusqueda.classList.remove('hidden');
            const total = this.estacionesFiltradas.length;
            const texto = total === 1 ? 'resultado' : 'resultados';
            this.elements.textoResultados.textContent =
                `${total} ${texto} para "${this.filtros.busqueda}"`;
        } else {
            this.elements.resultadosBusqueda.classList.add('hidden');
        }
    }

    //Reconstruye la lista de filtros activos en pantalla
    actualizarFiltrosActivos() {
        const container = this.elements.filtrosActivos;

        // Elimina todos los chips existentes de filtros activos
        const filtrosExistentes = container.querySelectorAll('.filtro-activo');
        filtrosExistentes.forEach(f => f.remove());

        // Agrega chips de filtros según lo seleccionado
        if (this.filtros.tipo) {
            this.agregarFiltroActivo('Tipo', this.filtros.tipo, 'tipo');
        }
        if (this.filtros.estado) {
            this.agregarFiltroActivo('Estado', this.filtros.estado, 'estado');
        }
    }

    //Agrega un chip visual de filtro activo
    agregarFiltroActivo(label, value, tipo) {
        const filtro = document.createElement('span');
        filtro.className = 'filtro-activo bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm flex items-center space-x-2';
        filtro.innerHTML = `
        <span>${label}: ${value}</span>
        <button class="btn-remover-filtro hover:bg-blue-200 rounded-full p-1" data-tipo="${tipo}">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
        this.elements.filtrosActivos.appendChild(filtro);
    }

    //Quita un filtro individual (tipo o estado)
    removerFiltro(tipo) {
        switch (tipo) {
            case 'tipo':
                this.elements.filtroTipo.value = '';
                break;
            case 'estado':
                this.elements.filtroEstado.value = '';
                break;
        }
        this.aplicarFiltros();
    }

    //Limpia solo la barra de búsqueda
    limpiarBusqueda() {
        this.elements.barraBusqueda.value = '';
        this.elements.limpiarBusqueda.classList.add('hidden');
        this.aplicarFiltros();
    }

    //Limpia todos los filtros (tipo, estado, búsqueda)
    limpiarFiltros() {
        this.elements.filtroTipo.value = '';
        this.elements.filtroEstado.value = '';
        this.elements.barraBusqueda.value = '';
        this.elements.limpiarBusqueda.classList.add('hidden');
        this.aplicarFiltros();
        this.mostrarToast('Filtros limpiados', 'info');
    }

    //Limpieza total (usa limpiarFiltros como base)
    limpiarTodo() {
        this.limpiarFiltros();
    }

    //Muestra estadísticas generales de estaciones
    actualizarEstadisticas() {
        const total = this.estacionesOriginales.length;
        const activas = this.estacionesOriginales.filter(e => e.estado === 'Activa').length;
        const inactivas = this.estacionesOriginales.filter(e => e.estado === 'Inactiva').length;
        const mantenimiento = this.estacionesOriginales.filter(e => e.estado === 'Mantenimiento').length;

        if (this.elements.totalEstaciones) this.elements.totalEstaciones.textContent = total;
        if (this.elements.estacionesActivas) this.elements.estacionesActivas.textContent = activas;
        if (this.elements.estacionesInactivas) this.elements.estacionesInactivas.textContent = inactivas;
        if (this.elements.estacionesMantenimiento) this.elements.estacionesMantenimiento.textContent = mantenimiento;
    }

    /* //Actualiza estadísticas sólo si hay filtros activos, con asterisco (*)
    actualizarEstadisticasFiltradas() {
        const hayFiltros = this.filtros.tipo || this.filtros.estado || this.filtros.busqueda;

        if (hayFiltros) {
            const total = this.estacionesFiltradas.length;
            const activas = this.estacionesFiltradas.filter(e => e.estado === 'Activa').length;
            const inactivas = this.estacionesFiltradas.filter(e => e.estado === 'Inactiva').length;
            const mantenimiento = this.estacionesFiltradas.filter(e => e.estado === 'Mantenimiento').length;

            if (this.elements.totalEstaciones) this.elements.totalEstaciones.textContent = `${total}*`;
            if (this.elements.estacionesActivas) this.elements.estacionesActivas.textContent = `${activas}*`;
            if (this.elements.estacionesInactivas) this.elements.estacionesInactivas.textContent = `${inactivas}*`;
            if (this.elements.estacionesMantenimiento) this.elements.estacionesMantenimiento.textContent = `${mantenimiento}*`;
        } else {
            this.actualizarEstadisticas(); // Restaurar estadísticas normales si no hay filtros
        }
    } */




}

// Inicializar el sistema
let sistemaEstaciones;
$(document).ready(() => {
    sistemaEstaciones = new SistemaEstaciones();

    // Funcionalidad de radio buttons personalizados
    $(document).on('change', 'input[name="estado"]', function () {
        // Remover selección de todos
        $('label').each(function () {
            if ($(this).find('input[name="estado"]').length) {
                $(this).removeClass('ring-2 ring-blue-500 bg-blue-50');
            }
        });

        // Agregar selección al actual
        $(this).closest('label').addClass('ring-2 ring-blue-500 bg-blue-50');
    });
});
