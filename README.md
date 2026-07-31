# MexCore

Librería PHP para la normalización y conversión de los **32 estados de la República Mexicana** más **Nacido en el Extranjero**, procesamiento inteligente de **nombres de personas mexicanas** a partir de datos crudos del INE, y **validación de CURP** según el Instructivo Normativo de RENAPO.

API intuitiva con dos puntos de entrada: `MexCore::Estado()` y `MexCore::Persona()`.

```php
use Irwinlopez1023\MexCore\MexCore;

$p = MexCore::Persona()->fromData('SOSR650222MPLSNC03', 'MARIA DEL ROCIO', 'SOSA', 'SANCHEZ');

echo $p->toPrimerNombre();   // MARIA DEL ROCIO  (no se parte en MARIA + DEL ROCIO)
echo $p->toEdad();           // edad a partir de la CURP
echo $p->toEstado()->toNombre(); // Puebla
var_dump($p->tieneCurpValida()); // true
var_dump($p->coincideConCurp()); // true: los nombres cuadran con la CURP
```

## Requisitos

- PHP >= 8.0
- ext-mbstring

## Instalación

```bash
composer require irwinlopez1023/mex-core
```

---

## MexCore::Estado()

Acepta identificadores de estado en cualquier formato y los convierte a cualquier otro formato.

### Named constructors

```php
use Irwinlopez1023\MexCore\MexCore;

$estado = MexCore::Estado()->fromCurp('HEGG560427MVZRRL04'); // CURP completa
$estado = MexCore::Estado()->fromCurp('SP');                 // o solo el código
$estado = MexCore::Estado()->fromNumero(24);
$estado = MexCore::Estado()->fromNumero('09');               // con cero a la izquierda
$estado = MexCore::Estado()->fromAbreviatura('SLP');
$estado = MexCore::Estado()->fromNombre('San Luis Potosí');
```

### Salida

```php
echo $estado->toNumero();            // 24
echo $estado->toNumeroFormateado();  // 24  (y '09' para la CDMX)
echo $estado->toCurp();              // SP
echo $estado->toAbreviatura();       // SLP
echo $estado->toIso();               // SLP
echo $estado->toNombre();            // San Luis Potosí

var_dump($estado->esExtranjero());   // false
```

### Abreviatura contra código ISO

Son dos catálogos distintos y es importante no confundirlos.

`toAbreviatura()` devuelve la abreviatura de uso común, y **tiene largo variable**: de dos letras (`BC`, `NL`, `QR`) a cinco (`TAMPS`). Sólo 22 de las 33 entidades tienen tres.

`toIso()` devuelve el código **ISO 3166-2:MX, siempre de tres letras**: `BCN`, `NLE`, `ROO`, `TAM`, `CMX`. Es el que piden las APIs con un catálogo cerrado.

```php
$bc = MexCore::Estado()->fromCurp('XXXX000101HBCXXX00');

$bc->toAbreviatura(); // 'BC'   <- dos letras, rompe una API que espera tres
$bc->toIso();         // 'BCN'  <- ISO 3166-2:MX
```

Las 16 entidades donde los dos difieren:

| Clave | Abreviatura | ISO | | Clave | Abreviatura | ISO |
|---|---|---|---|---|---|---|
| 1 | AGS | AGU | | 16 | MICH | MIC |
| 2 | BC | **BCN** | | 19 | NL | **NLE** |
| 4 | CAMP | CAM | | 22 | QRO | QUE |
| 5 | COAH | COA | | 23 | QR | **ROO** |
| 7 | CHIS | CHP | | 28 | TAMPS | **TAM** |
| 8 | CHIH | CHH | | 29 | TLAX | TLA |
| 9 | CDMX | **CMX** | | 33 | EXT | **NE** |
| 10 | DGO | DUR | | 11 | GTO | GUA |
| 13 | HGO | HID | | | | |

