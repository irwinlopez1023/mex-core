<?php

declare(strict_types=1);

require_once __DIR__ . '/tests/harness.php';

use Irwinlopez1023\MexCore\Curp;
use Irwinlopez1023\MexCore\Estado;
use Irwinlopez1023\MexCore\InvalidStateException;
use Irwinlopez1023\MexCore\MexCore;
use Irwinlopez1023\MexCore\Persona;

/**
 * Suite de aserciones para el area de Estados.
 * Ejecutar con: php test_estados.php
 */

suite('Estados');

function estado(int|string $valor): Estado
{
    return MexCore::Estado()->desde($valor);
}

function nombreDe(int|string $valor): string
{
    return estado($valor)->toNombre();
}

// --- 1. Catalogo ------------------------------------------------------

$catalogo = MexCore::Estado()->listar();

check('[catalogo] 32 entidades mas extranjero', 33, count($catalogo));
check('[catalogo] primera entidad', 'Aguascalientes', $catalogo[0]->toNombre());
check('[catalogo] ultima entidad', 'Nacido en el Extranjero', $catalogo[32]->toNombre());

$numeros      = array_map(static fn (Estado $e): int    => $e->toNumero(),      $catalogo);
$codigos      = array_map(static fn (Estado $e): string => $e->toCurp(),        $catalogo);
$abreviaturas = array_map(static fn (Estado $e): string => $e->toAbreviatura(), $catalogo);
$nombres      = array_map(static fn (Estado $e): string => $e->toNombre(),      $catalogo);

check('[catalogo] claves del 1 al 33 en orden', range(1, 33), $numeros);
check('[catalogo] codigos de CURP unicos', 33, count(array_unique($codigos)));
check('[catalogo] abreviaturas unicas', 33, count(array_unique($abreviaturas)));
check('[catalogo] nombres unicos', 33, count(array_unique($nombres)));

// Cada entidad debe resolverse por sus cuatro identificadores.
foreach ($catalogo as $e) {
    $n = $e->toNumero();

    check("[ida y vuelta {$n}] por numero", $n, MexCore::Estado()->fromNumero($n)->toNumero());
    check("[ida y vuelta {$n}] por curp", $n, MexCore::Estado()->fromCurp($e->toCurp())->toNumero());
    check("[ida y vuelta {$n}] por abreviatura", $n, MexCore::Estado()->fromAbreviatura($e->toAbreviatura())->toNumero());
    check("[ida y vuelta {$n}] por nombre", $n, MexCore::Estado()->fromNombre($e->toNombre())->toNumero());
}

// --- 2. Named constructors -------------------------------------------

check('[from] curp completa', 'San Luis Potosí', MexCore::Estado()->fromCurp('VARG740228HSPLSN07')->toNombre());
check('[from] curp completa PL', 'Puebla', MexCore::Estado()->fromCurp('SOSR650222MPLSNC03')->toNombre());
check('[from] codigo de dos letras', 'Nuevo León', MexCore::Estado()->fromCurp('NL')->toNombre());
check('[from] codigo en minusculas', 'San Luis Potosí', MexCore::Estado()->fromCurp('sp')->toNombre());
check('[from] codigo DF sigue vigente', 'Ciudad de México', MexCore::Estado()->fromCurp('DF')->toNombre());
check('[from] codigo NE', 'Nacido en el Extranjero', MexCore::Estado()->fromCurp('NE')->toNombre());
check('[from] numero', 'San Luis Potosí', MexCore::Estado()->fromNumero(24)->toNombre());
check('[from] numero con cero a la izquierda', 'Ciudad de México', MexCore::Estado()->fromNumero('09')->toNombre());
check('[from] abreviatura', 'Ciudad de México', MexCore::Estado()->fromAbreviatura('CDMX')->toNombre());
check('[from] nombre a codigo', 'NL', MexCore::Estado()->fromNombre('Nuevo León')->toCurp());
check('[from] nombre historico a abreviatura', 'CDMX', MexCore::Estado()->fromNombre('Distrito Federal')->toAbreviatura());

// --- 3. Alias en ingles (compatibilidad) -----------------------------

