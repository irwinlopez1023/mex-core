# MexCore

Librería PHP para la normalización y conversión de los **32 estados de la República Mexicana** más **Nacido en el Extranjero**, y procesamiento inteligente de **nombres de personas mexicanas** a partir de datos crudos del INE.

API intuitiva con dos puntos de entrada: `MexCore::Estado()` y `MexCore::Persona()`.

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

$estado = MexCore::Estado()->fromCurp('HEGG560427MVZRRL04');
$estado = MexCore::Estado()->fromCurp('SP');
$estado = MexCore::Estado()->fromNumber(24);
$estado = MexCore::Estado()->fromAbbr('SLP');
$estado = MexCore::Estado()->fromName('San Luis Potosí');
```

### Salida

```php
echo $estado->toNumber(); // 24
echo $estado->toCurp();   // SP
echo $estado->toAbbr();   // SLP
echo $estado->toName();   // San Luis Potosí
```

### Fluent Interface

```php
$abbr = MexCore::Estado()->fromName('Nuevo León')->toAbbr(); // NL
$num  = MexCore::Estado()->fromAbbr('CDMX')->toNumber();     // 9
$name = MexCore::Estado()->fromCurp('VARG740228HSPLSN07')->toName(); // San Luis Potosí
```

### Resiliencia

| Entrada | Método | Resultado |
|---|---|---|
| `'S.L.P.'` | `fromAbbr()` | San Luis Potosí |
| `'slp'` | `fromAbbr()` | San Luis Potosí |
| `'san luis potosi'` | `fromName()` | San Luis Potosí |
| `'Mexico'` | `fromName()` | Estado de México |
| `'EDOMEX'` | `fromName()` | Estado de México |
| `'extranjero'` | `fromName()` | Nacido en el Extranjero |

### Listar todos los estados

```php
foreach (MexCore::Estado()->listar() as $e) {
    echo $e->toName();
}
```

---

## MexCore::Persona()

Procesa datos crudos (CURP, nombres, apellidos) y estructura los nombres aplicando la **lógica de pegamento** para nombres compuestos con conectores.

### Named constructor

```php
$persona = MexCore::Persona()->fromData(
    curp: 'VARG740228HSPLSN07',
    nombres: 'JUAN CARLOS',
    primerApellido: 'VALENCIA',
    segundoApellido: 'RAMIREZ',
);
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

### Integración con Estado

```php
$estado = $persona->toEstado();

echo $estado->toName();   // San Luis Potosí
echo $estado->toAbbr();   // SLP
echo $estado->toNumber(); // 24
echo $estado->toCurp();   // SP

// Todo en una línea
MexCore::Persona()->fromData('VARG740228HSPLSN07', 'JUAN CARLOS', 'VALENCIA', 'RAMIREZ')
    ->toEstado()
    ->toAbbr(); // SLP
```

### toNombreUnico() y combinar()

Cuando el sistema separa incorrectamente los nombres (como `MA. GUADALUPE`), puedes unirlos:

```php
$p = MexCore::Persona()->fromData('AAAA000101HBCXXX00', 'MA. GUADALUPE', 'TORRES', 'FLORES');

// Obtener nombres unidos como string (sin modificar el objeto)
echo $p->toNombreUnico(); // MA. GUADALUPE

// Crear una nueva Persona con los nombres combinados
$combinada = $p->combinar();
echo $combinada->toPrimerNombre(); // MA. GUADALUPE
echo $combinada->toSegundoNombre(); // (vacío)
echo $combinada->toNombreCompleto(); // MA. GUADALUPE TORRES FLORES
```

### Lógica de pegamento (conectores)

| Entrada | Primer nombre | Segundo nombre |
|---|---|---|
| `MARIA DEL ROCIO` | `MARIA DEL ROCIO` | _(vacío)_ |
| `JOSE DE JESUS` | `JOSE DE JESUS` | _(vacío)_ |
| `MARIA DE LOS ANGELES` | `MARIA DE LOS ANGELES` | _(vacío)_ |
| `JUAN CARLOS` | `JUAN` | `CARLOS` |
| `LUIS ALBERTO` | `LUIS` | `ALBERTO` |

### Manejo de abreviaturas con punto

