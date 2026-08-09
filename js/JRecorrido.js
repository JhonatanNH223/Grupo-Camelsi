
/* Clase de funcionamiento recorrido */
class Recorrido {
    /* Contructor de l aclase */
    constructor() {

        this.apiUrl = 'api-recorrido.php';
        this.userLevel = nivelphp; //Nivel del usuario al iniciar secion
        this.id = idDesdePHP; //id de la estación
        this.nom_estacion = nom_estacionPHP;
        this.imgLogoEstacion = imgLogoEstacionPHP;
        this.dataTable = null; // Variable para almacenar la instancia de DataTable
        this.editar = null; //id de elemento a editar
        this.elementos = []; //Arreglo donde se guardan todos los eleemtos

        this.elements = {
            /* Tabla del recorrido */
            tabla: document.getElementById('tabla-recorrido'),

            //form y botones de modal
            btnAgregarRecorrido: document.getElementById('btnAgregarRecorrido'),
            btnCerrarModal: document.getElementById('btnCerrarModal'),
            btnCancelar: document.getElementById('btnCancelar'),
            formRecorrido: document.getElementById('formRecorrido'),

            //Modal eliminar 
            nombreEstacionEliminar: document.getElementById('nombreEstacionEliminar'),
            btnConfirmarEliminar: document.getElementById('btnConfirmarEliminar'),
            btnCancelarEliminar: document.getElementById('btnCancelarEliminar'),

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
            if (e.target.id === 'btnAgregarRecorrido' || e.target.closest('#btnAgregarRecorrido')) { e.preventDefault(); this.abrirModal(); }

            //evento para el btn cerrar modal
            if (e.target.id === 'btnCerrarModal' || e.target.closest('#btnCerrarModal')) { e.preventDefault(); this.cerrarModal(); }

            //evento para el btn cancelar
            if (e.target.id === 'btnCancelar') { e.preventDefault(); this.cerrarModal(); }

            //evento para el btn generar PDF
            if (e.target.id === "btnPDF" || e.target.closest("#btnPDF")) {
                e.preventDefault(); this.generarPDF();
            }
        });

        //evento para el btn editar
        $(document).on('click', '.btn-editar', (e) => { const id = $(e.currentTarget).data('id'); this.editarRecorrido(id); });

        //evento para el btn eliminar
        $(document).on('click', '.btn-eliminar', (e) => { const id = $(e.currentTarget).data('id'); this.eliminarRecorrido(id); });

        // Modal confirmación
        this.elements.btnCancelarEliminar.addEventListener('click', () => this.cerrarModalConfirmacion());
        this.elements.btnConfirmarEliminar.addEventListener('click', () => this.confirmarEliminar());