check('[alias] fromName + toName', 'Puebla', MexCore::Estado()->fromName('Puebla')->toName());
check('[alias] fromAbbr + toNumber', 24, MexCore::Estado()->fromAbbr('SLP')->toNumber());
check('[alias] fromNumber + toAbbr', 'PUE', MexCore::Estado()->fromNumber(21)->toAbbr());
check('[alias] toName coincide con toNombre', MexCore::Estado()->fromNumero(30)->toNombre(), MexCore::Estado()->fromNumero(30)->toName());
check('[alias] toAbbr coincide con toAbreviatura', MexCore::Estado()->fromNumero(30)->toAbreviatura(), MexCore::Estado()->fromNumero(30)->toAbbr());
check('[alias] toNumber coincide con toNumero', MexCore::Estado()->fromNumero(30)->toNumero(), MexCore::Estado()->fromNumero(30)->toNumber());

// --- 4. Resiliencia de entrada ---------------------------------------

check('[resiliencia] abreviatura con puntos', 'San Luis Potosí', MexCore::Estado()->fromAbreviatura('S.L.P.')->toNombre());
check('[resiliencia] abreviatura con puntos y espacios', 'San Luis Potosí', MexCore::Estado()->fromAbreviatura('S. L. P.')->toNombre());
check('[resiliencia] abreviatura en minusculas', 'San Luis Potosí', MexCore::Estado()->fromAbreviatura('slp')->toNombre());
check('[resiliencia] nombre sin acento', 'Nuevo León', MexCore::Estado()->fromNombre('NUEVO LEON')->toNombre());
check('[resiliencia] nombre en minusculas sin acento', 'San Luis Potosí', MexCore::Estado()->fromNombre('san luis potosi')->toNombre());
check('[resiliencia] nombre con acento', 'Michoacán', MexCore::Estado()->fromNombre('MICHOACÁN')->toNombre());

// Fix: los separadores no se normalizaban. Antes solo se aplicaba trim, asi
// que un espacio doble o un espacio duro copiado de un PDF no resolvia.
check('[resiliencia] espacios dobles', 'Baja California', MexCore::Estado()->fromNombre('  Baja   California  ')->toNombre());
check('[resiliencia] espacio duro U+00A0', 'San Luis Potosí', MexCore::Estado()->fromNombre("San\u{00A0}Luis Potosí")->toNombre());
check('[resiliencia] salto de linea', 'Quintana Roo', MexCore::Estado()->fromNombre("Quintana\nRoo")->toNombre());
check('[resiliencia] tabulador en abreviatura', 'Yucatán', MexCore::Estado()->fromAbreviatura("\tYUC\t")->toNombre());

// --- 5. Fix: fromNumero ya no acepta basura --------------------------

check('[numero] cadena de digitos', 'San Luis Potosí', MexCore::Estado()->fromNumero('24')->toNombre());
check('[numero] con espacios alrededor', 'San Luis Potosí', MexCore::Estado()->fromNumero(' 24 ')->toNombre());

// (int) '24abc' daba 24 y (int) '24.9' daba 24, asi que ambos devolvian
// San Luis Potosi sin avisar de que la entrada estaba mal.
excepcion('[numero] rechaza 24abc', InvalidStateException::class, static fn () => MexCore::Estado()->fromNumero('24abc'));
excepcion('[numero] rechaza 24.9', InvalidStateException::class, static fn () => MexCore::Estado()->fromNumero('24.9'));
excepcion('[numero] rechaza negativo', InvalidStateException::class, static fn () => MexCore::Estado()->fromNumero('-5'));
excepcion('[numero] rechaza cadena vacia', InvalidStateException::class, static fn () => MexCore::Estado()->fromNumero(''));
excepcion('[numero] rechaza cero', InvalidStateException::class, static fn () => MexCore::Estado()->fromNumero(0));
excepcion('[numero] rechaza 34', InvalidStateException::class, static fn () => MexCore::Estado()->fromNumero(34));

// --- 6. Fix: la CURP se detecta por forma, no por longitud -----------

