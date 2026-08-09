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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Menú CAMELSI</title>


  <!-- Styles -->
  <link rel="stylesheet" href="../css/output.css">

</head>

<body class="bg-gray-100">

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
            <a href="inicio.php" class="bg-white bg-opacity-90 text-camelsi-dark-blue px-4 py-2 text-sm font-medium transition duration-200 rounded-full">Inicio</a>
            <a href="estaciones.php" class="text-white hover:text-gray-200 px-3 py-2 text-sm font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Estaciones</a>
            <a href="panelAlmacen.php" class="text-white hover:text-gray-200 px-3 py-2 text-sm font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Almacen</a>
            <?php if ($nivel == 'SADMIN' || $nivel == 'ADMIN') { ?>
              <a href="usuarios.php" class="text-white hover:text-gray-200 px-3 py-2 text-sm font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Usuarios</a><?php } ?>
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
          <a href="inicio.php" class="bg-white bg-opacity-90 text-camelsi-dark-blue block px-4 py-2 text-base font-medium transition duration-200 rounded-full mx-1">Inicio</a>
          <a href="estaciones.php" class="text-white hover:text-gray-200 block px-3 py-2 text-base font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Estaciones</a>
          <a href="almacen.php" class="text-white hover:text-gray-200 block px-3 py-2 text-base font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Almacen</a>
          <?php if ($nivel == 'SADMIN' || $nivel == 'ADMIN') { ?>
            <a href="usuarios.php" class="text-white hover:text-gray-200 block px-3 py-2 text-base font-medium transition duration-200 rounded-md hover:bg-white hover:bg-opacity-10">Usuarios</a><?php } ?>
        </div>
      </div>
    </nav>
  </header>

  <!-- Contenido -->
  <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="text-center bg-white rounded-lg shadow-md p-8">
      <h2 class="text-3xl font-bold text-gray-900 mb-4">CAMELSI</h2>
      <p class="text-lg text-gray-600 mb-8">Consultoria en Medicina y Laboratorio de Seguridad Industrial</p>
    </div>

    <!-- JavaScript -->
    <script src="../js/JMenu.js"></script>
</body>

</html>