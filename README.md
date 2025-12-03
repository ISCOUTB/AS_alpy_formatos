# Block MBTI 93 - Test de Personalidad para Savio

## Descripción

**Block MBTI 93** es un plugin de bloque para Savio que permite evaluar la personalidad de los estudiantes utilizando un test basado en el modelo Myers-Briggs Type Indicator (MBTI) con 93 preguntas. Este bloque proporciona herramientas tanto para estudiantes como para profesores, facilitando la comprensión de los perfiles de personalidad en el contexto educativo.

### Características Principales

-  **Test de 93 preguntas** dividido en 4 secciones temáticas
-  **Análisis visual** de resultados con gráficos radar y de barras
-  **Vista para estudiantes** con resultados personalizados y explicaciones de su tipo MBTI
-  **Vista para profesores** con estadísticas grupales y análisis de tendencias
-  **Reportes exportables** en formato CSV y PDF
-  **Panel de administración** para gestionar participaciones
-  **Soporte multiidioma** (Español e Inglés)

---

##  ¿Qué es el MBTI?

El **Myers-Briggs Type Indicator (MBTI)** es un cuestionario psicológico que clasifica la personalidad en 16 tipos diferentes basándose en 4 dimensiones:

1. **Extraversión (E) vs. Introversión (I)**: Dónde enfocas tu energía
2. **Sensación (S) vs. Intuición (N)**: Cómo procesas la información
3. **Pensamiento (T) vs. Sentimiento (F)**: Cómo tomas decisiones
4. **Juicio (J) vs. Percepción (P)**: Cómo te organizas con el mundo exterior

Los 16 tipos resultantes son: **ISTJ, ISFJ, INFJ, INTJ, ISTP, ISFP, INFP, INTP, ESTP, ESFP, ENFP, ENTP, ESTJ, ESFJ, ENFJ, ENTJ**.

---

## Instalación

### Requisitos Previos

- Moodle 4.2 o superior
- PHP 7.4 o superior
- Privilegios de administrador en Moodle

### Pasos de Instalación

1. **Descarga el plugin**
   ```bash
   cd /path/to/moodle/blocks/
   git clone https://github.com/ISCOUTB/AS_alpy_formatos.git mbti_93
   ```

2. **Renombra la carpeta** (si es necesario)
   ```bash
   mv AS_alpy_formatos mbti_93
   ```

3. **Accede a Moodle como administrador**
   - Navega a: `Administración del sitio > Notificaciones`
   - Moodle detectará el nuevo plugin automáticamente

4. **Completa la instalación**
   - Sigue las instrucciones en pantalla
   - La tabla `personality_test` se creará automáticamente en la base de datos

5. **Verifica la instalación**
   - Ve a: `Administración del sitio > Plugins > Bloques`
   - Busca "Test MBTI 93" en la lista

---

## Uso del Bloque

### Para Estudiantes

1. **Acceso al Test**
   - El estudiante ingresa a un curso donde el bloque MBTI 93 está activado
   - Si no ha completado el test, será automáticamente redirigido a la página del cuestionario

2. **Completar el Test**
   - El test consta de 93 preguntas divididas en 4 secciones:
     - **Sección 1** (Preguntas 1-26): Preferencias de comportamiento
     - **Sección 2** (Preguntas 27-58): Selección de palabras representativas
     - **Sección 3** (Preguntas 59-75): Situaciones cotidianas
     - **Sección 4** (Preguntas 76-93): Rasgos y conceptos
   
3. **Navegación**
   - Barra lateral con progreso por sección
   - Botones de navegación "Anterior" y "Siguiente"
   - Barra de progreso general en la parte superior
   - El botón "Enviar test" se habilita cuando todas las preguntas están respondidas

4. **Resultados**
   - Una vez completado, el estudiante visualiza:
     - Su tipo MBTI (ej: INTJ, ESFP, etc.)
     - Gráfico radar con sus dimensiones de personalidad
     - Explicación detallada de su perfil psicológico
     - Fortalezas y áreas de desarrollo

### Para Profesores

