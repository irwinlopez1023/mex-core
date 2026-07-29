<?php
require_once __DIR__ . '/vendor/autoload.php';

use Irwinlopez1023\MexCore\MexCore;

echo "================================" . PHP_EOL;
echo "  MexCore - Demo y pruebas" . PHP_EOL;
echo "================================" . PHP_EOL . PHP_EOL;

// 1. LISTADO COMPLETO DE LOS 33 ESTADOS
echo "1. LISTADO DE LOS 33 ESTADOS" . PHP_EOL;
echo "   (32 estados + Nacido en el Extranjero)" . PHP_EOL;
echo "------------------------------------------" . PHP_EOL;

for ($i = 1; $i <= 33; $i++) {
    $e = MexCore::fromNumber($i);
    echo "  #" . $e->toNumber() . "\t" . $e->toCurp() . "\t" . $e->toAbbr() . "\t" . $e->toName() . PHP_EOL;
}

// 2. CURP COMPLETA DE 18 CARACTERES
echo PHP_EOL . "2. CURP COMPLETA (18 caracteres)" . PHP_EOL;
echo "   Se extrae automaticamente el codigo de estado (posiciones 11-12)" . PHP_EOL;
echo "--------------------------------------------------------------------" . PHP_EOL;

$curps = [
    'AAAA000101HASXXX00',
    'BBBB000101HBCXXX00',
    'CCCC000101HBSXXX00',
    'DDDD000101HCCXXX00',
    'EEEE000101HCLXXX00',
    'FFFF000101HCMXXX00',
    'GGGG000101HCSXXX00',
    'HHHH000101HCHXXX00',
    'IIII000101HDFXXX00',
    'JJJJ000101HDGXXX00',
    'KKKK000101HGTXXX00',
    'LLLL000101HGRXXX00',
    'MMMM000101HHGXXX00',
    'NNNN000101HJCXXX00',
    'OOOO000101HMSXXX00',
    'PPPP000101HMCXXX00',
    'QQQQ000101HMNXXX00',
    'RRRR000101HNTXXX00',
    'SSSS000101HNLXXX00',
    'TTTT000101HOCXXX00',
    'UUUU000101HPLXXX00',
    'VVVV000101HQTXXX00',
    'WWWW000101HQRXXX00',
    'XXXX000101HSPXXX00',
    'YYYY000101HSLXXX00',
    'ZZZZ000101HSRXXX00',
    'ABCD000101HTCXXX00',
    'EFGH000101HTSXXX00',
    'IJKL000101HTLXXX00',
    'MNOP000101HVZXXX00',
    'QRST000101HYNXXX00',
    'UVWX000101HZSXXX00',
    'EXTR000101HNEXXX00',
];

foreach ($curps as $curp) {
    $resultado = MexCore::fromCurp($curp)->toName();
    echo "  $curp  =>  $resultado" . PHP_EOL;
}

// 3. CODIGO CURP DE 2 LETRAS
echo PHP_EOL . "3. CODIGO CURP DE 2 LETRAS" . PHP_EOL;
echo "---------------------------------" . PHP_EOL;
echo "  fromCurp('SP')  =>  " . MexCore::fromCurp('SP')->toName() . PHP_EOL;
echo "  fromCurp('NL')  =>  " . MexCore::fromCurp('NL')->toName() . PHP_EOL;
echo "  fromCurp('NE')  =>  " . MexCore::fromCurp('NE')->toName() . PHP_EOL;
echo "  fromCurp('as')  =>  " . MexCore::fromCurp('as')->toName() . "  (minusculas)" . PHP_EOL;

// 4. RESILIENCIA
echo PHP_EOL . "4. RESILIENCIA" . PHP_EOL;
echo "   Tolera: acentos, puntos, mayusculas/minusculas" . PHP_EOL;
echo "---------------------------------------------------" . PHP_EOL;

echo "  fromAbbr('S.L.P.')  =>  " . MexCore::fromAbbr('S.L.P.')->toName() . "  (le quita los puntos)" . PHP_EOL;
echo "  fromAbbr('slp')     =>  " . MexCore::fromAbbr('slp')->toName() . "  (tolera minusculas)" . PHP_EOL;
echo "  fromAbbr('CDMX')    =>  " . MexCore::fromAbbr('CDMX')->toName() . PHP_EOL;
echo "  fromAbbr('cdmx')    =>  " . MexCore::fromAbbr('cdmx')->toName() . "  (tolera minusculas)" . PHP_EOL;
echo "  fromName('NUEVO LEON')     =>  " . MexCore::fromName('NUEVO LEON')->toName() . "  (sin acento)" . PHP_EOL;
echo "  fromName('Nuevo Leon')     =>  " . MexCore::fromName('Nuevo Leon')->toName() . "  (sin acento)" . PHP_EOL;
echo "  fromName('san luis potosi')  =>  " . MexCore::fromName('san luis potosi')->toName() . "  (minusculas + sin acento)" . PHP_EOL;
echo "  fromName('Mexico')    =>  " . MexCore::fromName('Mexico')->toName() . "  (alias: Mexico = Estado de Mexico)" . PHP_EOL;
echo "  fromName('EDOMEX')    =>  " . MexCore::fromName('EDOMEX')->toName() . "  (alias)" . PHP_EOL;
echo "  fromName('DF')        =>  " . MexCore::fromName('DF')->toName() . "  (alias)" . PHP_EOL;
echo "  fromName('Distrito Federal')  =>  " . MexCore::fromName('Distrito Federal')->toName() . "  (alias)" . PHP_EOL;
echo "  fromName('extranjero')  =>  " . MexCore::fromName('extranjero')->toName() . "  (alias)" . PHP_EOL;

// 5. INTERFAZ FLUIDA
echo PHP_EOL . "5. INTERFAZ FLUIDA" . PHP_EOL;
echo "   Llamadas encadenadas en una sola linea" . PHP_EOL;
echo "--------------------------------------------" . PHP_EOL;

$result = MexCore::fromName('Nuevo Leon')->toCurp();
echo "  fromName('Nuevo Leon')->toCurp()        =>  $result" . PHP_EOL;

$result = MexCore::fromAbbr('CDMX')->toNumber();
echo "  fromAbbr('CDMX')->toNumber()            =>  $result" . PHP_EOL;

$result = MexCore::fromCurp('EXTR000101HNEXXX00')->toAbbr();
echo "  fromCurp('EXTR...HNE...')->toAbbr()     =>  $result" . PHP_EOL;

$result = MexCore::fromNumber(15)->toCurp();
echo "  fromNumber(15)->toCurp()                =>  $result" . PHP_EOL;

$result = MexCore::fromCurp('SSSS000101HNLXXX00')->toNumber();
echo "  fromCurp('SSSS...HNL...')->toNumber()   =>  $result" . PHP_EOL;

// 6. EXCEPCION
echo PHP_EOL . "6. EXCEPCION (input invalido)" . PHP_EOL;
echo "---------------------------------" . PHP_EOL;

try {
    MexCore::fromCurp('AAAA000101HZZXXX00');
} catch (\Irwinlopez1023\MexCore\InvalidStateException $e) {
    echo "  fromCurp('AAAA000101HZZXXX00')  =>  " . $e->getMessage() . PHP_EOL;
}

try {
    MexCore::fromNumber(99);
} catch (\Irwinlopez1023\MexCore\InvalidStateException $e) {
    echo "  fromNumber(99)                   =>  " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "================================" . PHP_EOL;
echo "  FIN - Todo correcto" . PHP_EOL;
echo "================================" . PHP_EOL;
