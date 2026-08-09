<?php
include("../../Conect/conect.php"); // aquí ya tienes $pdo configurado con PDO
session_start();

$username = trim($_POST['username']);
$password = $_POST['password'];

try {
    // Consulta solo por el usuario (estatus = 1)
    $sql = "SELECT * FROM usuarios WHERE username = :username AND estatus = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);

    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($datos) {
        // Validar contraseña hasheada
        if (password_verify($password, $datos['password'])) {
            // Guardar variables de sesión
            $_SESSION['username'] = $datos['username'];
            $_SESSION['nivel'] = $datos['nivel'];
            $_SESSION['id_usuario'] = $datos['id_usuario'];
            $_SESSION['ultimo_acceso'] = time();

            echo "1"; // Login exitoso
        } else {
            echo "0"; // Contraseña incorrecta
            session_destroy();
        }
    } else {
        echo "0"; // Usuario no encontrado o inactivo
        session_destroy();
    }

} catch (PDOException $e) {
    echo "3"; // Error en la consulta
}
