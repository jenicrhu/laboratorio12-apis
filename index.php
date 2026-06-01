<?php
// index.php - Consulta de Múltiples Ciudades con Validación y Tabla
$apiKey = getenv('WEATHER_API_KEY'); // Ejercicio 5: Variable de entorno [cite: 36]
$error_msg = "";
$resultados = [];

if (isset($_GET['ciudades'])) {
    $input = trim($_GET['ciudades']);
    
    // Ejercicio 2: Validación en Backend [cite: 33]
    if (empty($input)) {
        $error_msg = "Error: El campo de búsqueda no puede estar vacío.";
    } else {
        $ciudades = array_map('trim', explode(',', $input));
        
        foreach ($ciudades as $ciudad) {
            if (empty($ciudad)) continue;

            $url = "https://api.openweathermap.org/data/2.5/weather?q=" 
                    . urlencode($ciudad) . "&appid=" . $apiKey . "&units=metric&lang=es";

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_HTTPHEADER => ['Accept: application/json']
            ]);

            $response = curl_exec($ch);
            $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_err = curl_error($ch);
            curl_close($ch);

            // Ejercicio 2: Manejo de errores HTTP [cite: 33]
            if ($curl_err) {
                $resultados[] = ['ciudad' => $ciudad, 'error' => "Error de conexión: $curl_err"];
            } elseif ($status === 401) {
                $resultados[] = ['ciudad' => $ciudad, 'error' => "API Key inválida (Error 401)."];
            } elseif ($status === 404) {
                $resultados[] = ['ciudad' => $ciudad, 'error' => "Ciudad no encontrada (Error 404)."];
            } elseif ($status >= 500) {
                $resultados[] = ['ciudad' => $ciudad, 'error' => "Error del servidor externo (Error $status)."];
            } elseif ($status !== 200) {
                $resultados[] = ['ciudad' => $ciudad, 'error' => "Error inesperado ($status)."];
            } else {
                $data = json_decode($response, true);
                $resultados[] = [
                    'status' => 'success',
                    'ciudad' => $data['name'],
                    'temp' => $data['main']['temp'],
                    'humidity' => $data['main']['humidity'],
                    'desc' => ucfirst($data['weather'][0]['description'])
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Laboratorio 12 - Clima Múltiple</title>
    <style>
        body { font-family: sans-serif; margin: 40px; background-color: #f4f6f9; }
        .container { max-width: 800px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 0 auto; }
        input[type="text"] { width: 70%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 8px 15px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .error { color: #721c24; background: #f8d7da; padding: 10px; margin-top: 10px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #eef2f5; }
    </style>
</head>
<body>
<div class="container">
    <h2>🌦️ Consulta de Clima Múltiple (PHP)</h2>
    <p>
        <a href="index.php">Actualizar Buscador</a> | 
        <a href="usuarios.html">Ir a Ejercicio 3 (Usuarios Fetch)</a>
    </p>
    
    <form method="GET">
        <input type="text" name="ciudades" placeholder="Ej: Lima, Madrid, Tokyo" value="<?= htmlspecialchars($_GET['ciudades'] ?? '') ?>">
        <button type="submit">Buscar</button>
    </form>

    <?php if (!empty($error_msg)): ?>
        <div class="error"><?= $error_msg ?></div>
    <?php endif; ?>

    <?php if (!empty($resultados)): ?>
        <table>
            <thead>
                <tr>
                    <th>Ciudad</th>
                    <th>Temperatura</th>
                    <th>Humedad</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultados as $res): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($res['ciudad']) ?></strong></td>
                        <?php if (isset($res['status']) && $res['status'] === 'success'): ?>
                            <td><?= $res['temp'] ?> °C</td>
                            <td><?= $res['humidity'] ?>%</td>
                            <td><?= $res['desc'] ?></td>
                            <td style="color: green;">✔ 200 OK</td>
                        <?php else: ?>
                            <td colspan="3" style="color: gray; font-style: italic;">No disponible</td>
                            <td style="color: red;">❌ <?= htmlspecialchars($res['error']) ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?> </tbody>
        </table>
    <?php endif; ?> </div>
</body>
</html>