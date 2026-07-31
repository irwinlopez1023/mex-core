<?php

declare(strict_types=1);

require_once __DIR__ . '/tests/harness.php';

use Irwinlopez1023\MexCore\Curp;
use Irwinlopez1023\MexCore\MexCore;
use Irwinlopez1023\MexCore\Persona;

/**
 * Suite de aserciones para el area de Personas.
 * Ejecutar con: php test_persona.php (o php test.php para las dos suites).
 */

suite('Personas');

function personaDe(string $nombres, bool $mantenerPunto = true): Persona
{
    return MexCore::Persona()->fromData('VARG740228HSPLSN07', $nombres, 'VALENCIA', 'RAMIREZ', $mantenerPunto);
}

function partir(string $nombres, bool $mantenerPunto = true): array
{
    $p = personaDe($nombres, $mantenerPunto);

    return [$p->toPrimerNombre(), $p->toSegundoNombre()];
}

// --- 1. Regla de negocio intocable -----------------------------------

$intocables = [
    ['MARIA DEL ROCIO',           ['MARIA DEL ROCIO', '']],
    ['JUAN CARLOS DE JESUS',      ['JUAN', 'CARLOS DE JESUS']],
    ['MARIA DEL ROCIO ALEJANDRA', ['MARIA DEL ROCIO', 'ALEJANDRA']],
    ['MA. DE LOS ANGELES',        ['MA. DE LOS ANGELES', '']],
    ['JOSE DE JESUS',             ['JOSE DE JESUS', '']],
    ['JUAN CARLOS',               ['JUAN', 'CARLOS']],
    ['MARIA',                     ['MARIA', '']],
    // Confirmado con la credencial de JUAN DE JESUS RIVAS SANCHEZ
    // (RISJ841105HJCVNN07): es un solo nombre, no JUAN + DE JESUS.
    ['JUAN DE JESUS',             ['JUAN DE JESUS', '']],
    ['JUAN DE DIOS',              ['JUAN DE DIOS', '']],
    ['MARIA DE JESUS',            ['MARIA DE JESUS', '']],
    // Y con un nombre mas atras el bloque se cierra donde debe.
    ['JUAN DE JESUS ALBERTO',     ['JUAN DE JESUS', 'ALBERTO']],
];

foreach ($intocables as $caso) {
    check("[intocable] {$caso[0]}", $caso[1], partir($caso[0]));
}

// --- 2. Fix: un segundo grupo de conectores abre bloque nuevo ---------

check('[fix conectores] MARIA DE LA LUZ DEL CARMEN', ['MARIA DE LA LUZ', 'DEL CARMEN'], partir('MARIA DE LA LUZ DEL CARMEN'));
check('[fix conectores] MARIA DE LOS ANGELES DE LA CRUZ', ['MARIA DE LOS ANGELES', 'DE LA CRUZ'], partir('MARIA DE LOS ANGELES DE LA CRUZ'));

// --- 3. Fix: las abreviaturas pegan hacia adelante --------------------

check('[abrev] J. JESUS', ['J. JESUS', ''], partir('J. JESUS'));
check('[abrev] MA GUADALUPE', ['MA GUADALUPE', ''], partir('MA GUADALUPE'));
check('[abrev] MA. GUADALUPE ALEJANDRA', ['MA. GUADALUPE', 'ALEJANDRA'], partir('MA. GUADALUPE ALEJANDRA'));
check('[abrev] MA. GUADALUPE DE JESUS', ['MA. GUADALUPE', 'DE JESUS'], partir('MA. GUADALUPE DE JESUS'));
check('[abrev] GPE MARTIN', ['GPE MARTIN', ''], partir('GPE MARTIN'));
check('[abrev] R JESUS', ['R JESUS', ''], partir('R JESUS'));
// Una abreviatura no pega hacia atras: JOSE queda como primer nombre.
check('[abrev] JOSE MA. DEL CARMEN', ['JOSE', 'MA. DEL CARMEN'], partir('JOSE MA. DEL CARMEN'));
check('[abrev] sin punto J CONCEPCION', ['J CONCEPCION', ''], partir('J. CONCEPCION', false));
check('[abrev] con punto J. CONCEPCION', ['J. CONCEPCION', ''], partir('J. CONCEPCION'));

// --- 4. Fix: pegamento colgante y basura de entrada -------------------

