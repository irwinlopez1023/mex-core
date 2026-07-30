<?php
require_once __DIR__ . '/vendor/autoload.php';

use Irwinlopez1023\MexCore\MexCore;

echo "==================================" . PHP_EOL;
echo "  MexCore - Prueba de Personas" . PHP_EOL;
echo "==================================" . PHP_EOL . PHP_EOL;

$personas = [
    ['Nombre simple',            'VARG740228HSPLSN07', 'JUAN CARLOS',        'VALENCIA', 'RAMIREZ'],
    ['Con conector DEL',         'HEGG560427MVZRRL04', 'MARIA DEL ROCIO',    'HERNANDEZ', 'GOMEZ'],
    ['Con conector DE JESUS',    'MORL850912HDFRRN05', 'JOSE DE JESUS',      'MORALES',  'RIVERA'],
    ['Con conector DE LOS',      'GODE561231HASLRN09', 'MARIA DE LOS ANGELES', 'GONZALEZ', 'DE LA CRUZ'],
    ['Nombre unico',             'CCCC000101HCCXXX00', 'MARIA',              'SANTOS',   'PEREZ'],
    ['Abreviatura MA. (con punto)', 'AAAA000101HBCXXX00', 'MA. GUADALUPE',   'TORRES',  'FLORES'],
    ['Abreviatura J. (sin punto)',  'BBBB000101HBSXXX00', 'J. CONCEPCION',   'MENDOZA', 'SANCHEZ', false],
    ['Extranjero',               'EXTR000101HNEXXX00', 'PEDRO MIGUEL',       'LOPEZ',    'MARTINEZ'],
    ['Apellido con conector',    'JIML921015HNLNRS03', 'LUIS ALBERTO',       'JIMENEZ',  'LOPEZ'],
];

echo "1. PROCESAMIENTO DE NOMBRES (logica de pegamento)" . PHP_EOL;
echo "------------------------------------------------------" . PHP_EOL;

foreach ($personas as $datos) {
    $descripcion = $datos[0];
    $curp        = $datos[1];
    $nombres     = $datos[2];
    $paterno     = $datos[3];
    $materno     = $datos[4];
    $mantener    = $datos[5] ?? true;

    $p = MexCore::Persona()->fromData($curp, $nombres, $paterno, $materno, $mantener);

    echo "  Caso: {$descripcion}" . PHP_EOL;
    echo "    CURP:          {$p->toCurp()}" . PHP_EOL;
    echo "    Primer nombre: {$p->toPrimerNombre()}" . PHP_EOL;
    echo "    Segundo nom:   {$p->toSegundoNombre()}" . PHP_EOL;
    echo "    Paterno:       {$p->toPrimerApellido()}" . PHP_EOL;
    echo "    Materno:       {$p->toSegundoApellido()}" . PHP_EOL;
    echo "    Completo:      {$p->toNombreCompleto()}" . PHP_EOL;
    echo "" . PHP_EOL;
}

// ─── 2. INTERFAZ FLUIDA y toArray ──────────────────────────────────

echo "2. INTERFAZ FLUIDA" . PHP_EOL;
echo "--------------------------------------------" . PHP_EOL;

$p = MexCore::Persona()->fromData('VARG740228HSPLSN07', 'JUAN CARLOS', 'VALENCIA', 'RAMIREZ');

echo "  toPrimerNombre()    =>  " . $p->toPrimerNombre() . PHP_EOL;
echo "  toSegundoNombre()   =>  " . $p->toSegundoNombre() . PHP_EOL;
echo "  toPrimerApellido()  =>  " . $p->toPrimerApellido() . PHP_EOL;
echo "  toSegundoApellido() =>  " . $p->toSegundoApellido() . PHP_EOL;
echo "  toNombreCompleto()  =>  " . $p->toNombreCompleto() . PHP_EOL;
echo "  toArray():" . PHP_EOL;
print_r($p->toArray());

// ─── 3. toNombreUnico Y combinar ──────────────────────────────────

echo "3. toNombreUnico() y combinar()" . PHP_EOL;
echo "   Para cuando el sistema separo incorrectamente los nombres" . PHP_EOL;
echo "---------------------------------------------------------------" . PHP_EOL;

$p1 = MexCore::Persona()->fromData('AAAA000101HBCXXX00', 'MA. GUADALUPE', 'TORRES', 'FLORES');
echo "  Original:" . PHP_EOL;
echo "    Primer nombre: {$p1->toPrimerNombre()}" . PHP_EOL;
echo "    Segundo nombre: {$p1->toSegundoNombre()}" . PHP_EOL;
echo "  toNombreUnico(): {$p1->toNombreUnico()}" . PHP_EOL;

$combinada = $p1->combinar();
echo "  Despues de combinar():" . PHP_EOL;
echo "    Primer nombre: {$combinada->toPrimerNombre()}" . PHP_EOL;
echo "    Segundo nombre: {$combinada->toSegundoNombre()}" . PHP_EOL;
echo "    Completo: {$combinada->toNombreCompleto()}" . PHP_EOL;
echo "" . PHP_EOL;

$p2 = MexCore::Persona()->fromData('VARG740228HSPLSN07', 'JUAN CARLOS', 'VALENCIA', 'RAMIREZ');
echo "  toNombreUnico() con dos nombres reales: {$p2->toNombreUnico()}" . PHP_EOL;
echo "  toNombreUnico() con nombre simple: "
    . MexCore::Persona()->fromData('CCCC000101HCCXXX00', 'MARIA', 'SANTOS', 'PEREZ')->toNombreUnico() . PHP_EOL;

// ─── 4. INTEGRACION CON ESTADO (toEstado) ──────────────────────────

echo "4. INTEGRACION CON ESTADO (toEstado)" . PHP_EOL;
echo "--------------------------------------------" . PHP_EOL;

$p = MexCore::Persona()->fromData('VARG740228HSPLSN07', 'JUAN CARLOS', 'VALENCIA', 'RAMIREZ');
$e = $p->toEstado();

echo "  Persona: {$p->toNombreCompleto()}" . PHP_EOL;
echo "  Estado via toEstado():" . PHP_EOL;
echo "    ->toName()     =>  {$e->toName()}" . PHP_EOL;
echo "    ->toAbbr()     =>  {$e->toAbbr()}" . PHP_EOL;
echo "    ->toNumber()   =>  {$e->toNumber()}" . PHP_EOL;
echo "    ->toCurp()     =>  {$e->toCurp()}" . PHP_EOL;

echo PHP_EOL . "  Fluent completo:" . PHP_EOL;
echo "  MexCore::Persona()->fromData(...)->toEstado()->toAbbr()" . PHP_EOL;
echo "    =>  " . MexCore::Persona()->fromData('VARG740228HSPLSN07', 'JUAN CARLOS', 'VALENCIA', 'RAMIREZ')->toEstado()->toAbbr() . PHP_EOL;

echo PHP_EOL . "==================================" . PHP_EOL;
echo "  Prueba de Personas - Completa" . PHP_EOL;
echo "==================================" . PHP_EOL;