La norma ISO sólo cubre las 32 entidades federativas. Para Nacido en el Extranjero se devuelve `NE`, que es el código que ya usa la CURP.

El código ISO también sirve de entrada. `fromIso()` es estricto y sólo acepta el catálogo ISO; `fromAbreviatura()` y `desde()` aceptan las dos formas:

```php
MexCore::Estado()->fromIso('BCN')->toNumero();          // 2
MexCore::Estado()->fromAbreviatura('BCN')->toNumero();  // 2
MexCore::Estado()->desde('BCN')->toNumero();            // 2
MexCore::Estado()->desde('BC')->toNumero();             // 2

MexCore::Estado()->fromIso('BC');    // InvalidStateException
MexCore::Estado()->fromIso('TAMPS'); // InvalidStateException
```

### Fluent Interface

```php
$abr = MexCore::Estado()->fromNombre('Nuevo León')->toAbreviatura(); // NL
$num = MexCore::Estado()->fromAbreviatura('CDMX')->toNumero();       // 9
$nom = MexCore::Estado()->fromCurp('VARG740228HSPLSN07')->toNombre(); // San Luis Potosí
```

### desde(): detección automática del formato

Cuando la columna de origen es mixta y una misma celda puede traer `24`, `'SP'`, `'SLP'` o `'San Luis Potosi'`, `desde()` detecta el formato y resuelve. `intentarDesde()` es la variante que devuelve `null` en lugar de lanzar, para no envolver cada renglón de una carga masiva en un `try/catch`.

```php
MexCore::Estado()->desde(24)->toNombre();                    // San Luis Potosí
MexCore::Estado()->desde('SP')->toNombre();                  // San Luis Potosí
MexCore::Estado()->desde('SLP')->toNombre();                 // San Luis Potosí
MexCore::Estado()->desde('VARG740228HSPLSN07')->toNombre();  // San Luis Potosí

MexCore::Estado()->intentarDesde('Narnia'); // null, no lanza
MexCore::Estado()->existe('PUE');           // true
MexCore::Estado()->existe(99);              // false
```

El orden de resolución es número, CURP, abreviatura, nombre. `BC`, `NL` y `QR` son a la vez código de CURP y abreviatura, pero apuntan a la misma entidad en los dos catálogos, así que la precedencia no cambia el resultado.

### Resiliencia

| Entrada | Método | Resultado |
|---|---|---|
| `'S.L.P.'` | `fromAbreviatura()` | San Luis Potosí |
| `'S. L. P.'` | `fromAbreviatura()` | San Luis Potosí |
| `'slp'` | `fromAbreviatura()` | San Luis Potosí |
| `"\tYUC\t"` | `fromAbreviatura()` | Yucatán |
| `'san luis potosi'` | `fromNombre()` | San Luis Potosí |
| `'MICHOACÁN'` | `fromNombre()` | Michoacán |
| `'  Baja   California  '` | `fromNombre()` | Baja California |
| `"San\u{00A0}Luis Potosí"` | `fromNombre()` | San Luis Potosí |
| `'Mexico'` / `'EDOMEX'` / `'EDO MEX'` | `desde()` | Estado de México |
| `'Distrito Federal'` / `'CDMX'` / `'DF'` | `desde()` | Ciudad de México |
| `'extranjero'` | `desde()` | Nacido en el Extranjero |
| `'SOSR650222 MPLSNC03'` | `fromCurp()` | Puebla |

Las tres normalizaciones colapsan cualquier separador Unicode, incluido el espacio duro `U+00A0` que llega al copiar de un PDF. Los nombres además pierden acentos, y las abreviaturas pierden puntos, espacios y guiones.

Una CURP no lleva espacios, así que en lugar de colapsarlos se eliminan todos: `'SOSR650222 MPLSNC03'` vuelve a alinear sus posiciones 11 y 12 y resuelve Puebla. El mismo criterio rige en `Curp` y en `PersonaQuery`, de modo que una entrada que resuelve el estado también pasa `Curp::esValida()` y deriva correctamente.