check('[colgante] MARIA DE', ['MARIA', ''], partir('MARIA DE'));
check('[colgante] JUAN CARLOS DE', ['JUAN', 'CARLOS'], partir('JUAN CARLOS DE'));
check('[basura] solo conectores DE LA', ['DE LA', ''], partir('DE LA'));
check('[basura] vacio', ['', ''], partir(''));
check('[basura] solo espacios', ['', ''], partir('   '));

// --- 5. Fix: normalizacion de separadores ----------------------------

check('[norm] espacios multiples', ['MARIA DEL ROCIO', ''], partir('MARIA  DEL   ROCIO'));
check('[norm] tab y salto de linea', ['MARIA DEL ROCIO', ''], partir("MARIA\tDEL\nROCIO"));
check('[norm] espacio duro U+00A0', ['MARIA DEL ROCIO', ''], partir("MARIA\u{00A0}DEL ROCIO"));
check('[norm] minusculas', ['MARIA DEL ROCIO', ''], partir('maria del rocio'));
check('[norm] recorte externo', ['MARIA DEL ROCIO', ''], partir('   MARIA DEL ROCIO   '));

// --- 6. Comportamiento preservado ------------------------------------

check('[ok] MARIA Y JOSE', ['MARIA Y JOSE', ''], partir('MARIA Y JOSE'));
check('[ok] LUZ MARIA DE LOURDES', ['LUZ', 'MARIA DE LOURDES'], partir('LUZ MARIA DE LOURDES'));
check('[ok] MC DONALD', ['MC DONALD', ''], partir('MC DONALD'));
check('[ok] VAN GOGH', ['VAN GOGH', ''], partir('VAN GOGH'));
check('[ok] DE JESUS', ['DE JESUS', ''], partir('DE JESUS'));
check('[ok] tres nombres', ['JUAN', 'CARLOS ALBERTO'], partir('JUAN CARLOS ALBERTO'));

// --- 7. toNombres(): no se pierde el tercer nombre -------------------

check('[toNombres] tres bloques', ['JUAN', 'CARLOS', 'ALBERTO'], personaDe('JUAN CARLOS ALBERTO')->toNombres());
check('[toNombres] bloque compuesto', ['MARIA DEL ROCIO', 'ALEJANDRA'], personaDe('MARIA DEL ROCIO ALEJANDRA')->toNombres());
check('[separarNombres] acceso directo', ['MARIA DE LA LUZ', 'DEL CARMEN'], MexCore::Persona()->separarNombres('MARIA DE LA LUZ DEL CARMEN'));

// --- 8. combinar() / separar() / estaCombinado() ---------------------

$p = personaDe('JUAN CARLOS');
$c = $p->combinar();

check('[combinar] primerNombre', 'JUAN CARLOS', $c->toPrimerNombre());
check('[combinar] segundoNombre', '', $c->toSegundoNombre());
check('[combinar] estaCombinado', true, $c->estaCombinado());
check('[combinar] no muta el origen', 'JUAN', $p->toPrimerNombre());
check('[combinar] nombre completo', 'JUAN CARLOS VALENCIA RAMIREZ', $c->toNombreCompleto());
check('[separar] es reversible', true, $c->separar()->equals($p));
check('[separar] idempotente', true, $p->separar()->equals($p));
check('[combinar] nombre simple', true, personaDe('MARIA')->combinar()->equals(personaDe('MARIA')));
check('[toNombreUnico] simple', 'MARIA', personaDe('MARIA')->toNombreUnico());
check('[toNombreUnico] doble', 'JUAN CARLOS', $p->toNombreUnico());

// --- 9. Salidas de formato -------------------------------------------

check('[formato] invertido', 'VALENCIA RAMIREZ, JUAN CARLOS', $p->toNombreCompletoInvertido());
check('[formato] iniciales', 'JCVR', $p->toIniciales());
check('[formato] iniciales con bloque compuesto', 'MAVR', personaDe('MARIA DEL ROCIO ALEJANDRA')->toIniciales());
check('[formato] toArray', [
    'curp'            => 'VARG740228HSPLSN07',
    'primerNombre'    => 'JUAN',
    'segundoNombre'   => 'CARLOS',
    'primerApellido'  => 'VALENCIA',
    'segundoApellido' => 'RAMIREZ',
], $p->toArray());
check('[formato] json', $p->toArray(), json_decode(json_encode($p), true));