// Antes bastaba con medir 18 caracteres para leer las posiciones 11 y 12, asi
// que cualquier cadena larga cuyos caracteres 11 y 12 fueran un codigo valido
// se resolvia por accidente. Aqui 'PL' cae justo ahi.
excepcion(
    '[forma] cadena larga que no es CURP',
    InvalidStateException::class,
    static fn () => MexCore::Estado()->fromCurp('ABCDEFGHIJKPLMNOPQRS')
);

check('[forma] entidad inexistente en la CURP', false, MexCore::Estado()->existe('AAAA000101HZZXXX00'));
excepcion('[forma] entidad ZZ', InvalidStateException::class, static fn () => MexCore::Estado()->fromCurp('AAAA000101HZZXXX00'));
excepcion('[forma] CURP truncada a 17', InvalidStateException::class, static fn () => MexCore::Estado()->fromCurp('SOSR650222MPLSNC0'));

// --- 7. CURP con separadores en medio -------------------------------

// Copiar de un PDF puede partir la CURP. Como una CURP no lleva espacios, se
// eliminan todos y las posiciones vuelven a alinearse.
check('[separadores] espacio en medio', 'Puebla', MexCore::Estado()->fromCurp('SOSR650222 MPLSNC03')->toNombre());
check('[separadores] espacio duro en medio', 'Puebla', MexCore::Estado()->fromCurp("SOSR650222\u{00A0}MPLSNC03")->toNombre());

// El mismo criterio rige en Curp y en PersonaQuery, para que una entrada que
// resuelve el estado tambien pase la validacion y la derivacion.
check('[separadores] Curp::esValida', true, Curp::esValida('SOSR650222 MPLSNC03'));
check('[separadores] Curp::sexo', 'M', Curp::sexo('SOSR650222 MPLSNC03'));

$conEspacio = MexCore::Persona()->fromData('SOSR650222 MPLSNC03', 'MARIA DEL ROCIO', 'SOSA', 'SANCHEZ');
check('[separadores] Persona normaliza la CURP', 'SOSR650222MPLSNC03', $conEspacio->toCurp());
check('[separadores] Persona valida la CURP', true, $conEspacio->tieneCurpValida());
check('[separadores] Persona resuelve el estado', 'Puebla', $conEspacio->toEstado()->toNombre());

// --- 8. Alias y nombres oficiales -----------------------------------

check('[alias] Mexico es el Estado de Mexico', 'Estado de México', nombreDe('Mexico'));
check('[alias] EDOMEX', 'Estado de México', nombreDe('EDOMEX'));
check('[alias] EDO MEX', 'Estado de México', nombreDe('EDO MEX'));
check('[alias] CDMX como nombre', 'Ciudad de México', MexCore::Estado()->fromNombre('CDMX')->toNombre());
check('[alias] extranjero', 'Nacido en el Extranjero', nombreDe('extranjero'));
check('[alias] DF como abreviatura', 'Ciudad de México', MexCore::Estado()->fromAbreviatura('DF')->toNombre());
check('[alias] QROO como abreviatura', 'Quintana Roo', MexCore::Estado()->fromAbreviatura('QROO')->toNombre());

// Nombres constitucionales: son los que trae el acta de nacimiento.
check('[oficial] Coahuila de Zaragoza', 5, MexCore::Estado()->fromNombre('Coahuila de Zaragoza')->toNumero());
check('[oficial] Michoacán de Ocampo', 16, MexCore::Estado()->fromNombre('Michoacán de Ocampo')->toNumero());
check('[oficial] Veracruz de Ignacio de la Llave', 30, MexCore::Estado()->fromNombre('Veracruz de Ignacio de la Llave')->toNumero());
check('[oficial] Querétaro de Arteaga', 22, MexCore::Estado()->fromNombre('Querétaro de Arteaga')->toNumero());

// --- 9. desde(), intentarDesde() y existe() -------------------------

check('[desde] numero entero', 'San Luis Potosí', nombreDe(24));
check('[desde] numero como cadena', 'San Luis Potosí', nombreDe('24'));
check('[desde] codigo de CURP', 'San Luis Potosí', nombreDe('SP'));
check('[desde] abreviatura', 'San Luis Potosí', nombreDe('SLP'));
check('[desde] nombre', 'San Luis Potosí', nombreDe('san luis potosi'));
check('[desde] CURP completa', 'San Luis Potosí', nombreDe('VARG740228HSPLSN07'));

