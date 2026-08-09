<?php
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include("../../Conect/conect.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST' && isset($_POST['_method'])) {
    $override = strtoupper($_POST['_method']);
    if (in_array($override, ['PUT', 'DELETE'])) {
        $method = $override;
    }
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
} elseif (in_array($method, ['POST', 'PUT']) && !empty($_POST)) {
    $input = $_POST;
} else {
    $input = json_decode(file_get_contents('php://input'), true);
}

switch ($method) {
    case 'GET':
        obtenerEstaciones();
        break;
    case 'POST':
        crearEstacion($input);
        break;
    case 'PUT':
        actualizarEstacion($input);
        break;
    case 'DELETE':
        eliminarEstacion($input);
        break;
    case 'OPTIONS':
        // Preflight request
        http_response_code(200);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
        break;
}

function obtenerEstaciones()
{
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT * FROM estaciones ORDER BY id");
        $estaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $estaciones
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al obtener estaciones: ' . $e->getMessage()]);
    }
}

function crearEstacion($data)
{
    global $pdo;

    date_default_timezone_set('America/Mexico_City');
    $fecha = date('Y-m-d');




    // Validaciones
    if (empty($data['nombre']) || strlen($data['nombre']) < 3) {
        http_response_code(400);
        echo json_encode(['error' => 'El nombre debe tener al menos 3 caracteres']);
        return;
    }

    if (empty($data['descripcion']) || strlen($data['descripcion']) < 10) {
        http_response_code(400);
        echo json_encode(['error' => 'La descripción debe tener al menos 10 caracteres']);
        return;
    }

    /* $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $nombreArchivo = uniqid("logo_", true); // totalmente independiente

    $rutaDestino = '../../svg/imgLogos/' . $nombreArchivo . "." . $extension;

    if (move_uploaded_file($file['tmp_name'], $rutaDestino)) {
        $data['imgLogo'] = $nombreArchivo . "." . $extension;
    } */






    try {

        $stmt1 = $pdo->prepare("SELECT * FROM estaciones WHERE nombre = ?");
        $stmt1->execute([
            $data['nombre']
        ]);

        if ($stmt1->fetch(PDO::FETCH_ASSOC)) {
            http_response_code(500);
            echo json_encode(['error' => 'Ya existe otra estación con ese nombre']);
            return;
        }




         $file = isset($_FILES['imgLogoEstacion']) ? $_FILES['imgLogoEstacion'] : null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $nombreArchivo = uniqid("logo_", true); // totalmente independiente 
            $rutaDestino = '../../svg/imgLogos/' . $nombreArchivo . "." . $extension;   

            if (move_uploaded_file($file['tmp_name'], $rutaDestino)) {
                $data['imgLogo'] = $nombreArchivo . "." . $extension;
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al subir la imagen lol: ' . $nombreArchivo. '. Extensión: ' . $extension. ' Ruta destino: ' . $rutaDestino]);
                return;
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'No se ha proporcionado una imagen válida']);
            return;
        }




        $stmt = $pdo->prepare("INSERT INTO estaciones (nombre, descripcion, tipo, estado, fecha, equipos, imgLogo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['nombre'],
            $data['descripcion'],
            $data['tipo'],
            $data['estado'],
            isset($data['fecha']) ? $data['fecha'] : $fecha,
            isset($data['equipos']) ? $data['equipos'] : 0,
            isset($data['imgLogo']) ? $data['imgLogo'] : null,
        ]);

        $id = $pdo->lastInsertId();

        // Obtener la estación recién creada
        $stmt = $pdo->prepare("SELECT * FROM estaciones WHERE id = ?");
        $stmt->execute([$id]);
        $estacion = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Estación creada exitosamente',
            'data' => $estacion
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear estación: ' . $e->getMessage()]);
    }
}