// --- 10. Apellidos: normalizacion -----------------------------------

$ap = MexCore::Persona()->fromData('GODE561231HASLRN09', 'MARIA', '  gonzalez ', "DE\u{00A0}LA   CRUZ");
check('[apellidos] paterno normalizado', 'GONZALEZ', $ap->toPrimerApellido());
check('[apellidos] materno normalizado', 'DE LA CRUZ', $ap->toSegundoApellido());

// --- 11. fromArray(): evita invertir apellidos ----------------------

$fa = MexCore::Persona()->fromArray([
    'curp'            => 'HEGG560427MVZRRL04',
    'nombres'         => 'MARIA DEL ROCIO',
    'primerApellido'  => 'HERNANDEZ',
    'segundoApellido' => 'GOMEZ',
]);
check('[fromArray] equivale a fromData', true, $fa->equals(
    MexCore::Persona()->fromData('HEGG560427MVZRRL04', 'MARIA DEL ROCIO', 'HERNANDEZ', 'GOMEZ')
));
check('[fromArray] defaults', ['', ''], [
    MexCore::Persona()->fromArray([])->toCurp(),
    MexCore::Persona()->fromArray([])->toPrimerNombre(),
]);

// --- 12. Diccionarios configurables ---------------------------------

$sinConectores = MexCore::Persona()->withConectores([]);
$x = $sinConectores->fromData('VARG740228HSPLSN07', 'MARIA DEL ROCIO', 'VALENCIA', 'RAMIREZ');
check('[config] sin conectores separa normal', ['MARIA', 'DEL ROCIO'], [$x->toPrimerNombre(), $x->toSegundoNombre()]);
check('[config] no contamina la instancia base', ['MARIA DEL ROCIO', ''], partir('MARIA DEL ROCIO'));

// --- 13. CURP derivada ----------------------------------------------

$curp = MexCore::Persona()->fromData('HEGG560427MVZRRL04', 'MARIA DEL ROCIO', 'HERNANDEZ', 'GOMEZ');

check('[curp] sexo', 'M', $curp->toSexo());
check('[curp] fecha', '1956-04-27', $curp->toFechaNacimiento() ? $curp->toFechaNacimiento()->format('Y-m-d') : null);
check('[curp] estado', 'Veracruz', $curp->toEstado()->toName());
check('[curp] estructura valida', true, $curp->tieneCurpValida());
check('[curp] edad con referencia', 68, $curp->toEdad(new DateTimeImmutable('2025-01-01')));

// Homoclave con letra (posicion 17) => nacido en el siglo 2000.
$siglo21 = MexCore::Persona()->fromData('LOPJ050310HDFPRNA9', 'JUAN', 'LOPEZ', 'PEREZ');
check('[curp] siglo 2000', '2005-03-10', $siglo21->toFechaNacimiento() ? $siglo21->toFechaNacimiento()->format('Y-m-d') : null);
check('[curp] sexo H', 'H', $siglo21->toSexo());

$malFormada = MexCore::Persona()->fromData('LOPE050310HDF PRNA9', 'JUAN', 'LOPEZ', 'PEREZ');
check('[curp] mal formada', false, $malFormada->tieneCurpValida());

check('[curp] fecha inexistente', null, MexCore::Persona()
    ->fromData('LOPJ050231HDFPRNA9', 'JUAN', 'LOPEZ', 'PEREZ')
    ->toFechaNacimiento());
check('[curp] vacia -> sexo', '', MexCore::Persona()->fromData('', 'JUAN', 'LOPEZ', 'PEREZ')->toSexo());
check('[curp] vacia -> fecha', null, MexCore::Persona()->fromData('', 'JUAN', 'LOPEZ', 'PEREZ')->toFechaNacimiento());
check('[curp] estado inexistente', false, MexCore::Persona()->fromData('LOPJ050310HZZPRNA9', 'JUAN', 'LOPEZ', 'PEREZ')->tieneCurpValida());

// --- 14. CURPs reales: digito verificador y validacion cruzada -------

