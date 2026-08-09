<?php
session_start();
$nivel = $_SESSION['nivel'];


if ($nivel == null) {
    session_unset();     // Elimina todas las variables
    session_destroy();   // Destruye la sesión
    header('Location: ../index.html');
    exit;
}

$inactividad = 3600; // 600 segundos = 10 minutos

if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > $inactividad) {
    session_unset();     // Elimina todas las variables
    session_destroy();   // Destruye la sesión
    header("Location: ../index.html");
    exit();
} else {
    $_SESSION['ultimo_acceso'] = time(); // Reinicia contador
}

?>

<!DOCTYPE html>
<html lang="es">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Estaciones</title>

    <link rel="stylesheet" href="../css/output.css">
    <link rel="stylesheet" href="../css/jquery.modal.min.css">

</head>

<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">


    <!-- Menu -->
    <header>
        <nav class="bg-gradient-to-r from-camelsi-blue to-camelsi-dark-blue shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3">
                        <img src="../svg/logo1.png" class="w-30 h-20" alt="Logo CAMELSI" />
                    </div>

                    <!-- Menú Desktop -->
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="inicio.php" class="text-white hover:text-gray-200 px-3 py-2 text-sm font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Inicio</a>
                        <a href="estaciones.php" class="bg-white bg-opacity-90 text-camelsi-dark-blue px-4 py-2 text-sm font-medium transition duration-200 rounded-full">Estaciones</a>
                        <a href="panelAlmacen.php" class="text-white hover:text-gray-200 px-3 py-2 text-sm font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Almacen</a>
                        <?php if ($nivel == 'SADMIN' || $nivel == 'ADMIN') { ?><a href="usuarios.php" class="text-white hover:text-gray-200 px-3 py-2 text-sm font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Usuarios</a><?php } ?>
                    </div>

                    <!-- Botón Menú Mobile -->
                    <div class="md:hidden flex items-center">
                        <button id="mobile-menu-button" class="text-white hover:text-gray-200 focus:outline-none">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Menú Mobile -->
            <div id="mobile-menu" class="md:hidden hidden">
                <div class="px-2 pt-2 pb-3 space-y-1 bg-camelsi-dark-blue">
                    <a href="inicio.php" class="text-white hover:text-gray-200 block px-3 py-2 text-base font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Inicio</a>
                    <a href="estaciones.php" class="bg-white bg-opacity-90 text-camelsi-dark-blue block px-4 py-2 text-base font-medium transition duration-200 rounded-full mx-1">Estaciones</a>
                    <a href="panelAlmacen.php" class="text-white hover:text-gray-200 block px-3 py-2 text-base font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Almacen</a>
                    <?php if ($nivel == 'SADMIN' || $nivel == 'ADMIN') { ?><a href="usuarios.php" class="text-white hover:text-gray-200 block px-3 py-2 text-base font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Usuarios</a><?php } ?>
                </div>
            </div>
        </nav>
    </header>


    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div class="flex items-center space-x-4">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                        Sistema de Estaciones
                    </h1>
                    <p class="text-gray-500 text-sm">Panel de Control</p>
                </div>
            </div>
        </div>
    </div>


    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Estaciones -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 animate-fade-in">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Estaciones</p>
                        <p id="totalEstaciones" class="text-3xl font-bold text-gray-900">0</p>
                    </div>
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Estaciones Activas -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 animate-fade-in" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Activas</p>
                        <p id="estacionesActivas" class="text-3xl font-bold text-green-600">0</p>
                    </div>
                    <div class="bg-gradient-to-r from-green-500 to-green-600 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Estaciones Inactivas -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 animate-fade-in" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Inactivas</p>
                        <p id="estacionesInactivas" class="text-3xl font-bold text-red-600">0</p>
                    </div>
                    <div class="bg-gradient-to-r from-red-500 to-red-600 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Mantenimiento -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 animate-fade-in" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Mantenimiento</p>
                        <p id="estacionesMantenimiento" class="text-3xl font-bold text-yellow-600">0</p>
                    </div>
                    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Controles y Filtros -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 border border-gray-100">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0">
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 w-full lg:w-auto">
                    <div class="relative">
                        <select id="filtroTipo" class="appearance-none bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 min-w-[160px]">
                            <option value="">Todos los tipos</option>
                            <option value="Principal">Principal</option>
                            <option value="Secundaria">Secundaria</option>
                            <option value="Emergencia">Emergencia</option>
                            <option value="Comunicaciones">Comunicaciones</option>
                            <option value="Laboratorio">Laboratorio</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="relative">
                        <select id="filtroEstado" class="appearance-none bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 min-w-[160px]">
                            <option value="">Todos los estados</option>
                            <option value="Activa">Activa</option>
                            <option value="Inactiva">Inactiva</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="relative">
                        <input type="text" id="barraBusqueda" placeholder="Buscar estaciones..." class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 pl-10 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 flex items-center px-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <button id="limpiarBusqueda" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 hidden">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <button id="limpiarFiltros" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-3 px-4 rounded-xl transition-colors flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span>Limpiar</span>
                    </button>
                </div>


                
                <button id="btnAgregarEstacion" class=" <?php if ($nivel != 'SADMIN' && $nivel != 'ADMIN') echo 'hidden'; ?>  bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span>Nueva Estación</span>
                </button>

            </div>

            <!-- Indicadores de filtros activos -->
            <div id="filtrosActivos" class="mt-4 flex flex-wrap gap-2 hidden">
                <span class="text-sm text-gray-600">Filtros activos:</span>
            </div>

            <!-- Resultados de búsqueda -->
            <div id="resultadosBusqueda" class="mt-4 text-sm text-gray-600 hidden">
                <span id="textoResultados"></span>
            </div>
        </div>

        <!-- Grid de Estaciones -->
        <div id="gridEstaciones" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Las estaciones se cargarán dinámicamente desde la base de datos -->
        </div>

        <!-- Loading -->
        <div id="loading" class="text-center py-12">
            <div class="loading mx-auto mb-4"></div>
            <p class="text-gray-600">Cargando estaciones...</p>
        </div>

        <!-- Mensaje cuando no hay resultados -->
        <div id="sinResultados" class="text-center py-12 hidden">
            <div class="animate-bounce-in">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="text-xl font-medium text-gray-900 mb-2">No se encontraron estaciones</h3>
                <p class="text-gray-500 mb-4">Intenta ajustar los filtros o términos de búsqueda.</p>
                <button id="limpiarTodo" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                    Mostrar todas las estaciones
                </button>
            </div>
        </div>
    </main>

    <!-- Modal para agregar/editar estación -->
    <div id="modalEstacion" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h3 id="tituloModal" class="text-2xl font-bold text-gray-900">Nueva Estación</h3>
                <button id="btnCerrarModal" class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="formEstacion" class="space-y-6">
                <input type="hidden" id="estacionId" value="">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Nombre de la Estación *
                    </label>
                    <input type="text" id="nombreEstacion" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" placeholder="Ej: Estación Norte" required>
                    <p class="text-xs text-gray-500 mt-1">Mínimo 3 caracteres</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Descripción *
                    </label>
                    <textarea rows="4" id="descripcionEstacion" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none" placeholder="Describe la función y características de la estación..." required></textarea>
                    <p class="text-xs text-gray-500 mt-1">Mínimo 10 caracteres</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tipo de Estación *
                    </label>
                    <div class="relative">
                        <select id="tipoEstacion" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 appearance-none" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="Principal">Principal</option>
                            <option value="Secundaria">Secundaria</option>
                            <option value="Emergencia">Emergencia</option>
                            <option value="Comunicaciones">Comunicaciones</option>
                            <option value="Laboratorio">Laboratorio</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Estado
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <label class="flex items-center p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" name="estado" value="Activa" class="sr-only" checked>
                            <div class="w-4 h-4 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-sm font-medium">Activa</span>
                        </label>
                        <label class="flex items-center p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" name="estado" value="Inactiva" class="sr-only">
                            <div class="w-4 h-4 bg-red-500 rounded-full mr-2"></div>
                            <span class="text-sm font-medium">Inactiva</span>
                        </label>
                        <label class="flex items-center p-3 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" name="estado" value="Mantenimiento" class="sr-only">
                            <div class="w-4 h-4 bg-yellow-500 rounded-full mr-2"></div>
                            <span class="text-sm font-medium">Mant.</span>
                        </label>
                    </div>

                </div>

                <div>

                    <label for="imgLogoEstacion" class="block text-sm font-semibold text-gray-700 mb-2">Imagen de la estación *</label>
                    <input type="file" id="imgLogoEstacion" name="imgLogoEstacion" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                    <p class="text-xs text-gray-500 mt-1">Solo se permiten imágenes JPEG y PNG (máx. 2MB)</p>

                </div>

                <div class="flex flex-wrap items-stretch gap-x-3 gap-y-3 space-x-3 pt-4">
                    <button type="submit" id="btnGuardar" class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg">
                        <span class="btn-text">Crear Estación</span>
                    </button>
                    <button type="button" id="btnCancelar" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación para eliminar -->
    <div id="modalConfirmacion" class="modal modal-confirmacion">
        <div class="modal-content">
            <div class="flex items-center mb-4">
                <div class="bg-red-100 p-3 rounded-full mr-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Confirmar eliminación</h3>
                    <p class="text-sm text-gray-600">Esta acción no se puede deshacer</p>
                </div>
            </div>

            <p class="text-gray-700 mb-6">
                ¿Estás seguro de que deseas eliminar la estación <strong id="nombreEstacionEliminar"></strong>?
            </p>

            <div class="flex flex-wrap items-stretch gap-x-3 gap-y-3 pt-4">
                <button id="btnConfirmarEliminar" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                    <span class="btn-text">Eliminar</span>
                </button>
                <button id="btnCancelarEliminar" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2">
        <!-- Los toasts aparecerán aquí -->
    </div>



</body>

<!-- jQuery -->
<script src="../js/jquery-3.7.1.min.js"></script>
<script src="../js/jquery.modal.min.js"></script>
<script src="../js/JMenu.js"></script>
<script src="../js/JEstaciones_fun.js"></script>


<script>
    /* Constante del id de la estacion, de php a js */
    const nivelphp = <?= json_encode($nivel) ?>;
</script>


</html>