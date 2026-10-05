<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servidor = "localhost";
$usuario = "root";
$password = "";
$base_datos = "agrisense";

$conexion = new mysqli($servidor, $usuario, $password, $base_datos);

if ($conexion->connect_error) {
    echo json_encode(["exito" => false, "mensaje" => "Error de conexión"]);
    exit();
}

$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input, true);

$id_usuario = $data['id_usuario'] ?? 1;
$id_cultivo = $data['id_cultivo'] ?? 1;
$temperatura = $data['temperatura'] ?? null;
$humedad_aire = $data['humedad_aire'] ?? null;
$humedad_suelo = $data['humedad_suelo'] ?? null;

if ($temperatura !== null && $humedad_aire !== null && $humedad_suelo !== null) {
    // Al no incluir la columna 'fecha' explícitamente, MySQL usa el 'current_timestamp()' automático que ya tienes configurado
    $sql = "INSERT INTO mediciones (id_usuario, id_cultivo, temperatura, humedad_aire, humedad_suelo) 
            VALUES ('$id_usuario', '$id_cultivo', '$temperatura', '$humedad_aire', '$humedad_suelo')";
    
    if ($conexion->query($sql) === TRUE) {
        echo json_encode(["exito" => true, "mensaje" => "Datos guardados correctamente"]);
    } else {
        echo json_encode(["exito" => false, "mensaje" => "Error SQL: " . $conexion->error]);
    }
} else {
    echo json_encode(["exito" => false, "mensaje" => "Faltan datos de sensores"]);
}

$conexion->close();
?>