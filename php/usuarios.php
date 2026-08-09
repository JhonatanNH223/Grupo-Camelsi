<?php
session_start();
$nivel = $_SESSION['nivel'];
$id_user = $_SESSION['id_usuario'];

if ($nivel != 'SADMIN' && $nivel != 'ADMIN') {
    session_unset();     // Elimina todas las variables
    session_destroy();   // Destruye la sesión
    header('Location: inicio.php');
    exit;
}

if ($nivel == null) {
    session_unset();     // Elimina todas las variables
    session_destroy();   // Destruye la sesión
    header('Location: ../index.html');
    exit;
}

$inactividad = 3600;

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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>

    <!-- Styles -->
    <link rel="stylesheet" href="../css/output.css">
    <link rel="stylesheet" href="../css/dataTables.dataTables.css">
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
                        <a href="estaciones.php" class="text-white hover:text-gray-200 px-3 py-2 text-sm font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Estaciones</a>
                        <a href="panelAlmacen.php" class="text-white hover:text-gray-200 px-3 py-2 text-sm font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Almacen</a>
                        <a href="usuarios.php" class="bg-white bg-opacity-90 text-camelsi-dark-blue px-4 py-2 text-sm font-medium transition duration-200 rounded-full">Usuarios</a>
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
                    <a href="estaciones.php" class="text-white hover:text-gray-200 block px-3 py-2 text-base font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Estaciones</a>
                    <a href="panelAlmacen.php" class="text-white hover:text-gray-200 block px-3 py-2 text-base font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Almacen</a>
                    <a href="usuarios.php" class="bg-white bg-opacity-90 text-camelsi-dark-blue block px-4 py-2 text-base font-medium transition duration-200 rounded-full mx-1">Usuarios</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Contenedor principal del header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Contenedor de título y botón volver -->
        <div class="flex justify-between items-center py-6">

            <!-- Título principal -->
            <div class="flex items-center space-x-4">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">Gestion de Usuarios</h1>
                    <!-- <p class="text-gray-500 text-sm">Gestión de usuarios</p> -->
                </div>
            </div>

        </div>
    </div>

    <!-- Contenido principal -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Regristro de recorrido -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 border border-gray-100">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0">
                <div class="flex items-center space-x-4">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m0 0L9 7"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Registro de Usuarios</h2>
                        <!-- <p class="text-gray-500 text-sm">Gestiona los elementos del recorrido</p> -->
                    </div>
                </div>

                <button id="btnAgregar" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span>Nuevo Elemento</span>
                </button>
            </div>
        </div>


        <!-- Tabla -->
        <div id="centro_dato" class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table id="tabla-usuarios" class="min-w-full">
                        <thead class="bg-gradient-to-r from-camelsi-blue to-camelsi-dark-blue shadow-lg">
                            <!-- Encabezado de la tabla -->
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">#</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Apellido</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Username</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Nivel</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-white uppercase tracking-wider">Acciones</th>

                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Los datos se cargarán dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div id="loading" class="text-center py-12 hidden">
            <div class="inline-block w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-4"></div>
            <p class="text-gray-600">Cargando datos...</p>
        </div>

        <!-- Mensaje cuando no hay resultados -->
        <div id="sinResultados" class="text-center py-12 hidden">
            <div class="animate-bounce-in">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-xl font-medium text-gray-900 mb-2">No se encontraron registros</h3>
                <p class="text-gray-500 mb-4">Comienza agregando un nuevo Usuario.</p>
            </div>
        </div>
    </main>


    <!-- Modal para agregar/editar recorrido -->
    <div id="modalUsuarios" class="modal">
        <div class="modal-content">
            <!-- Encavezado del modal -->
            <div class="flex justify-between items-center mb-6">
                <h3 id="tituloModal" class="text-2xl font-bold text-gray-900">Nuevo Registro de Usuario</h3>
                <button id="btnCerrarModal" class="text-gray-400 hover:text-gray-600 transition-colors p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Formulario para recorrido -->
            <form id="formUsuarios" class="space-y-6">
                <input type="hidden" id="Id">

                <!-- Información básica -->
                <div class="flex flex-wrap gap-6">
                    <div class="w-full">
                        <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-2">Nombre *</label>
                        <input type="text" id="nombre" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" placeholder="Ej: Noe" required>
                    </div>

                    <div class="w-full">
                        <label for="apellido" class="block text-sm font-semibold text-gray-700 mb-2">Apellido *</label>
                        <input type="text" id="apellido" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" required placeholder="Ej. Higuera">
                    </div>

                    <div class="w-full">
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">Username *</label>
                        <input type="text" id="username" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" required placeholder="Ej. Noe_10">
                    </div>

                    <div class="w-full">
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password *</label>
                        <input type="password" id="password"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" required
                            placeholder="Ej. 1234...">

                        <label for="checkPassword" class="label_password flex items-center gap-2 mt-4 cursor-pointer text-sm text-gray-700">
                            <input type="checkbox" id="checkPassword"
                                class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out">
                            <span>¿Desea cambiar la contraseña?</span>
                        </label>
                    </div>

                </div>

                <div id="nivel_usr" class="w-full">
                    <label for="nivel" class="block text-sm font-semibold text-gray-700 mb-2">Nivel *</label>
                    <select id="nivel" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" required>
                        <option value="">-- NIVEL --</option>
                        <option class="selectADMIN" value="ADMIN">ADMIN</option>
                        <option value="USUARIO">USUARIO</option>
                    </select>
                </div>


                <!-- Botones -->
                <div class="flex flex-wrap items-stretch gap-x-3 gap-y-3 pt-4">
                    <button type="submit" id="btnGuardar" class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg">
                        <span class="btn-text">Guardar Registro</span>
                    </button>
                    <button type="button" id="btnCancelar" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-xl transition-colors">
                        Cancelar
                    </button>
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
                ¿Estás seguro de que deseas eliminar el usuario  <strong id="nombreEstacionEliminar"></strong>?
            </p>

            <div class="flex flex-wrap items-stretch gap-x-3 gap-y-3 pt-4">
                <button id="btnConfirmarEliminar" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                    <span class="btn-text">Eliminar</span>
                    <!-- <div class="loading hidden ml-2"></div> -->
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
<script src="../js/dataTables.js"></script>
<script src="../js/JMenu.js"></script>
<script src="../js/JUsuarios.js"></script>

<script>
    /* Constante del id de la estacion, de php a js */
    const nivelphp = <?= json_encode($nivel) ?>;
    const id_userphp = <?= json_encode($id_user) ?>;
</script>

</html>