`fromNumero()` en cambio es estricto: solo acepta dígitos. `'24abc'` y `'24.9'` lanzan `InvalidStateException` en vez de devolver San Luis Potosí en silencio.

### Nombres oficiales

Los nombres constitucionales completos, que son los que trae el acta de nacimiento, resuelven igual que los cortos.

```php
MexCore::Estado()->fromNombre('Coahuila de Zaragoza')->toNumero();            // 5
MexCore::Estado()->fromNombre('Michoacán de Ocampo')->toNumero();             // 16
MexCore::Estado()->fromNombre('Veracruz de Ignacio de la Llave')->toNumero(); // 30
MexCore::Estado()->fromNombre('Querétaro de Arteaga')->toNumero();            // 22
```

### Value object

```php
$cdmx = MexCore::Estado()->fromNumero(9);

$cdmx->toArray();
// ['numero' => 9, 'nombre' => 'Ciudad de México', 'curp' => 'DF',
//  'abreviatura' => 'CDMX', 'iso' => 'CMX']

json_encode($cdmx);
// {"numero":9,"nombre":"Ciudad de México","curp":"DF","abreviatura":"CDMX","iso":"CMX"}

// equals() compara datos, no instancias: da igual por dónde entró.
MexCore::Estado()->fromAbreviatura('SLP')->equals(MexCore::Estado()->fromNumero(24)); // true
```

### Listar todos los estados

```php
foreach (MexCore::Estado()->listar() as $e) {
    echo $e->toNumeroFormateado() . ' ' . $e->toNombre() . PHP_EOL;
}
```

### Alias en inglés

La API principal está en español, igual que `Persona`. Los nombres en inglés siguen disponibles como alias delegados, así que el código existente no se rompe:

| Alias | Equivale a |
|---|---|
| `->fromNumber()` | `->fromNumero()` |
| `->fromAbbr()` | `->fromAbreviatura()` |
| `->fromName()` | `->fromNombre()` |
| `->toNumber()` | `->toNumero()` |
| `->toAbbr()` | `->toAbreviatura()` |
| `->toName()` | `->toNombre()` |

---

## MexCore::Persona()

Procesa datos crudos (CURP, nombres, apellidos) y estructura los nombres aplicando la **lógica de pegamento** para nombres compuestos con conectores.

### Named constructors

```php
$persona = MexCore::Persona()->fromData(
    curp: 'VARG740228HSPLSN07',
    nombres: 'JUAN CARLOS',
    primerApellido: 'VALENCIA',
    segundoApellido: 'RAMIREZ',
);
```

`fromData()` recibe los dos apellidos como parámetros posicionales contiguos, así que invertir paterno y materno no produce ningún error: solo datos incorrectos silenciosos. Para cargas masivas conviene `fromArray()`, que obliga a nombrar cada campo:

```php
$persona = MexCore::Persona()->fromArray([
    'curp'            => 'VARG740228HSPLSN07',
    'nombres'         => 'JUAN CARLOS',
    'primerApellido'  => 'VALENCIA',
    'segundoApellido' => 'RAMIREZ',
]);
```

### Salida

```php
echo $persona->toPrimerNombre();    // JUAN
echo $persona->toSegundoNombre();   // CARLOS
echo $persona->toPrimerApellido();  // VALENCIA
echo $persona->toSegundoApellido(); // RAMIREZ
echo $persona->toNombreCompleto();  // JUAN CARLOS VALENCIA RAMIREZ
echo $persona->toCurp();            // VARG740228HSPLSN07
print_r($persona->toArray());
```

Formatos adicionales:

```php
echo $persona->toNombreUnico();              // JUAN CARLOS
echo $persona->toNombreCompletoInvertido();  // VALENCIA RAMIREZ, JUAN CARLOS
echo $persona->toIniciales();                // JCVR
print_r($persona->toNombres());              // ['JUAN', 'CARLOS']
echo json_encode($persona);                  // implementa JsonSerializable
```