// Cada caso: CURP, nombres, paterno, materno, sexo, fecha, estado, y si la
// inversion de apellidos es detectable (no lo es cuando ambos son iguales).
$reales = [
    ['SOSR650222MPLSNC03', 'MARIA DEL ROCIO',  'SOSA',      'SANCHEZ',   'M', '1965-02-22', 'Puebla',           true],
    ['MEST591010MQTNNR15', 'MA. TERESA PABLA', 'MENDEZ',    'SANCHEZ',   'M', '1959-10-10', 'Querétaro',        true],
    ['LUHR991216MPLZRC00', 'ROCIO',            'DE LA LUZ', 'HERNANDEZ', 'M', '1999-12-16', 'Puebla',           true],
    ['PEIA620825HCSRCN02', 'ANDRES',           'PEREZ',     'ICH',       'H', '1962-08-25', 'Chiapas',          true],
    ['TEMC930414MMCNNH05', 'CHEEL ICH',        'TENORIO',   'MENDOZA',   'M', '1993-04-14', 'Michoacán',        true],
    ['OEAI750827MMSVRC06', 'MA. ICH-CHEL',     'OVELIZ',    'ARANDA',    'M', '1975-08-27', 'Estado de México', true],
    ['BABA940526HHGCCB07', 'ABRAHAM',          'BACA',      'BACA',      'H', '1994-05-26', 'Hidalgo',          false],
    ['VEBS940210MCHGCH04', 'SAHIRA YANIRA',    'VEGA',      'BACA',      'M', '1994-02-10', 'Chihuahua',        true],
    // OJO: el homoclave de esta CURP es '0', un digito, asi que la norma la
    // manda al siglo XX: 1909, no 2009. Prefijo, consonantes y digito
    // verificador son correctos, pero conviene confirmar la fecha real.
    ['LOID091215MCSPCM00', 'DOMINGA',          'LOPEZ',     'ICH',       'M', '1909-12-15', 'Chiapas',          true],
    ['RISJ841105HJCVNN07', 'JUAN DE JESUS',    'RIVAS',     'SANCHEZ',   'H', '1984-11-05', 'Jalisco',          true],
];

foreach ($reales as $r) {
    [$curpReal, $nom, $pat, $mat, $sexo, $fechaEsperada, $estadoEsperado, $detectable] = $r;

    $pr = MexCore::Persona()->fromData($curpReal, $nom, $pat, $mat);

    check("[real {$curpReal}] digito verificador", substr($curpReal, 17), $pr->toDigitoVerificador());
    check("[real {$curpReal}] curp valida", true, $pr->tieneCurpValida());
    check("[real {$curpReal}] coincide con nombres", true, $pr->coincideConCurp());
    check("[real {$curpReal}] sexo", $sexo, $pr->toSexo());
    check("[real {$curpReal}] fecha", $fechaEsperada, $pr->toFechaNacimiento()->format('Y-m-d'));
    check("[real {$curpReal}] estado", $estadoEsperado, $pr->toEstado()->toNombre());

    // Apellidos invertidos: la validacion cruzada debe detectarlo.
    if ($detectable) {
        $invertido = MexCore::Persona()->fromData($curpReal, $nom, $mat, $pat);
        check("[real {$curpReal}] detecta apellidos invertidos", false, $invertido->coincideConCurp());
    }
}

// RENAPO omite MARIA/JOSE cuando hay un nombre posterior: SOSR viene de ROCIO,
// no de MARIA, y MEST viene de TERESA, no de MA.
check('[renapo] prefijo omite MARIA', 'SOSR', Curp::prefijoDesde('MARIA DEL ROCIO', 'SOSA', 'SANCHEZ'));
check('[renapo] prefijo omite MA.', 'MEST', Curp::prefijoDesde('MA. TERESA PABLA', 'MENDEZ', 'SANCHEZ'));
check('[renapo] particulas del apellido', 'LUHR', Curp::prefijoDesde('ROCIO', 'DE LA LUZ', 'HERNANDEZ'));
check('[renapo] nombre unico no se omite', 'SOSM', Curp::prefijoDesde('MARIA', 'SOSA', 'SANCHEZ'));
check('[renapo] consonantes', 'SNC', Curp::consonantesDesde('MARIA DEL ROCIO', 'SOSA', 'SANCHEZ'));
check('[renapo] sin segundo apellido', 'X', substr(Curp::prefijoDesde('ROCIO', 'SOSA', ''), 2, 1));
check('[renapo] digito con curp corta', '', Curp::digitoVerificador('SOSR65'));
check('[renapo] digito ignora el 18o caracter', '3', Curp::digitoVerificador('SOSR650222MPLSNC09'));
check('[renapo] curp con digito incorrecto', false, Curp::esValida('SOSR650222MPLSNC09'));