// BC, NL y QR son a la vez codigo de CURP y abreviatura. Apuntan a la misma
// entidad en los dos catalogos, asi que la precedencia no altera el resultado.
check('[desde] BC sin ambiguedad', 2, estado('BC')->toNumero());
check('[desde] NL sin ambiguedad', 19, estado('NL')->toNumero());
check('[desde] QR sin ambiguedad', 23, estado('QR')->toNumero());

check('[intentar] devuelve null en lugar de lanzar', null, MexCore::Estado()->intentarDesde('Narnia'));
check('[intentar] resuelve lo valido', 21, MexCore::Estado()->intentarDesde('PL')?->toNumero());
check('[existe] valor valido', true, MexCore::Estado()->existe('PUE'));
check('[existe] valor invalido', false, MexCore::Estado()->existe('Narnia'));
check('[existe] numero fuera de rango', false, MexCore::Estado()->existe(99));
excepcion('[desde] lanza con valor invalido', InvalidStateException::class, static fn () => MexCore::Estado()->desde('Narnia'));

// --- 10. Value object ------------------------------------------------

$cdmx = MexCore::Estado()->fromNumero(9);

check('[objeto] toNumero', 9, $cdmx->toNumero());
check('[objeto] toCurp', 'DF', $cdmx->toCurp());
check('[objeto] toAbreviatura', 'CDMX', $cdmx->toAbreviatura());
check('[objeto] toNombre', 'Ciudad de México', $cdmx->toNombre());
check('[objeto] toNumeroFormateado con cero', '09', $cdmx->toNumeroFormateado());
check('[objeto] toNumeroFormateado de dos digitos', '24', MexCore::Estado()->fromNumero(24)->toNumeroFormateado());
check('[objeto] esExtranjero falso', false, $cdmx->esExtranjero());
check('[objeto] esExtranjero verdadero', true, MexCore::Estado()->fromNumero(33)->esExtranjero());

check('[objeto] toIso', 'CMX', $cdmx->toIso());

check('[objeto] toArray', [
    'numero'      => 9,
    'nombre'      => 'Ciudad de México',
    'curp'        => 'DF',
    'abreviatura' => 'CDMX',
    'iso'         => 'CMX',
], $cdmx->toArray());

check('[objeto] equals por identidad de datos', true, $cdmx->equals(MexCore::Estado()->fromCurp('DF')));
check('[objeto] equals distingue entidades', false, $cdmx->equals(MexCore::Estado()->fromNumero(15)));
check('[objeto] equals por otra via de entrada', true, MexCore::Estado()->fromAbreviatura('SLP')->equals(MexCore::Estado()->fromNumero(24)));

check(
    '[objeto] json',
    '{"numero":1,"nombre":"Aguascalientes","curp":"AS","abreviatura":"AGS","iso":"AGU"}',
    json_encode(MexCore::Estado()->fromNumero(1))
);

// --- 10b. Codigo ISO 3166-2:MX ---------------------------------------

// La abreviatura de uso comun tiene largo variable (BC, JAL, CDMX, TAMPS),
// asi que no sirve para las APIs que exigen un catalogo de tres letras. El
// codigo ISO si es de largo fijo.
$isoEsperado = [
    1 => 'AGU', 2 => 'BCN', 3 => 'BCS', 4 => 'CAM', 5 => 'COA', 6 => 'COL',
    7 => 'CHP', 8 => 'CHH', 9 => 'CMX', 10 => 'DUR', 11 => 'GUA', 12 => 'GRO',
    13 => 'HID', 14 => 'JAL', 15 => 'MEX', 16 => 'MIC', 17 => 'MOR', 18 => 'NAY',
    19 => 'NLE', 20 => 'OAX', 21 => 'PUE', 22 => 'QUE', 23 => 'ROO', 24 => 'SLP',
    25 => 'SIN', 26 => 'SON', 27 => 'TAB', 28 => 'TAM', 29 => 'TLA', 30 => 'VER',
    31 => 'YUC', 32 => 'ZAC', 33 => 'NE',
];