`toNombres()` devuelve los bloques de nombre tal como los detectó la lógica de pegamento, sin colapsarlos. Es la única forma de recuperar el tercer nombre y siguientes, porque `toSegundoNombre()` los junta en una sola cadena:

```php
$p = MexCore::Persona()->fromData('VARG740228HSPLSN07', 'JUAN CARLOS ALBERTO', 'VALENCIA', 'RAMIREZ');

echo $p->toSegundoNombre(); // CARLOS ALBERTO
print_r($p->toNombres());   // ['JUAN', 'CARLOS', 'ALBERTO']
```

### Lógica de pegamento (conectores)

En México los nombres de pila compuestos por preposiciones o artículos no deben separarse de forma tradicional. La librería usa un diccionario de conectores (`DE`, `DEL`, `LA`, `LAS`, `LOS`, `Y`, `MAC`, `MC`, `VAN`, `VON`) que se pegan **hacia atrás y hacia adelante**, agrupando el bloque completo.

| Entrada | Primer nombre | Segundo nombre |
|---|---|---|
| `MARIA DEL ROCIO` | `MARIA DEL ROCIO` | _(vacío)_ |
| `JOSE DE JESUS` | `JOSE DE JESUS` | _(vacío)_ |
| `MARIA DE LOS ANGELES` | `MARIA DE LOS ANGELES` | _(vacío)_ |
| `MARIA DEL ROCIO ALEJANDRA` | `MARIA DEL ROCIO` | `ALEJANDRA` |
| `JUAN CARLOS DE JESUS` | `JUAN` | `CARLOS DE JESUS` |
| `JUAN CARLOS` | `JUAN` | `CARLOS` |

Un **segundo grupo de conectores cierra el bloque anterior y abre uno nuevo**, en lugar de seguir absorbiendo palabras indefinidamente:

| Entrada | Primer nombre | Segundo nombre |
|---|---|---|
| `MARIA DE LA LUZ DEL CARMEN` | `MARIA DE LA LUZ` | `DEL CARMEN` |
| `MARIA DE LOS ANGELES DE LA CRUZ` | `MARIA DE LOS ANGELES` | `DE LA CRUZ` |

Si los datos vienen truncados y el bloque termina en un conector colgante, el conector se descarta antes que devolver un nombre que no existe:

| Entrada | Primer nombre | Segundo nombre |
|---|---|---|
| `MARIA DE` | `MARIA` | _(vacío)_ |
| `JUAN CARLOS DE` | `JUAN` | `CARLOS` |

### Abreviaturas

Las abreviaturas típicas del INE (`MA.`, `J.`, `GPE.`) se tratan como pegamento, pero **solo hacia adelante**: nunca se pegan a la palabra anterior. Se reconocen por el punto final, por tener una sola letra, o por estar en el diccionario `MA, M, J, GPE, FCO, FCA, ANT`.

| Entrada | Primer nombre | Segundo nombre |
|---|---|---|
| `MA. GUADALUPE` | `MA. GUADALUPE` | _(vacío)_ |
| `MA GUADALUPE` | `MA GUADALUPE` | _(vacío)_ |
| `J. JESUS` | `J. JESUS` | _(vacío)_ |
| `MA. GUADALUPE ALEJANDRA` | `MA. GUADALUPE` | `ALEJANDRA` |
| `JOSE MA. DEL CARMEN` | `JOSE` | `MA. DEL CARMEN` |

El último caso es el motivo de que las abreviaturas no peguen hacia atrás: `JOSE MA. DEL CARMEN` da el mismo resultado que `JOSE MARIA DEL CARMEN`.

El parámetro `mantenerPunto` decide si el punto sobrevive a la normalización. Solo aplica a los nombres, no a los apellidos:

```php
$p = MexCore::Persona()->fromData('AAAA000101HBCXXX00', 'MA. GUADALUPE', 'TORRES', 'FLORES');
echo $p->toPrimerNombre(); // MA. GUADALUPE

$p = MexCore::Persona()->fromData('AAAA000101HBCXXX00', 'MA. GUADALUPE', 'TORRES', 'FLORES', mantenerPunto: false);
echo $p->toPrimerNombre(); // MA GUADALUPE
```

### separarNombres()

Aplica la lógica de pegamento a una cadena sin construir una `Persona`. Útil para inspeccionar la segmentación o para partir una cadena de apellidos completa:

```php
print_r(MexCore::Persona()->separarNombres('MARIA DEL ROCIO ALEJANDRA'));
// ['MARIA DEL ROCIO', 'ALEJANDRA']
```

### Diccionarios configurables

`withConectores()` y `withAbreviaturas()` devuelven copias inmutables, así que se puede ajustar el comportamiento sin editar la librería ni contaminar la instancia compartida por `MexCore::Persona()`:

```php
$query = MexCore::Persona()
    ->withConectores(['DE', 'DEL', 'LA'])
    ->withAbreviaturas(['MA', 'J']);

$query->separarNombres('MARIA DE LOS ANGELES');
// ['MARIA DE LOS', 'ANGELES']  <- LOS ya no es conector, cierra el bloque
```

### combinar() y separar()

Cuando el origen trae dos nombres reales que se quieren tratar como uno, `combinar()` los fusiona. La operación es **reversible**, porque la `Persona` conserva internamente los bloques originales:

```php
$p = MexCore::Persona()->fromData('VARG740228HSPLSN07', 'JUAN CARLOS', 'VALENCIA', 'RAMIREZ');

$c = $p->combinar();
echo $c->toPrimerNombre();  // JUAN CARLOS
echo $c->toSegundoNombre(); // (vacío)
var_dump($c->estaCombinado()); // true

$v = $c->separar();
echo $v->toSegundoNombre();   // CARLOS
var_dump($v->equals($p));     // true
```

### Datos derivados de la CURP

```php
$p = MexCore::Persona()->fromData('SOSR650222MPLSNC03', 'MARIA DEL ROCIO', 'SOSA', 'SANCHEZ');

echo $p->toSexo();                              // M
echo $p->toFechaNacimiento()->format('Y-m-d');  // 1965-02-22
echo $p->toEdad();                              // edad al día de hoy
echo $p->toEdad(new DateTimeImmutable('2020-01-01')); // 54
echo $p->toDigitoVerificador();                 // 3
var_dump($p->tieneCurpValida());                // true
```

`toEdad()` acepta una fecha de referencia para que el resultado sea determinista en pruebas. `toFechaNacimiento()` deduce el siglo del homoclave (posición 17): dígito para nacidos antes del 2000, letra a partir del 2000. Devuelve `null` si la fecha no existe, por ejemplo un `050231`.

### Validación cruzada: `coincideConCurp()`

Las primeras cuatro letras de la CURP y las tres consonantes internas se derivan del primer apellido, el segundo apellido y el nombre. Eso permite confirmar que los campos capturados corresponden entre sí, y detectar registros con apellidos invertidos o mal transcritos:

```php
$ok = MexCore::Persona()->fromData('SOSR650222MPLSNC03', 'MARIA DEL ROCIO', 'SOSA', 'SANCHEZ');
var_dump($ok->coincideConCurp()); // true

// Paterno y materno invertidos: derivaria SASR/NSC, no SOSR/SNC
$mal = MexCore::Persona()->fromData('SOSR650222MPLSNC03', 'MARIA DEL ROCIO', 'SANCHEZ', 'SOSA');
var_dump($mal->coincideConCurp()); // false
```

Es una **heurística**, no una validación estricta. Puede dar falso negativo en casos exóticos (apellidos compuestos con guion, homonimias resueltas a mano por RENAPO) y no detecta la inversión cuando ambos apellidos son idénticos. Conviene usarla para marcar registros a revisar, no para rechazarlos.

