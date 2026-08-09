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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>panel-Almacen</title>

    <!-- Styles -->
    <link rel="stylesheet" href="../css/output.css">

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
                        <a href="panelAlmacen.php" class="bg-white bg-opacity-90 text-camelsi-dark-blue px-4 py-2 text-sm font-medium transition duration-200 rounded-full">Almacen</a>
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
                    <a href="estaciones.php" class="text-white hover:text-gray-200 block px-3 py-2 text-base font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Estaciones</a>
                    <a href="panelAlmacen.php" class="bg-white bg-opacity-90 text-camelsi-dark-blue block px-4 py-2 text-base font-medium transition duration-200 rounded-full mx-1">Almacen</a>
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
                        Panel de Control del Almacen
                    </h1>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-6 mb-8">
            
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 animate-fade-in" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Almacen</p>
                        <p id="contadorAlmacen" class="text-3xl font-bold text-blue-500">0</p>
                    </div>
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-3 rounded-xl">
                        <!-- Heroicon sugerido: Office Building (puede representar un almacén) -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21V6l9-4 9 4v15M4.5 21v-6h15v6M9 21v-3h6v3" />
                        </svg>




                    </div>
                </div>
            </div>

            <!-- Estaciones Activas -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 animate-fade-in" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Tipos</p>
                        <p id="contadorTipos" class="text-3xl font-bold text-green-600">0</p>
                    </div>
                    <div class="bg-gradient-to-r from-green-500 to-green-600 p-3 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9 5 9-5-9-5-9 5zm0 6l9 5 9-5" />
                        </svg>

                    </div>
                </div>
            </div>


            <!-- Elementos de bajo stock -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 animate-fade-in" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Stock Bajo</p>
                        <p id="contadorBajoStock" class="text-3xl font-bold text-green-600">0</p>
                    </div>
                    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 p-3 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>

                    </div>
                </div>
            </div>


        </div>





        <!-- Estadisticas Más Recientes -->
         <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            
            <a href="almacen/almacen.php">
                <div class="bg-gradient-to-b from-white via-white to-blue-50 rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl  mb-2  font-bold text-gray-900">Elementos más recientes del Almacen</h2>

                            <!-- <p class="text-sm font-medium text-gray-600 mt-3">Elementos más recientes:</p> -->
                            <ul id="listAlmacen" class="mt-2 font-medium list-disc list-inside text-sm text-gray-700 space-y-1">
                            </ul>

                            <!-- Loading -->
                            <div id="loading" class="text-center py-12 hidden">
                                <div class="inline-block w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-4"></div>
                                <p class="text-gray-600">Cargando datos...</p>
                            </div>

                            <!-- Mensaje cuando no hay resultados -->
                            <div id="sinResultados" class="text-center py-3 hidden">
                                <div class="animate-bounce-in">
                                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-base font-medium text-gray-900 mb-2">No se encontraron registros</h3>
                                    <p class="text-gray-500 mb-4 text-sm">Comienza agregando nuevos elementos al Almacen.</p>
                                </div>
                            </div>
                            
                        </div>

                    </div>
                </div>
            </a>

            <a <?php if($nivel == "SADMIN" || $nivel == "ADMIN"){ ?> href="almacen/tipo.php" <?php }?> >
                <div class="bg-gradient-to-b from-white via-white to-green-50 rounded-2xl shadow-lg p-6 border border-gray-100 hover:shadow-xl transition-all duration-300 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl mb-2 font-bold text-gray-900">Tipos</h2>
                            <p class="text-sm text-gray-600 mb-1">. </p>
                            <p class="text-sm font-medium text-gray-600 mt-3">Elementos más recientes:</p>
                            <ul id="listTipo" class="mt-2 font-medium list-disc list-inside text-sm text-gray-700 space-y-1"></ul>


                            <!-- Loading -->
                            <div id="loading2" class="text-center py-12 hidden">
                                <div class="inline-block w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-4"></div>
                                <p class="text-gray-600">Cargando datos...</p>
                            </div>

                            <!-- Mensaje cuando no hay resultados -->
                            <div id="sinResultados2" class="text-center py-3 hidden">
                                <div class="animate-bounce-in">
                                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-base font-medium text-gray-900 mb-2">No se encontraron registros</h3>
                                    <p class="text-gray-500 mb-4 text-sm">Comienza agregando nuevos elementos al Almacen.</p>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </a>

            <a href="almacen/almacen.php">
                <div class="bg-gradient-to-b from-white via-white to-yellow-50 rounded-2xl shadow-lg p-6 border border-yellow-100 hover:shadow-xl transition-all duration-300 animate-fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl  mb-2  font-bold text-gray-900">Elementos con stock bajo en el Almacen</h2>

                            <p class="text-sm font-medium text-gray-600 mt-3">Elementos más recientes:</p>
                            <ul id="listStockAlmacen" class="mt-2 font-medium list-disc list-inside text-sm text-gray-700 space-y-1">
                            </ul>

                            <!-- Loading -->
                            <div id="loading" class="text-center py-12 hidden">
                                <div class="inline-block w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-4"></div>
                                <p class="text-gray-600">Cargando datos...</p>
                            </div>

                            <!-- Mensaje cuando no hay resultados -->
                            <div id="sinResultados" class="text-center py-3 hidden">
                                <div class="animate-bounce-in">
                                    <svg class="mx-auto h-8 w-8 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-base font-medium text-gray-900 mb-2">No se encontraron registros</h3>
                                    <p class="text-gray-500 mb-4 text-sm">Comienza agregando nuevos elementos al Almacen.</p>
                                </div>
                            </div>
                            
                        </div>

                    </div>
                </div>
            </a>

        </div>






    </main>

</body>

<!-- JavaScript -->
<script src="../js/JMenu.js"></script>
<script src="../js/JPanelAlmacen.js"></script>

</html>