# MexCore

Librería PHP para la normalización y conversión de los **32 estados de la República Mexicana** más **Nacido en el Extranjero**.

Acepta identificadores en cualquier formato (CURP completa de 18 caracteres, código CURP de 2 letras, ID numérico, abreviatura, nombre completo) y los convierte a cualquier otro formato mediante una **interfaz fluida** (Fluent Interface).

## Requisitos

- PHP >= 8.0
- ext-mbstring

## Instalación

```bash
composer require irwinlopez1023/mex-core
```

## Uso básico

### Named constructors (entrada)

```php
use Irwinlopez1023\MexCore\MexCore;

// Desde CURP completa (18 caracteres)
$estado = MexCore::fromCurp('HEGG560427MVZRRL04');

// Desde código CURP de 2 letras
$estado = MexCore::fromCurp('SP');

// Desde ID numérico (1-33)
$estado = MexCore::fromNumber(24);

// Desde abreviatura
$estado = MexCore::fromAbbr('SLP');

// Desde nombre completo
$estado = MexCore::fromName('San Luis Potosí');
```

### Fluent Interface (salida)

```php
echo MexCore::fromCurp('VARG740228HSPLSN07')->toName();     // San Luis Potosí
echo MexCore::fromNumber(9)->toCurp();                       // DF
echo MexCore::fromAbbr('CDMX')->toNumber();                  // 9
echo MexCore::fromName('Nuevo León')->toAbbr();              // NL
echo MexCore::fromName('Nuevo León')->toCurp();              // NL
```

### Todo en una línea

```php
$curp = MexCore::fromName('Nuevo León')->toCurp();    // "NL"
$num  = MexCore::fromAbbr('CDMX')->toNumber();        // 9
$abbr = MexCore::fromCurp('GODE561231HASLRN09')->toAbbr(); // "AGS"
```

## Resiliencia

Los métodos de entrada normalizan automáticamente el texto:

| Entrada | Método | Resultado |
|---|---|---|
| `'S.L.P.'` | `fromAbbr()` | San Luis Potosí |
| `'slp'` | `fromAbbr()` | San Luis Potosí |
| `'san luis potosi'` | `fromName()` | San Luis Potosí |
| `'NUEVO LEON'` | `fromName()` | Nuevo León |
| `'Mexico'` | `fromName()` | Estado de México |
| `'EDOMEX'` | `fromName()` | Estado de México |
| `'DF'` | `fromName()` | Ciudad de México |
| `'Distrito Federal'` | `fromName()` | Ciudad de México |
| `'extranjero'` | `fromName()` | Nacido en el Extranjero |

## CURP completa vs código de 2 letras

`fromCurp()` acepta ambos formatos automáticamente:

```php
// CURP completa de 18 caracteres
MexCore::fromCurp('HEGG560427MVZRRL04')->toName(); // Veracruz

// Código de 2 letras
MexCore::fromCurp('VZ')->toName(); // Veracruz
```

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

## Excepciones

```php
use Irwinlopez1023\MexCore\InvalidStateException;

try {
    MexCore::fromCurp('AAAA000101HZZXXX00');
} catch (InvalidStateException $e) {
    echo $e->getMessage(); // "No se encontró un estado de México para: AAAA000101HZZXXX00"
}
```

## Métodos disponibles

### Entrada (Named constructors)

| Método | Descripción |
|---|---|
| `MexCore::fromCurp(string $curp)` | Acepta CURP de 18 caracteres o código de 2 letras |
| `MexCore::fromNumber(int\|string $number)` | ID numérico del 1 al 33 |
| `MexCore::fromAbbr(string $abbr)` | Abreviatura (tolera puntos, mayúsculas/minúsculas) |
| `MexCore::fromName(string $name)` | Nombre completo (tolera acentos, mayúsculas/minúsculas) |

### Salida

| Método | Retorna | Ejemplo |
|---|---|---|
| `->toNumber()` | `int` | `24` |
| `->toCurp()` | `string` | `"SP"` |
| `->toAbbr()` | `string` | `"SLP"` |
| `->toName()` | `string` | `"San Luis Potosí"` |

## Licencia

MIT License

Copyright (c) 2026 Irwin Lopez

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