### Integración con Estado

```php
$estado = $persona->toEstado();

echo $estado->toNombre();      // San Luis Potosí
echo $estado->toAbreviatura(); // SLP
echo $estado->toNumero();      // 24
echo $estado->toCurp();        // SP

// Todo en una línea
MexCore::Persona()->fromData('VARG740228HSPLSN07', 'JUAN CARLOS', 'VALENCIA', 'RAMIREZ')
    ->toEstado()
    ->toAbreviatura(); // SLP
```

### Normalización de entrada

Toda la entrada pasa a mayúsculas y colapsa cualquier separador unicode a un espacio simple, incluido el espacio duro `U+00A0` que llega al copiar texto de un PDF o de una credencial digitalizada. Los apellidos reciben el mismo tratamiento que los nombres, así que `DE  LA  CRUZ` no conserva los espacios dobles.

---

## Curp

Las reglas del Instructivo Normativo de RENAPO viven en una clase estática aparte, para poder validar una cadena de 18 caracteres sin construir una `Persona`.

```php
use Irwinlopez1023\MexCore\Curp;

var_dump(Curp::esValida('SOSR650222MPLSNC03')); // true
echo Curp::digitoVerificador('SOSR650222MPLSNC0'); // 3
echo Curp::sexo('SOSR650222MPLSNC03');             // M
echo Curp::fechaNacimiento('SOSR650222MPLSNC03')->format('Y-m-d'); // 1965-02-22
```

`esValida()` comprueba estructura, fecha real, entidad existente y dígito verificador. La derivación de letras también es pública:

```php
echo Curp::prefijoDesde('MARIA DEL ROCIO', 'SOSA', 'SANCHEZ');     // SOSR
echo Curp::consonantesDesde('MARIA DEL ROCIO', 'SOSA', 'SANCHEZ'); // SNC
```

Las tres reglas del instructivo están implementadas: se descartan las partículas del apellido (`DE LA LUZ` deriva de `LUZ`), se omite `MARIA` o `JOSE` cuando hay un nombre posterior (`MARIA DEL ROCIO` deriva de `ROCIO`, y `MA. TERESA` de `TERESA`), y si las cuatro primeras letras forman una de las 78 palabras inconvenientes la segunda se sustituye por `X` (`ANA BACA CRUZ` da `BXCA`).

---

## Listado completo de estados

| Clave | CURP | Abreviatura | ISO | Nombre |
|---|---|---|---|---|
| 1 | AS | AGS | AGU | Aguascalientes |
| 2 | BC | BC | BCN | Baja California |
| 3 | BS | BCS | BCS | Baja California Sur |
| 4 | CC | CAMP | CAM | Campeche |
| 5 | CL | COAH | COA | Coahuila |
| 6 | CM | COL | COL | Colima |
| 7 | CS | CHIS | CHP | Chiapas |
| 8 | CH | CHIH | CHH | Chihuahua |
| 9 | DF | CDMX | CMX | Ciudad de México |
| 10 | DG | DGO | DUR | Durango |
| 11 | GT | GTO | GUA | Guanajuato |
| 12 | GR | GRO | GRO | Guerrero |
| 13 | HG | HGO | HID | Hidalgo |
| 14 | JC | JAL | JAL | Jalisco |
| 15 | MC | MEX | MEX | Estado de México |
| 16 | MN | MICH | MIC | Michoacán |
| 17 | MS | MOR | MOR | Morelos |
| 18 | NT | NAY | NAY | Nayarit |
| 19 | NL | NL | NLE | Nuevo León |
| 20 | OC | OAX | OAX | Oaxaca |
| 21 | PL | PUE | PUE | Puebla |
| 22 | QT | QRO | QUE | Querétaro |
| 23 | QR | QR | ROO | Quintana Roo |
| 24 | SP | SLP | SLP | San Luis Potosí |
| 25 | SL | SIN | SIN | Sinaloa |
| 26 | SR | SON | SON | Sonora |
| 27 | TC | TAB | TAB | Tabasco |
| 28 | TS | TAMPS | TAM | Tamaulipas |
| 29 | TL | TLAX | TLA | Tlaxcala |
| 30 | VZ | VER | VER | Veracruz |
| 31 | YN | YUC | YUC | Yucatán |
| 32 | ZS | ZAC | ZAC | Zacatecas |
| 33 | NE | EXT | NE | Nacido en el Extranjero |