foreach ($isoEsperado as $clave => $codigo) {
    check("[iso {$clave}] codigo", $codigo, MexCore::Estado()->fromNumero($clave)->toIso());
    check("[iso {$clave}] ida y vuelta", $clave, MexCore::Estado()->fromIso($codigo)->toNumero());
}

// Las 32 entidades traen tres letras. El 33 no existe en la norma ISO, asi
// que se usa NE, que es el codigo de la CURP y el que espera el catalogo.
$isos = array_map(static fn (Estado $e): string => $e->toIso(), MexCore::Estado()->listar());

check('[iso] 33 codigos unicos', 33, count(array_unique($isos)));
check('[iso] las 32 entidades son de tres letras', 32, count(array_filter($isos, static fn (string $c): bool => strlen($c) === 3)));
check('[iso] el extranjero es NE', 'NE', MexCore::Estado()->fromNumero(33)->toIso());

// El caso que rompio en produccion: BC no es un codigo valido para la API,
// BCN si. Antes toAbreviatura() devolvia BC y no habia forma de obtener BCN.
check('[iso] Baja California desde CURP', 'BCN', MexCore::Estado()->fromCurp('XXXX000101HBCXXX00')->toIso());
check('[iso] BC sigue siendo la abreviatura', 'BC', MexCore::Estado()->fromCurp('XXXX000101HBCXXX00')->toAbreviatura());

// El codigo ISO tambien se acepta como entrada.
check('[iso] fromAbreviatura acepta BCN', 2, MexCore::Estado()->fromAbreviatura('BCN')->toNumero());
check('[iso] desde acepta BCN', 2, MexCore::Estado()->desde('BCN')->toNumero());
check('[iso] desde acepta TAM', 28, MexCore::Estado()->desde('TAM')->toNumero());
check('[iso] desde sigue aceptando TAMPS', 28, MexCore::Estado()->desde('TAMPS')->toNumero());

// fromIso() es estricto: solo el catalogo ISO, no las formas de uso comun.
excepcion('[iso] fromIso rechaza BC', InvalidStateException::class, static fn () => MexCore::Estado()->fromIso('BC'));
excepcion('[iso] fromIso rechaza TAMPS', InvalidStateException::class, static fn () => MexCore::Estado()->fromIso('TAMPS'));
excepcion('[iso] fromIso rechaza CDMX', InvalidStateException::class, static fn () => MexCore::Estado()->fromIso('CDMX'));

// --- 11. Congruencia con el area de Personas -------------------------

// Los dos value objects deben exponer la misma superficie basica.
foreach (['toArray', 'equals', 'jsonSerialize'] as $metodo) {
    check("[congruencia] Estado::{$metodo}", true, method_exists(Estado::class, $metodo));
    check("[congruencia] Persona::{$metodo}", true, method_exists(Persona::class, $metodo));
}

check('[congruencia] Estado es JsonSerializable', true, $cdmx instanceof JsonSerializable);

$persona = MexCore::Persona()->fromData('VARG740228HSPLSN07', 'JUAN CARLOS', 'VALENCIA', 'RAMIREZ');
check('[congruencia] Persona::toEstado', 'San Luis Potosí', $persona->toEstado()->toNombre());
check('[congruencia] toEstado equivale a fromCurp', true, $persona->toEstado()->equals(MexCore::Estado()->fromCurp('VARG740228HSPLSN07')));

// --- 12. Excepciones -------------------------------------------------

excepcion('[error] codigo inexistente', InvalidStateException::class, static fn () => MexCore::Estado()->fromCurp('ZZ'));
excepcion('[error] abreviatura inexistente', InvalidStateException::class, static fn () => MexCore::Estado()->fromAbreviatura('XYZ'));
excepcion('[error] nombre inexistente', InvalidStateException::class, static fn () => MexCore::Estado()->fromNombre('Narnia'));
excepcion('[error] cadena vacia como nombre', InvalidStateException::class, static fn () => MexCore::Estado()->fromNombre(''));

cerrarSuite();