```php
// Con punto (por defecto)
$p = MexCore::Persona()->fromData('AAAA000101HBCXXX00', 'MA. GUADALUPE', 'TORRES', 'FLORES');
echo $p->toPrimerNombre(); // MA.

// Sin punto
$p = MexCore::Persona()->fromData('AAAA000101HBCXXX00', 'MA. GUADALUPE', 'TORRES', 'FLORES', mantenerPunto: false);
echo $p->toPrimerNombre(); // MA
```

---

## Listado completo de estados

| # | CURP | ABBR | Nombre |
|---|---|---|---|
| 1 | AS | AGS | Aguascalientes |
| 2 | BC | BC | Baja California |
| 3 | BS | BCS | Baja California Sur |
| 4 | CC | CAMP | Campeche |
| 5 | CL | COAH | Coahuila |
| 6 | CM | COL | Colima |
| 7 | CS | CHIS | Chiapas |
| 8 | CH | CHIH | Chihuahua |
| 9 | DF | CDMX | Ciudad de México |
| 10 | DG | DGO | Durango |
| 11 | GT | GTO | Guanajuato |
| 12 | GR | GRO | Guerrero |
| 13 | HG | HGO | Hidalgo |
| 14 | JC | JAL | Jalisco |
| 15 | MS | MEX | Estado de México |
| 16 | MC | MICH | Michoacán |
| 17 | MN | MOR | Morelos |
| 18 | NT | NAY | Nayarit |
| 19 | NL | NL | Nuevo León |
| 20 | OC | OAX | Oaxaca |
| 21 | PL | PUE | Puebla |
| 22 | QT | QRO | Querétaro |
| 23 | QR | QR | Quintana Roo |
| 24 | SP | SLP | San Luis Potosí |
| 25 | SL | SIN | Sinaloa |
| 26 | SR | SON | Sonora |
| 27 | TC | TAB | Tabasco |
| 28 | TS | TAMPS | Tamaulipas |
| 29 | TL | TLAX | Tlaxcala |
| 30 | VZ | VER | Veracruz |
| 31 | YN | YUC | Yucatán |
| 32 | ZS | ZAC | Zacatecas |
| 33 | NE | EXT | Nacido en el Extranjero |

---

## Excepciones

```php
use Irwinlopez1023\MexCore\InvalidStateException;

try {
    MexCore::Estado()->fromCurp('AAAA000101HZZXXX00');
} catch (InvalidStateException $e) {
    echo $e->getMessage();
}
```

---

## API completa

### MexCore::Estado()

| Método | Descripción | Retorno |
|---|---|---|
| `->fromCurp(string $curp)` | CURP completa (18 chars) o código (2 letras) | `Estado` |
| `->fromNumber(int\|string $number)` | ID numérico 1-33 | `Estado` |
| `->fromAbbr(string $abbr)` | Abreviatura (tolera puntos, mayús/minús) | `Estado` |
| `->fromName(string $name)` | Nombre completo (tolera acentos, mayús/minús) | `Estado` |
| `->listar()` | Todos los estados | `Estado[]` |

### Estado (value object)

| Método | Retorna |
|---|---|
| `->toNumber()` | `int` |
| `->toCurp()` | `string` |
| `->toAbbr()` | `string` |
| `->toName()` | `string` |

### MexCore::Persona()

| Método | Descripción | Retorno |
|---|---|---|
| `->fromData(curp, nombres, paterno, materno, mantenerPunto)` | Procesa datos crudos de persona | `Persona` |

### Persona (value object)

| Método | Retorna |
|---|---|
| `->toCurp()` | `string` (CURP completa) |
| `->toPrimerNombre()` | `string` |
| `->toSegundoNombre()` | `string` |
| `->toPrimerApellido()` | `string` |
| `->toSegundoApellido()` | `string` |
| `->toNombreCompleto()` | `string` |
| `->toNombreUnico()` | `string` (primerNombre + segundoNombre) |
| `->combinar()` | `Persona` (nueva instancia con nombres fusionados) |
| `->toArray()` | `array` |
| `->toEstado()` | `Estado` |

---

## Licencia

MIT License — Copyright (c) 2024 Irwin Lopez
