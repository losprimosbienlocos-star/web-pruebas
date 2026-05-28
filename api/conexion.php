<?php

function load_local_env()
{
    $root = dirname(__DIR__);
    $files = [$root . '/.env.local', $root . '/.env'];

    foreach ($files as $file) {
        if (!is_readable($file)) {
            continue;
        }

        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");

            if ($name !== '' && getenv($name) === false) {
                putenv($name . '=' . $value);
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

load_local_env();

function env_value($name, $default = '')
{
    $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name) ?: $default;

    return trim((string) $value, " \t\n\r\0\x0B\"'");
}

function normalize_supabase_url($value)
{
    $url = trim((string) $value, " \t\n\r\0\x0B\"'");

    if (stripos($url, 'SUPABASE_URL=') === 0) {
        $url = substr($url, strlen('SUPABASE_URL='));
    }

    $url = trim($url, " \t\n\r\0\x0B\"'");
    $url = preg_replace('#/rest/v1/?$#', '', $url);
    $url = rtrim($url, '/');

    if ($url !== '' && !preg_match('#^https?://#i', $url)) {
        $url = 'https://' . $url;
    }

    return $url;
}

function supabase_config()
{
    $url = normalize_supabase_url(env_value('SUPABASE_URL'));
    $key = env_value('SUPABASE_SERVICE_ROLE_KEY')
        ?: env_value('SUPABASE_ANON_KEY')
        ?: env_value('SUPABASE_PUBLISHABLE_KEY');

    if ($url === '' || $key === '') {
        http_response_code(500);
        die('Faltan SUPABASE_URL y SUPABASE_PUBLISHABLE_KEY en las variables de entorno.');
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        http_response_code(500);
        die('SUPABASE_URL no tiene un formato valido.');
    }

    return [$url, $key];
}

function supabase_request($path, $method = 'GET', $body = null, $headers = [])
{
    [$url, $key] = supabase_config();
    $endpoint = $url . '/rest/v1/' . ltrim($path, '/');

    $defaultHeaders = [
        'apikey: ' . $key,
        'Authorization: Bearer ' . $key,
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    $ch = curl_init($endpoint);
    if ($ch === false) {
        http_response_code(500);
        die('No se pudo inicializar la conexion con Supabase.');
    }

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array_merge($defaultHeaders, $headers),
        CURLOPT_TIMEOUT => 20,
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
    }

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $status >= 400) {
        http_response_code(500);
        $message = $error ?: $response;
        die('Error consultando Supabase: ' . htmlspecialchars($message));
    }

    if ($response === '' || $status === 204) {
        return [];
    }

    return json_decode($response, true) ?: [];
}

function supabase_query_string($query)
{
    if (is_array($query)) {
        return http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    $query = trim((string) $query);
    if ($query === '') {
        return '';
    }

    $parts = [];
    foreach (explode('&', $query) as $pair) {
        [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
        $parts[] = rawurlencode($key) . '=' . rawurlencode($value);
    }

    return implode('&', $parts);
}

function supabase_table($table, $query = '')
{
    $queryString = supabase_query_string($query);

    return rawurlencode($table) . ($queryString !== '' ? '?' . $queryString : '');
}

function conectar()
{
    return new SupabaseCompat();
}

class SupabaseResult
{
    public $num_rows = 0;
    private $rows;
    private $index = 0;

    public function __construct($rows)
    {
        $this->rows = array_values($rows);
        $this->num_rows = count($this->rows);
    }

    public function fetch_assoc()
    {
        if ($this->index >= $this->num_rows) {
            return null;
        }

        return $this->rows[$this->index++];
    }
}

class SupabaseCompat
{
    public function query($sql)
    {
        if (stripos($sql, 'FROM ingenios') !== false) {
            return new SupabaseResult(supabase_request(
                supabase_table('ingenios', 'select=*&order=nombre_ingenios.asc')
            ));
        }

        if (stripos($sql, 'FROM cursos') !== false) {
            $tipo = 'Curso';
            if (preg_match("/tipo\s*=\s*'([^']+)'/i", $sql, $matches)) {
                $tipo = $matches[1];
            }

            return new SupabaseResult(supabase_request(
                supabase_table('cursos', 'select=*&tipo=eq.' . rawurlencode($tipo) . '&order=nombre_cursos.asc')
            ));
        }

        if (stripos($sql, 'FROM solicitudes_inscripcion') !== false) {
            $rows = supabase_request(
                supabase_table(
                    'solicitudes_inscripcion',
                    'select=*,ingenios(nombre_ingenios),cursos(nombre_cursos)&order=fecha_solicitud.desc'
                )
            );

            foreach ($rows as &$row) {
                $row['ingenio'] = $row['ingenios']['nombre_ingenios'] ?? null;
                $row['curso'] = $row['cursos']['nombre_cursos'] ?? null;
            }

            return new SupabaseResult($rows);
        }

        return new SupabaseResult([]);
    }

    public function prepare($sql)
    {
        return new SupabaseStatement($sql);
    }

    public function close()
    {
        return true;
    }
}

class SupabaseStatement
{
    public $error = '';
    private $sql;
    private $params = [];
    private $result;

    public function __construct($sql)
    {
        $this->sql = $sql;
    }

    public function bind_param($types, ...$params)
    {
        $this->params = $params;
        return true;
    }

    public function execute()
    {
        if (stripos($this->sql, 'INSERT INTO solicitudes_inscripcion') !== false) {
            $row = [
                'nombre_participante' => $this->params[0] ?? '',
                'cui_participante' => $this->params[1] ?? '',
                'puesto_participante' => $this->params[2] ?? '',
                'area_participante' => $this->params[3] ?? '',
                'correo' => $this->params[4] ?? '',
                'telefono' => $this->params[5] ?? '',
                'ingenio_id' => isset($this->params[6]) ? (int) $this->params[6] : null,
                'otro_ingenio' => $this->params[7] ?? null,
                'curso_id' => isset($this->params[8]) ? (int) $this->params[8] : null,
                'tipo_pago' => $this->params[9] ?? '',
            ];

            supabase_request(
                supabase_table('solicitudes_inscripcion'),
                'POST',
                [$row],
                ['Prefer: return=minimal']
            );

            return true;
        }

        if (stripos($this->sql, 'FROM cursos') !== false) {
            $ids = array_map('intval', $this->params);
            $this->result = supabase_request(
                supabase_table('cursos', 'select=nombre_cursos&id=in.(' . implode(',', $ids) . ')&order=nombre_cursos.asc')
            );

            return true;
        }

        if (stripos($this->sql, 'FROM ingenios') !== false) {
            $id = isset($this->params[0]) ? (int) $this->params[0] : 0;
            $this->result = supabase_request(
                supabase_table('ingenios', 'select=nombre_ingenios&id=eq.' . $id . '&limit=1')
            );

            return true;
        }

        return true;
    }

    public function get_result()
    {
        return new SupabaseResult($this->result ?? []);
    }

    public function close()
    {
        return true;
    }
}
?>