function actualizarEstacion($data)
{
    global $pdo;

    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de estación requerido']);
        return;
    }

    if (empty($data['nombre']) || strlen($data['nombre']) < 3) {
        http_response_code(400);
        echo json_encode(['error' => 'El nombre debe tener al menos 3 caracteres']);
        return;
    }

    if (empty($data['descripcion']) || strlen($data['descripcion']) < 10) {
        http_response_code(400);
        echo json_encode(['error' => 'La descripción debe tener al menos 10 caracteres']);
        return;
    }

    try {
        $stmt1 = $pdo->prepare("SELECT * FROM estaciones WHERE nombre = ? AND id != ?");
        $stmt1->execute([
            $data['nombre'],
            $data['id']
        ]);
        $existe = $stmt1->fetch(PDO::FETCH_ASSOC);

        if ($existe) {
            http_response_code(400);
            echo json_encode(['error' => 'Ya existe otra estación con ese nombre']);
            return;
        }

        // Actualizar estación
        if (isset($_FILES['imgLogoEstacion']) && $_FILES['imgLogoEstacion']['error'] === UPLOAD_ERR_OK) {

            $stmt = $pdo->prepare("SELECT imgLogo FROM estaciones WHERE id = ?");
            $stmt->execute([$data['id']]);
            $estacion = $stmt->fetch(PDO::FETCH_ASSOC);

            $file = isset($_FILES['imgLogoEstacion']) ? $_FILES['imgLogoEstacion'] : null;
            if ($file && $file['error'] === UPLOAD_ERR_OK) {

                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $nombreArchivo = uniqid("logo_", true); // totalmente independiente 
                $rutaDestino = '../../svg/imgLogos/' . $nombreArchivo . "." . $extension;   
                
                if (move_uploaded_file($file['tmp_name'], $rutaDestino)) {
                    $data['imgLogo'] = $nombreArchivo . "." . $extension;
                    

                    $stmt = $pdo->prepare("UPDATE estaciones SET nombre = ?, descripcion = ?, tipo = ?, estado = ?, imgLogo = ? WHERE id = ?");
                    $stmt->execute([
                        $data['nombre'],
                        $data['descripcion'],
                        $data['tipo'],
                        $data['estado'],
                        $data['imgLogo'],
                        $data['id']
                    ]);

                    if ($estacion && !empty($estacion['imgLogo'])) {
                        $rutaImagen = '../../svg/imgLogos/' . $estacion['imgLogo'];
                        if (file_exists($rutaImagen)) {
                            unlink($rutaImagen);
                        }
                    }

                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Error al subir la imagen lol: ' . $nombreArchivo. '. Extensión: ' . $extension. ' Ruta destino: ' . $rutaDestino]);
                    return;
                }
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'No se ha proporcionado una imagen válida']);
                return;
            }



        } else {
            $stmt = $pdo->prepare("UPDATE estaciones SET nombre = ?, descripcion = ?, tipo = ?, estado = ? WHERE id = ?");
            $stmt->execute([
                $data['nombre'],
                $data['descripcion'],
                $data['tipo'],
                $data['estado'],
                $data['id']
            ]);
        }

        // Obtener la estación actualizada
        $stmt = $pdo->prepare("SELECT * FROM estaciones WHERE id = ?");
        $stmt->execute([$data['id']]);
        $estacion = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'message' => 'Estación actualizada exitosamente',
            'data' => $estacion
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar estación: ' . $e->getMessage()]);
    }
}


function eliminarEstacion($data)
{
    global $pdo;

    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de estación requerido']);
        return;
    }

    try {

        $stmt = $pdo->prepare("SELECT imgLogo FROM estaciones WHERE id = ?");
        $stmt->execute([$data['id']]);
        $estacion = $stmt->fetch(PDO::FETCH_ASSOC);
        

        $stmt = $pdo->prepare("DELETE FROM estaciones WHERE id = ?");
        $stmt->execute([$data['id']]);

        if ($stmt->rowCount() > 0) {

            if ($estacion && !empty($estacion['imgLogo'])) {
                $rutaImagen = '../../svg/imgLogos/' . $estacion['imgLogo'];
                if (file_exists($rutaImagen)) {
                    unlink($rutaImagen);
                }
            }
            echo json_encode([
                'success' => true,
                'message' => 'Estación eliminada exitosamente'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Estación no encontrada']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar estación: ' . $e->getMessage()]);
    }
}