La clave es la del INEGI. El código de la CURP de la capital sigue siendo `DF`, aunque la entidad se llame Ciudad de México desde 2016. La columna ISO es [ISO 3166-2:MX](https://en.wikipedia.org/wiki/ISO_3166-2:MX), sin el prefijo `MX-`.

Cuidado con las tres claves que empiezan con M: no siguen ningún patrón mnemotécnico y es fácil rotarlas. `MC` es **M**éxi**c**o, `MN` es Michoacá**n** y `MS` es Morel**os**.

Querétaro es `QT` en las CURP reales; varios catálogos públicos la listan como `QO`, que se acepta de entrada pero no es la que devuelve `toCurp()`.

---

## Excepciones

Todos los `from*()` y `desde()` lanzan `InvalidStateException` cuando el valor no resuelve.

```php
use Irwinlopez1023\MexCore\InvalidStateException;

try {
    MexCore::Estado()->fromCurp('AAAA000101HZZXXX00'); // la entidad ZZ no existe
} catch (InvalidStateException $e) {
    echo $e->getMessage();
}
```

Para procesar cargas masivas sin un `try/catch` por renglón, `intentarDesde()` devuelve `null` y `existe()` devuelve `bool`.

```php
foreach ($renglones as $r) {
    $estado = MexCore::Estado()->intentarDesde($r['entidad']);

    if ($estado === null) {
        $rechazados[] = $r;
        continue;
    }

    $limpios[] = $r + ['clave' => $estado->toNumero()];
}
```

---

## API completa

### MexCore::Estado()

| Método | Descripción | Retorno |
|---|---|---|
| `->fromCurp(string $curp)` | CURP completa (18 chars) o código (2 letras) | `Estado` |
| `->fromNumero(int\|string $numero)` | Clave del INEGI 1-33, tolera `'09'` | `Estado` |
| `->fromAbreviatura(string $abreviatura)` | Abreviatura de uso común o código ISO | `Estado` |
| `->fromIso(string $iso)` | Sólo ISO 3166-2:MX (`BCN`, `TAM`), más `NE` | `Estado` |
| `->fromNombre(string $nombre)` | Nombre corto u oficial (tolera acentos, mayús/minús) | `Estado` |
| `->desde(int\|string $valor)` | Detecta el formato: número, CURP, abreviatura o nombre | `Estado` |
| `->intentarDesde(int\|string $valor)` | Igual que `desde()` pero sin lanzar | `?Estado` |
| `->existe(int\|string $valor)` | Si el valor resuelve alguna entidad | `bool` |
| `->listar()` | Las 32 entidades más Nacido en el Extranjero | `Estado[]` |

Alias en inglés: `fromNumber()`, `fromAbbr()`, `fromName()`.

### Estado (value object)

| Método | Retorna |
|---|---|
| `->toNumero()` | `int` (clave del INEGI) |
| `->toNumeroFormateado()` | `string` (dos dígitos: `'09'`) |
| `->toCurp()` | `string` (código de las posiciones 11-12) |
| `->toAbreviatura()` | `string` (uso común, **largo variable**: 2 a 5) |
| `->toIso()` | `string` (ISO 3166-2:MX, **siempre 3**, más `NE`) |
| `->toNombre()` | `string` |
| `->esExtranjero()` | `bool` |
| `->equals(Estado $otro)` | `bool` |
| `->toArray()` | `array` |

`Estado` implementa `JsonSerializable`, igual que `Persona`, así que `json_encode()` produce las mismas llaves que `toArray()`. Alias en inglés: `toNumber()`, `toAbbr()`, `toName()`.

### MexCore::Persona()

| Método | Descripción | Retorno |
|---|---|---|
| `->fromData(curp, nombres, paterno, materno, mantenerPunto)` | Procesa datos crudos de persona | `Persona` |
| `->fromArray(array $datos)` | Igual, con llaves nombradas | `Persona` |
| `->separarNombres(string $nombres, bool $mantenerPunto)` | Bloques de nombre, sin construir `Persona` | `list<string>` |
| `->withConectores(array $conectores)` | Copia con otro diccionario de conectores | `PersonaQuery` |
| `->withAbreviaturas(array $abreviaturas)` | Copia con otro diccionario de abreviaturas | `PersonaQuery` |

### Persona (value object)

| Método | Retorna |
|---|---|
| `->toCurp()` | `string` (CURP completa) |
| `->toPrimerNombre()` | `string` |
| `->toSegundoNombre()` | `string` (segundo y siguientes, unidos) |
| `->toPrimerApellido()` | `string` |
| `->toSegundoApellido()` | `string` |
| `->toNombres()` | `list<string>` (bloques sin colapsar) |
| `->toNombreCompleto()` | `string` |
| `->toNombreCompletoInvertido()` | `string` (`APELLIDOS, NOMBRES`) |
| `->toNombreUnico()` | `string` (todos los bloques de nombre) |
| `->toIniciales()` | `string` |
| `->combinar()` | `Persona` (nombres fusionados) |
| `->separar()` | `Persona` (revierte `combinar()`) |
| `->estaCombinado()` | `bool` |
| `->equals(Persona $otra)` | `bool` |
| `->toSexo()` | `string` (`H`, `M`, `X` o vacío) |
| `->toFechaNacimiento()` | `?DateTimeImmutable` |
| `->toEdad(?DateTimeImmutable $referencia)` | `?int` |
| `->toDigitoVerificador()` | `string` |
| `->tieneCurpValida()` | `bool` |
| `->coincideConCurp()` | `bool` (heurística) |
| `->toArray()` | `array` |
| `->toEstado()` | `Estado` |

`Persona` implementa `JsonSerializable`, así que `json_encode()` produce las mismas llaves que `toArray()`.

### Curp (estática)

| Método | Retorna |
|---|---|
| `Curp::esValida(string $curp)` | `bool` (estructura, fecha, entidad y dígito) |
| `Curp::digitoVerificador(string $curp)` | `string` |
| `Curp::sexo(string $curp)` | `string` |
| `Curp::fechaNacimiento(string $curp)` | `?DateTimeImmutable` |
| `Curp::prefijoDesde(nombres, paterno, materno)` | `string` (posiciones 0-3) |
| `Curp::consonantesDesde(nombres, paterno, materno)` | `string` (posiciones 13-15) |

---

## Pruebas

Las dos suites comparten el harness de `tests/harness.php`, así que se pueden correr por separado o juntas:

```bash
php test.php          # las dos suites, un solo resumen
php test_persona.php  # solo Personas
php test_estados.php  # solo Estados
```

`test_persona.php` trae unas 145 aserciones sobre la lógica de pegamento, la normalización, los formatos de salida y la derivación de CURP, incluidas nueve CURP reales verificadas contra prefijo, consonantes internas y dígito verificador.

`test_estados.php` trae unas 290 sobre el catálogo completo (las 33 entidades resueltas por sus cinco identificadores, en ida y vuelta), los alias, los nombres oficiales, los códigos ISO, la resiliencia de entrada, la detección de CURP por forma y la congruencia del value object con `Persona`.

Cualquiera de los tres sale con código 1 si algo falla, así que sirven tal cual en CI.

---

## Licencia

MIT License — Copyright (c) 2024 Irwin Lopez