1. **Vista del Bloque**
   - Los profesores ven estadísticas agregadas de todos los estudiantes del curso:
     - Distribución de tipos MBTI (gráfico de barras)
     - Análisis por dimensiones de personalidad
     - Gráficos comparativos de rasgos

2. **Exportación de Datos**
   - Botón "Descargar CSV": Exporta datos en formato tabla
   - Botón "Descargar PDF": Genera reporte visual completo

3. **Panel de Administración**
   - Acceso mediante el icono ⚙️ en el bloque
   - Funcionalidades:
     - Ver lista completa de participantes
     - Buscar por nombre o correo electrónico
     - Ver resultados individuales de cada estudiante
     - Eliminar participaciones (con confirmación)
     - Exportar reportes generales

---

## Estructura del Test

### Sección 1: Preguntas 1-26
Evalúa preferencias de comportamiento en situaciones cotidianas.

**Ejemplo:**
> *"Cuando vas a algún lugar por el día, prefieres..."*
> - a) Planear con anticipación qué hacer y cuándo
> - b) Simplemente ir y luego ver qué hacer

### Sección 2: Preguntas 27-58
Selección de palabras que mejor representan al estudiante.

**Ejemplo:**
> *"Elige la palabra que más te representa:"*
> - a) Cerrado
> - b) Abierto

### Sección 3: Preguntas 59-75
Situaciones específicas y preferencias de trabajo/social.

**Ejemplo:**
> *"Cuando comienzas un gran proyecto a desarrollar en una semana..."*
> - a) Haces un listado de cosas por hacer según orden de importancia
> - b) Te sumerges en el tema

### Sección 4: Preguntas 76-93
Palabras y conceptos adicionales para refinar el tipo MBTI.

---

## Estructura de Archivos

```
blocks/mbti_93/
├── block_mbti_93.php          # Clase principal del bloque
├── version.php                # Información de versión
├── lib.php                    # Funciones auxiliares
├── view.php                   # Formulario del test (estudiantes)
├── save.php                   # Procesamiento y guardado de respuestas
├── admin_view.php             # Panel de administración (profesores)
├── view_individual.php        # Vista de resultados individuales
├── download_csv.php           # Exportación a CSV
├── download_pdf.php           # Exportación a PDF
├── edit_form.php              # Formulario de edición (si aplica)
├── styles.css                 # Estilos personalizados
├── LICENSE                    # Licencia del proyecto
├── README.md                  # Este archivo
│
├── amd/                       # JavaScript modular (AMD)
│   ├── build/
│   │   └── charts.min.js      # Gráficos minificados
│   └── src/
│       ├── charts.js          # Código fuente de gráficos
│       └── navigation.js      # Navegación del test
│
├── classes/
│   └── mbti_93_config.php     # Configuración del test
│
├── db/
│   ├── access.php             # Capacidades y permisos
│   └── install.xml            # Esquema de la base de datos
│
├── lang/                      # Archivos de idioma
│   ├── en/
│   │   └── block_mbti_93.php  # Strings en inglés
│   └── es/
│       └── block_mbti_93.php  # Strings en español
│
└── pix/                       # Imágenes e iconos
```

---

## Base de Datos

El plugin crea la tabla `personality_test` con la siguiente estructura:

| Campo         | Tipo    | Descripción                                    |
|---------------|---------|------------------------------------------------|
| id            | int     | Identificador único (auto-incremental)         |
| user          | int     | ID del usuario (estudiante)                    |
| course        | int     | ID del curso                                   |
| extraversion  | int     | Puntuación en Extraversión                     |
| introversion  | int     | Puntuación en Introversión                     |
| sensing       | int     | Puntuación en Sensación                        |
| intuition     | int     | Puntuación en Intuición                        |
| thinking      | int     | Puntuación en Pensamiento                      |
| feeling       | int     | Puntuación en Sentimiento                      |
| judging       | int     | Puntuación en Juicio                           |
| perceptive    | int     | Puntuación en Percepción                       |
| time_done     | int     | Timestamp de finalización                      |

**Índices:**
- `user_idx`: Índice en campo `user`
- `course_idx`: Índice en campo `course`
- `usercourse_uq`: Índice único en combinación `user` + `course` (evita duplicados)