// --- 15. Casos limite confirmados con CURPs reales -------------------

// ICH no tiene vocal interna. Como segundo apellido solo aporta su inicial,
// que es lo que confirma PEIA. Como primer apellido la posicion 2 cae en X.
check('[limite] ICH como segundo apellido', 'PEIA', Curp::prefijoDesde('ANDRES', 'PEREZ', 'ICH'));
check('[limite] ICH sin vocal interna', 'IXPA', Curp::prefijoDesde('ANDRES', 'ICH', 'PEREZ'));
check('[limite] ICH consonante interna', 'C', substr(Curp::consonantesDesde('ANDRES', 'ICH', 'PEREZ'), 0, 1));

// CHEEL no es MARIA ni JOSE, asi que el primer nombre no se omite aunque
// haya un segundo. El prefijo termina en C, no en I de ICH.
check('[limite] primer nombre no omitido', 'TEMC', Curp::prefijoDesde('CHEEL ICH', 'TENORIO', 'MENDOZA'));
check('[limite] CHEEL bloques', ['CHEEL', 'ICH'], MexCore::Persona()->fromData('TEMC930414MMCNNH05', 'CHEEL ICH', 'TENORIO', 'MENDOZA')->toNombres());

// El guion interno no debe romper la derivacion: MA. se omite y la letra
// viene de ICH-CHEL.
check('[limite] nombre con guion', 'OEAI', Curp::prefijoDesde('MA. ICH-CHEL', 'OVELIZ', 'ARANDA'));
check('[limite] guion consonante interna', 'C', substr(Curp::consonantesDesde('MA. ICH-CHEL', 'OVELIZ', 'ARANDA'), 2, 1));
check('[limite] MA. pega hacia adelante con guion', 'MA. ICH-CHEL', MexCore::Persona()->fromData('OEAI750827MMSVRC06', 'MA. ICH-CHEL', 'OVELIZ', 'ARANDA')->toPrimerNombre());

// BABA no esta en la lista de palabras inconvenientes y la CURP real lo
// confirma: no hay sustitucion por X. BACA si esta, y ahi si sustituye.
check('[limite] BABA no es inconveniente', 'BABA', Curp::prefijoDesde('ABRAHAM', 'BACA', 'BACA'));
check('[limite] BACA si es inconveniente', 'BXCA', Curp::prefijoDesde('ANA', 'BACA', 'CRUZ'));

// Apellidos identicos: la inversion es indetectable por construccion.
$babaOk  = MexCore::Persona()->fromData('BABA940526HHGCCB07', 'ABRAHAM', 'BACA', 'BACA');
check('[limite] apellidos identicos coinciden', true, $babaOk->coincideConCurp());

// JUAN no esta en la lista de omitidos, asi que el prefijo termina en J de
// JUAN, no en J de JESUS. Que las dos palabras empiecen con J hace que la
// CURP no distinga, pero la consonante interna si: N de JUAN, no S de JESUS.
check('[limite] JUAN no se omite', 'RISJ', Curp::prefijoDesde('JUAN DE JESUS', 'RIVAS', 'SANCHEZ'));
check('[limite] consonante interna de JUAN', 'VNN', Curp::consonantesDesde('JUAN DE JESUS', 'RIVAS', 'SANCHEZ'));
// Y con JOSE si se omite: la derivacion pasa a JESUS y la consonante a S.
check('[limite] JOSE si se omite', 'RISJ', Curp::prefijoDesde('JOSE DE JESUS', 'RIVAS', 'SANCHEZ'));
check('[limite] consonante interna de JESUS', 'VNS', Curp::consonantesDesde('JOSE DE JESUS', 'RIVAS', 'SANCHEZ'));

// coincideConCurp() usa toNombreUnico(), que vuelve a unir los bloques, asi
// que la segmentacion no altera la validacion cruzada.
$juan = MexCore::Persona()->fromData('RISJ841105HJCVNN07', 'JUAN DE JESUS', 'RIVAS', 'SANCHEZ');
check('[limite] JUAN DE JESUS es un solo bloque', ['JUAN DE JESUS'], $juan->toNombres());
check('[limite] JUAN DE JESUS sin segundo nombre', '', $juan->toSegundoNombre());
check('[limite] JUAN DE JESUS coincide', true, $juan->coincideConCurp());

cerrarSuite();