        // Form submit - mantener el event listener directo
        this.elements.formRecorrido.addEventListener('submit', (e) => { e.preventDefault(); this.guardarRecorrido(e); });
    }

    // Cargar los datos de la tabla desde la API
    async cargarTabla() {
        try {
            // Mostrar indicador de carga y ocultar otros elementos
            this.elements.loading.classList.remove('hidden');
            this.elements.sinResultados.classList.add('hidden');
            this.elements.centro_dato.classList.add('hidden');

            // Realizar petición a la API usando el ID actual
            const response = await fetch(`${this.apiUrl}?id=${this.id}`);
            const result = await response.json();

            // Ocultar el indicador de carga
            this.elements.loading.classList.add('hidden');

            // Si se encontraron datos
            if (result.success && result.data.length > 0) {
                // Mostrar el contenedor de datos y renderizar tabla
                this.elements.centro_dato.classList.remove('hidden');
                this.renderTabla(result.data);
                this.elementos = result.data; // Guardar datos en la propiedad
            } else {
                // Mostrar mensaje de "sin resultados"
                this.elements.sinResultados.classList.remove('hidden');
                this.mostrarToast('No se encontraron registros', 'info');
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fila.localizacion || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fila.contenido || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fila.capacidad || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            ${this.getSimboloHTML(fila.carga)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            ${this.getSimboloHTML(fila.mantenimiento)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            ${this.getSimboloHTML(fila.metalico)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            ${this.getSimboloHTML(fila.presillo)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            ${this.getSimboloHTML(fila.manguera)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            ${this.getSimboloHTML(fila.cincho)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            ${this.getSimboloHTML(fila.señalam)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            ${this.getSimboloHTML(fila.soporte)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                            ${this.getSimboloHTML(fila.pintura)}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fila.fecha_mes || '-'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${fila.fecha_año || '-'}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate" title="${fila.observaciones || ''}">${fila.observaciones || '-'}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2 justify-center items-center">

                            ${this.userLevel == 'USUARIO'? '' : ` 
                                <!-- Botón Editar -->
                                <button data-id="${fila.id_recorrido}"
                                  class="btn-editar w-8 h-8 bg-neutral-300 rounded-md flex items-center justify-center hover:bg-blue-400 hover:scale-105 transition-transform duration-300">
                                  <img src="../../svg/Editar.svg" class="w-7 h-7">
                                </button>

                                <!-- Botón Eliminar -->
                                <button data-id="${fila.id_recorrido}"
                                  class="btn-eliminar w-8 h-8 bg-neutral-300 rounded-md flex items-center justify-center hover:bg-red-400 hover:scale-105 transition-transform duration-300">
                                  <img src="../../svg/Eliminar.svg" class="w-7 h-7">
                                </button>

                            `} 
                          </div>
                        </td>
                        

                    </tr>
                `).join('');

        // Inicializar DataTable solo si está disponible y no existe ya
        if (typeof DataTable !== 'undefined') {
            try {
                this.dataTable = new DataTable('#tabla-recorrido', {
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

    getSimboloHTML(valor) {
        switch (valor) {
            case "1": case 1: return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">✓</span>`;
            case "2": case 2: return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">✗</span>`;
            case "3": case 3: return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⊗</span>`;
            case "4": case 4: return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-200 text-gray-800">N/A</span>`;
            default: return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-400">?</span>`;
        }
    }





    // Insertar o editar un elemento
    async guardarRecorrido(e) {
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
                localizacion: document.getElementById('localizacion').value.trim(),
                contenido: document.getElementById('contenido').value.trim(),
                capacidad: document.getElementById('capacidad').value.trim(),
                carga: document.getElementById('carga').value.trim(),
                mantenimiento: document.getElementById('mantenimiento').value.trim(),
                metalico: document.getElementById('metalico').value.trim(),
                presillo: document.getElementById('presillo').value.trim(),
                manguera: document.getElementById('manguera').value.trim(),
                cincho: document.getElementById('cincho').value.trim(),
                senalam: document.getElementById('senalam').value.trim(),
                soporte: document.getElementById('soporte').value.trim(),
                pintura: document.getElementById('pintura').value.trim(),
                fecha_mes: document.getElementById('fecha_mes').value.trim(),
                fecha_año: document.getElementById('fecha_año').value.trim(),
                observaciones: document.getElementById('observaciones').value.trim(),
                f_estacion: this.id // ID de la estación relacionada
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
                throw new Error(`Error HTTP ${response.status}: ${errorText}`);
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
            this.mostrarToast(`Error: ${error.message}`, "error");
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
    async editarRecorrido(id) {
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
            this.mostrarToast('Error al cargar datos de la estación al editar', 'error');
        }
    }

    //cargar elementos al modal y abrir
    abrirModal(datos = null) {

        // Limpiar estado anterior
        this.elements.formRecorrido.reset();

        if (datos) {
            // Obtener y estructurar los datos del formulario  EDITAR 
            document.getElementById('tituloModal').textContent = 'Editar Registro de Recorrido';
            document.getElementById('recorridoId').value = datos.id;
            document.getElementById('localizacion').value = datos.localizacion;
            document.getElementById('contenido').value = datos.contenido || '';
            document.getElementById('capacidad').value = datos.capacidad || '';

            document.getElementById('carga').value = datos.carga || '';
            document.getElementById('mantenimiento').value = datos.mantenimiento || '';
            document.getElementById('metalico').value = datos.metalico || '';
            document.getElementById('presillo').value = datos.presillo || '';
            document.getElementById('manguera').value = datos.manguera || '';
            document.getElementById('cincho').value = datos.cincho || '';
            document.getElementById('senalam').value = datos.señalam || '';
            document.getElementById('soporte').value = datos.soporte || '';
            document.getElementById('pintura').value = datos.pintura || '';

            document.getElementById('fecha_mes').value = datos.fecha_mes || '';
            document.getElementById('fecha_año').value = datos.fecha_año || '';
            document.getElementById('observaciones').value = datos.observaciones || '';
            document.querySelector('#btnGuardar .btn-text').textContent = 'Actualizar Registro';
        } else {
            // Modo creación
            document.getElementById('tituloModal').textContent = 'Nuevo Registro de Recorrido';
            document.querySelector('#btnGuardar .btn-text').textContent = 'Guardar Registro';
        }

        // Asegurar que el botón esté habilitado
        const btnGuardar = document.getElementById('btnGuardar');
        btnGuardar.disabled = false;

        // Abrir modal
        try {
            $('#modalRecorrido').modal();
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
        this.elements.formRecorrido.reset();

        // Limpiar campos hidden
        document.getElementById('recorridoId').value = '';

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
    eliminarRecorrido(id) {
        const elementos = this.elementos.find(e => e.id_recorrido == id);
        if (!elementos) {
            this.mostrarToast('No se encontró el elemento a eliminar', 'error');
            return;
        }

        this.editar = id;
        this.elements.nombreEstacionEliminar.textContent = elementos.localizacion || 'Estación sin nombre';
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
                this.mostrarToast('Error al eliminar elemento', 'error');
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

    // Método de debug para verificar el estado
    debugEstado() {
        console.log('Estado actual:', {
            id: this.id,
            dataTable: this.dataTable ? 'Inicializado' : 'No inicializado',
            modalVisible: $('#modalRecorrido').is(':visible'),
            btnAgregarDisponible: document.getElementById('btnAgregarRecorrido') ? 'Sí' : 'No'
        });
    }


    async generarPDF() {
        try {
            if (!this.elementos || this.elementos.length === 0) {
                this.mostrarToast("No hay datos para generar el PDF", "warning")
                return
            }

            this.mostrarToast("Generando PDF...", "info")
            const { jsPDF } = window.jspdf
            const doc = new jsPDF("l", "mm", "a4")

            const pageWidth = doc.internal.pageSize.getWidth()
            const pageHeight = doc.internal.pageSize.getHeight()
            const margin = 10
            const rowHeight = 5.5
            const elementsPerPage = 20
            const totalPages = Math.ceil(this.elementos.length / elementsPerPage)

            //Imagenes
            const imgCheck = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAAxAAAAMQBz4pYTAAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAADxSURBVDiNpZMxSkNBFEXPvdMIASEgKliI2ljYCLoPG+3sLO0sXYW1m3AFgmCTxsKksBG+hWDnAqI+mx9NZr5Jvhm4zcw9M2/u4ykiWGT5P5CkQ0ldACKilcCXwiFcAdtt4YsaHumxDXyewSF0MyeczoS/JuF0D3TmgU+FPzO4ByxHBLWJPWCrAT4R/shKfwC6vx44EB4KD42vRoeQjur9cbgPrEw8InSbmd5F6pV/9hOwVlQpPCjTLfQMbDRnBKvG12VQP3oBNv8MeeymfZHuMvgV2JnapYbkj4Ur4Tdgd1ab1TSNkpaA9YioZg7WouP8DSOeecurd/0LAAAAAElFTkSuQmCC";
            const imgNo = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAAdgAAAHYBTnsmCAAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAEFSURBVDiNldM9SkNBEMDxHxG9hWihvIj300JikSKVxMLTJB4jegwJaoIJBtZiZ8nyfJHnwBYzs/+dz+W3XOMRL1hhjdewNXFnhHEbPMETdkhxVthU+jfmlT6u4ecwvuEWp+Eb4AITbCs4RSZE5IRFBbZl1IJndc27iNwX3kY5Dbk5KdLuA4+inIQpucPpQPQuGC6rkn3iowO+acH3lW+AL3nE1vKojnrCRVZ4Jy9Mkkf1V9q1nNcllCZOesJwF/4HGMojObgkHdGXwQyLcfYPuExtWoxdSzKRezKIO2eR9tJ+C4/LA+MKnkdqRd/IU6o/07SG60dK2o39d17Le7KQG3bVBn8AYeSAf/xcreUAAAAASUVORK5CYII=";
            const imgIcono = "data:image/png;base64,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";

            const imagenCamelsi = new Image();
            imagenCamelsi.src = "../../svg/logo1.png";

            // Activar fuente Roboto
            doc.setFont("Roboto", "normal")

            const drawHeader = (pageNum, totalPages) => {
                const logoX = margin + 12
                const logoY = 10
                const logoRadius = 15

                doc.addImage(imgIcono, "PNG", logoX, logoY, 40, 40);
                //doc.addImage(imagenCamelsi, "PNG", logoX, logoY, 40, 40);

                const centerX = pageWidth / 2
                doc.setTextColor(0, 0, 0)
                doc.setFontSize(7)
                doc.setFont(undefined, "bold")
                doc.text("LILIANA AMAPOLA HENAINE CASTILLO", centerX, 8, { align: "center" })
                doc.setFont(undefined, "normal")
                doc.setFontSize(6.5)
                doc.text("BLVD. VIRGINIA PERALTA #2079 COL. ADOLFO RUIZ CORTINES", centerX, 12, { align: "center" })
                doc.text("LA PAZ BAJA CALIFORNIA SUR", centerX, 16, { align: "center" })
                doc.text("TELEFONO 612-129-37-35", centerX, 20, { align: "center" })

                /* const ritzX = pageWidth - 65
                const ritzY = 10
                doc.setDrawColor(0)
                doc.setFillColor(0, 0, 0)
                doc.rect(ritzX, ritzY-5, 50, 20, "FD")
                doc.setTextColor(255, 255, 255)
                doc.setFontSize(7)
                doc.setFont(undefined, "bold")
                const imagenCamelsi = new Image();
                imagenCamelsi.src = "../../svg/imgLogos/" + this.nom_estacion + ".png";
                doc.addImage(imagenCamelsi, "PNG", ritzX + 12, ritzY-4, 25, 18);
                //doc.text(this.nom_estacion, ritzX + 25, ritzY + 9, { align: "center" }) */

                /* const ritzX = pageWidth - 65
                const ritzY = 10
                doc.setFillColor(255, 255, 255)
                //doc.setFillColor(0, 0, 0)
                doc.rect(ritzX, ritzY-5, 50, 20, "FD")
                doc.setTextColor(255, 255, 255)
                doc.setFontSize(7)
                doc.setFont(undefined, "bold")
                const imagenCamelsi = new Image();
                imagenCamelsi.src = "../../svg/imgLogos/" + this.nom_estacion + ".png";
                doc.addImage(imagenCamelsi, "PNG", ritzX + 12, ritzY-4, 25, 18);
                //doc.text(this.nom_estacion, ritzX + 25, ritzY + 9, { align: "center" }) */

                const ritzX = pageWidth - 65
                const ritzY = 10
                doc.setFillColor(255, 255, 255)
                doc.rect(ritzX, ritzY-5, 50, 20, "FD")
                doc.setTextColor(255, 255, 255)
                doc.setFontSize(7)
                doc.setFont(undefined, "bold")
                const imagenCamelsi = new Image();
                imagenCamelsi.src = "../../svg/imgLogos/" + this.imgLogoEstacion;
                const extension = this.imgLogoEstacion.split('.').pop().toUpperCase();
                const formato =
                    extension === "JPG" || extension === "JPEG"
                        ? "JPEG"
                        : extension === "PNG"
                        ? "PNG"
                        : "PNG"; 

                doc.addImage(imagenCamelsi, formato, ritzX + 12, ritzY - 4, 25, 18);

                
                doc.setTextColor(0, 0, 0)
                doc.setFillColor(240, 240, 240)
                doc.rect(ritzX, ritzY + 18, 50, 6, "FD")
                doc.setFontSize(7)
                doc.setFont(undefined, "bold")
                doc.text("FECHA", ritzX + 25, ritzY + 22, { align: "center" })

                doc.setFillColor(255, 255, 255)
                doc.rect(ritzX, ritzY + 24, 50, 8, "FD")
                doc.setFillColor(240, 240, 240)
                doc.rect(ritzX, ritzY + 34, 50, 6, "FD")
                doc.text(`CONTROL ${pageNum} DE ${totalPages}`, ritzX + 25, ritzY + 38, { align: "center" })

                doc.setFontSize(10)
                doc.text("CONTROL DE VISITAS E INSPECCIÓN DE EXTINTORES", centerX, 28, { align: "center" })
                const empresaY = 32
                doc.setDrawColor(0)
                doc.setFillColor(240, 240, 240)
                doc.rect(centerX - 45, empresaY, 90, 6, "FD")
                doc.setFontSize(7)
                doc.text("EMPRESA", centerX, empresaY + 4, { align: "center" })
                doc.setFillColor(255, 255, 255)
                doc.rect(centerX - 45, empresaY + 6, 90, 8, "FD")
                doc.setFont(undefined, "bold")
                doc.setFontSize(8)
                doc.text(this.nom_estacion, centerX, empresaY + 11, { align: "center" })
            }

            const drawTable = (startY, pageElements, startIndex) => {
                const rowHeight = 5.2;
                const maxRowsPerPage = 20;

                // Anchos ajustados para 15 columnas 
                const widths = [10, 35, 18, 16, 10, 12, 10, 10, 10, 12, 10, 10, 10, 18, 30];
                const colX = (pageWidth - widths.reduce((a, b) => a + b, 0)) / 2;
                let y = startY;

                doc.setDrawColor(0);
                doc.setLineWidth(0.3);
                doc.setFont("Roboto", "normal");

                const headerHeights = {
                    h1: 5, h2: 5, h3: 5
                };

                // -------- ENCABEZADO NIVEL 1 --------
                const headersLevel1 = [
                    { text: "No.", col: 0, span: 1, height: headerHeights.h1 + headerHeights.h2 + headerHeights.h3 },
                    { text: "UBICACIÓN", col: 1, span: 1, height: headerHeights.h1 + headerHeights.h2 },
                    { text: "CARACTERÍSTICAS", col: 2, span: 2, height: headerHeights.h1 + headerHeights.h2 },
                    { text: "VERIFICACIÓN", col: 4, span: 9, height: headerHeights.h1 }, // span reducido de 10 a 9
                    { text: "FECHA RECARGA", col: 13, span: 1, height: headerHeights.h1 + headerHeights.h2 + headerHeights.h3 },
                    { text: "OBSERVACIONES", col: 14, span: 1, height: headerHeights.h1 + headerHeights.h2 + headerHeights.h3 }
                ];

                doc.setFont(undefined, "bold");

                for (const h of headersLevel1) {
                    const x = colX + widths.slice(0, h.col).reduce((a, b) => a + b, 0);
                    const w = widths.slice(h.col, h.col + h.span).reduce((a, b) => a + b, 0);

                    // Fondo celeste
                    doc.setFillColor(173, 216, 230);
                    doc.rect(x, y, w, h.height, "FD");

                    // Cambiar tamaño solo para "FECHA RECARGA"
                    if (h.text === "FECHA RECARGA") {
                        doc.setFontSize(5.2); // más pequeño solo para ese texto
                    } else {
                        doc.setFontSize(6.3); // tamaño general
                    }

                    doc.text(h.text, x + w / 2, y + (h.height > 10 ? 6.5 : 3.5), { align: "center" });
                }


                // -------- ENCABEZADO NIVEL 2 --------
                y += headerHeights.h1;
                const headersLevel2 = [
                    { text: "LOCALIZACIÓN", col: 1 },
                    { text: "CONTENIDO", col: 2 },
                    { text: "CAPACIDAD", col: 3 },
                    { text: "CARGA", col: 4 },
                    { text: "MANÓMETRO", col: 5 },
                    { text: "SEGUROS", col: 6, span: 2 },
                    { text: "MANGUERA", col: 8 },
                    { text: "CINCHO", col: 9 },
                    { text: "SEÑALAM", col: 10 },
                    { text: "SOPORTE", col: 11 },
                    { text: "PINTURA", col: 12 }
                ];

                doc.setFontSize(4.3);
                for (const h of headersLevel2) {
                    const x = colX + widths.slice(0, h.col).reduce((a, b) => a + b, 0);
                    const w = widths.slice(h.col, h.span ? h.col + h.span : h.col + 1).reduce((a, b) => a + b, 0);
                    const height = h.span ? headerHeights.h2 : headerHeights.h2 + headerHeights.h3;
                    doc.setFillColor(173, 216, 230);
                    doc.rect(x, y, w, height, "FD");
                    doc.text(h.text, x + w / 2, y + 3.2, { align: "center" });
                }

                // -------- ENCABEZADO NIVEL 3 --------
                y += headerHeights.h2;
                const segurosSubs = [
                    { text: "METÁLICO", col: 6 },
                    { text: "PRESILLO", col: 7 }
                ];

                for (const h of segurosSubs) {
                    const x = colX + widths.slice(0, h.col).reduce((a, b) => a + b, 0);
                    const w = widths[h.col];
                    doc.setFillColor(173, 216, 230);
                    doc.rect(x, y, w, headerHeights.h3, "FD");
                    doc.text(h.text, x + w / 2, y + 3.2, { align: "center" });
                }

                // -------- CUERPO DE LA TABLA --------
                y += headerHeights.h3;
                doc.setFont(undefined, "normal");
                doc.setFontSize(6);
                doc.setTextColor(0, 0, 0);

                for (let i = 0; i < pageElements.length; i++) {
                    // Si la tabla excede el límite visual, cortar y seguir en otra página
                    if (i > 0 && i % maxRowsPerPage === 0) {
                        doc.addPage();
                        drawHeader(doc, colX, startY); // Redibuja encabezado
                        y = startY + headerHeights.h1 + headerHeights.h2 + headerHeights.h3;
                    }

                    const el = pageElements[i];
                    let x = colX;

                    const rowData = [
                        startIndex + i + 1,
                        el.localizacion || "",
                        el.contenido || "",
                        el.capacidad || "",
                        el.carga || "",
                        el.mantenimiento || "",
                        el.metalico || "",
                        el.presillo || "",
                        el.manguera || "",
                        el.cincho || "",
                        el.señalam || "",
                        el.soporte || "",
                        el.pintura || "",
                        `${el.fecha_mes || ""} ${el.fecha_año || ""}`.trim(),
                        el.observaciones || ""
                    ];

                    for (let j = 0; j < rowData.length; j++) {
                        const w = widths[j];
                        doc.rect(x, y, w, rowHeight, "D");

                        const val = String(rowData[j]).trim(); // forzar string y quitar espacios

                        if (j >= 4 && j <= 12) {
                            const centerX = x + w / 2;
                            const centerY = y + rowHeight / 2 - 1.2;

                            if (val == '1') {
                                if (imgCheck) {
                                    doc.addImage(imgCheck, "PNG", centerX - 1, centerY, 2, 2);
                                } else {
                                    doc.text("✓", centerX, y + rowHeight / 2 + 1, { align: "center" });
                                }
                            } else if (val == '2') {
                                doc.text("X", centerX, y + rowHeight / 2 + 1, { align: "center" });
                            } else if (val == '3') {
                                if (imgNo) {
                                    doc.addImage(imgNo, "PNG", centerX - 1, centerY, 2, 2);
                                } else {
                                    doc.text("✗", centerX, y + rowHeight / 2 + 1, { align: "center" });
                                }
                            } else {
                                doc.text("N/A", centerX, y + rowHeight / 2 + 1, { align: "center" });
                            }
                        } else {
                            const maxLength = w < 12 ? 6 : 20;
                            doc.text(val.substring(0, maxLength), x + 0.5, y + rowHeight / 2 + 1);
                        }

                        x += w;
                    }


                    y += rowHeight;
                }
            };



            const drawFooter = () => {
                const firmaY = pageHeight - 8
                const centerX = pageWidth / 2
                const legendY = firmaY - 15

                doc.setFont("Roboto", "normal")
                doc.setFontSize(6)
                doc.setDrawColor(0)
                doc.setFillColor(255, 255, 255)
                doc.setTextColor(0, 0, 0)
                doc.rect(centerX - 35, legendY, 70, 12, "FD")


                // Fondo blanco para la leyenda
                doc.rect(centerX - 35, legendY, 70, 12, "FD");

                // Tamaño y posición de imágenes
                const iconSize = 2;
                const leftX = centerX - 35 + 2;
                const rightX = centerX + 3;

                // Imágenes + texto (izquierda)
                doc.addImage(imgCheck, "PNG", leftX, legendY + 3, iconSize, iconSize);
                doc.text("BUEN ESTADO", leftX + iconSize + 2, legendY + 5);

                doc.addImage(imgNo, "PNG", leftX, legendY + 8, iconSize, iconSize);
                doc.text("NO TIENE", leftX + iconSize + 2, legendY + 10);

                doc.text("X MAL ESTADO", centerX + 3, legendY + 4)
                doc.text("N/A NO APLICA", centerX + 3, legendY + 8)

                doc.setLineWidth(0.5)
                doc.line(margin + 20, firmaY, margin + 80, firmaY)
                doc.line(pageWidth - 80, firmaY, pageWidth - 20, firmaY)
                doc.setFontSize(6)
                doc.setFont(undefined, "bold")
                doc.text("FIRMA GRUPO CAMELSI", margin + 50, firmaY + 3, { align: "center" })
                doc.text("FIRMA EMPRESA", pageWidth - 50, firmaY + 3, { align: "center" })
            }



            for (let p = 0; p < totalPages; p++) {
                if (p > 0) doc.addPage()
                drawHeader(p + 1, totalPages)
                const pageStart = p * elementsPerPage
                const pageData = this.elementos.slice(pageStart, pageStart + elementsPerPage)
                drawTable(60, pageData, pageStart)
                drawFooter()
            }

            const fileName = `Recorrido_${this.nom_estacion}_${new Date().toISOString().split("T")[0]}.pdf`
            doc.save(fileName)
            this.mostrarToast("PDF generado exitosamente", "success")

        } catch (error) {
            console.error("Error generando PDF:", error)
            this.mostrarToast("Error al generar el PDF: " + error.message, "error")
        }
    }

}

// Inicializar la aplicación
let recorrido;
document.addEventListener('DOMContentLoaded', () => {
    recorrido = new Recorrido();
});