---

## Personalización

### Modificar Preguntas

Las preguntas se almacenan en los archivos de idioma:
- `lang/es/block_mbti_93.php` (español)
- `lang/en/block_mbti_93.php` (inglés)

Formato:
```php
$string['section1_q1'] = 'Texto de la pregunta';
$string['section1_q1_a'] = 'Opción A';
$string['section1_q1_b'] = 'Opción B';
```

### Cambiar Estilos

Edita el archivo `styles.css` para modificar:
- Colores del formulario
- Tamaño de fuentes
- Espaciado y márgenes
- Estilos de botones

### Añadir Gráficos

Los gráficos utilizan **Chart.js** y están en:
- `amd/src/charts.js` (código fuente)
- `amd/build/charts.min.js` (versión minificada)

---

## Funciones Principales

### Clase `block_mbti_93`

#### `get_content()`
Genera el contenido del bloque según el rol del usuario:
- **Estudiantes**: Redirige al test o muestra resultados
- **Profesores**: Muestra estadísticas del curso

#### `_get_teacher_content($DB, $COURSE)`
Procesa y prepara datos para la vista del profesor:
- Cuenta tipos MBTI
- Calcula distribución de rasgos
- Genera gráficos con Chart.js

### Archivo `save.php`

Procesa las respuestas del test:
1. Valida la sesión y datos POST
2. Calcula puntuaciones por dimensión
3. Determina el tipo MBTI resultante
4. Guarda en la base de datos
5. Redirige al curso

### Archivo `admin_view.php`

Panel de administración con:
- Lista de participantes
- Filtro de búsqueda
- Opciones de exportación
- Eliminar participaciones

---

## Reportes y Exportación

### Formato CSV

Incluye columnas:
- Nombre completo del estudiante
- Correo electrónico
- Tipo MBTI
- Puntuaciones individuales en cada dimensión
- Fecha de realización

### Formato PDF

Genera un documento con:
- Portada con información del curso
- Resumen estadístico
- Tablas de distribución MBTI
- Gráficos visuales
- Lista completa de participantes

---

## Permisos y Capacidades

Definidos en `db/access.php`:

```php
$capabilities = array(
    'block/mbti_93:addinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),
    'block/mbti_93:myaddinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        )
    )
);
```

---

## Soporte Multiidioma

El plugin incluye traducciones completas en:
- **Español** (`lang/es/block_mbti_93.php`)
- **Inglés** (`lang/en/block_mbti_93.php`)

Para añadir un nuevo idioma:
1. Crea una carpeta en `lang/` con el código del idioma (ej: `fr` para francés)
2. Copia `block_mbti_93.php` de otro idioma
3. Traduce todas las cadenas de texto

---

## Solución de Problemas

### El bloque no aparece en el curso

**Solución:**
- Verifica que el plugin esté instalado correctamente
- Activa el modo de edición en el curso
- Añade el bloque desde "Agregar un bloque"

### Los estudiantes no pueden acceder al test

**Solución:**
- Verifica que el estudiante tenga rol de "student" (roleid = 5)
- Revisa los permisos del curso
- Comprueba que la tabla `personality_test` existe

### Los gráficos no se muestran

**Solución:**
- Limpia la caché de Moodle: `Administración > Desarrollo > Purgar cachés`
- Verifica que los archivos AMD estén en `amd/build/`
- Comprueba la consola del navegador para errores JavaScript

### Error al guardar respuestas

**Solución:**
- Verifica que todas las preguntas tengan respuesta
- Comprueba que el token de sesión (`sesskey`) sea válido
- Revisa los logs de PHP para errores de base de datos

---

## Contribuciones

¡Las contribuciones son bienvenidas! Para contribuir:

1. Haz un fork del repositorio
2. Crea una rama para tu feature: `git checkout -b feature/nueva-funcionalidad`
3. Realiza tus cambios y haz commit: `git commit -m "Añade nueva funcionalidad"`
4. Sube los cambios: `git push origin feature/nueva-funcionalidad`
5. Abre un Pull Request

---
