<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/blocks/personality_test/classes/personality_test_config.php');

require_login();

// Validación básica de seguridad y método
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !confirm_sesskey()) {
    // usar courseid (nombre del input enviado por view.php) al redirigir
    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => optional_param('courseid', 0, PARAM_INT), 'error' => 1]));
}
// Preparar el registro para la base de datos
$courseid = optional_param('courseid', 0, PARAM_INT);

if (!$courseid) {
    redirect($CFG->wwwroot);
}

// Validación de la base de datos y curso
try {
    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
    $dbman = $DB->get_manager();
    
    if (!$dbman->table_exists('personality_test')) {
        // Optionally handle the case where the table does not exist
    }

    // Verificar si ya existe un registro para este usuario en este curso
    if ($DB->record_exists('personality_test', ['user' => $USER->id, 'course' => $courseid])) {
        redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
    }
} catch (Exception $e) {
    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));
}

// Inicializar acumuladores de puntajes
$scores = [
    'E' => 0, 'I' => 0,
    'S' => 0, 'N' => 0,
    'T' => 0, 'F' => 0,
    'J' => 0, 'P' => 0
];

// Detect input style: either the numeric array style used in the provided example
// (inputs like 'personality_test:q1' with integer values) or the radio style
// generated in view.php (inputs 'q1' with values 'a'/'b').
$postkeys = array_keys($_POST);
$uses_numeric_array = false;
// Algunas implementaciones envían un array anidado personality_test[q1] -> en PHP $_POST['personality_test'] = array(...)
if (isset($_POST['personality_test']) && is_array($_POST['personality_test'])) {
    $uses_numeric_array = true;
} else {
    foreach ($postkeys as $k) {
        // Aceptar variantes de nombre de campo:
        //  - personaity_test:q1   (alguna implementación)
        //  - personality_test[q1] (forma array en HTML)
        //  - personality_test[1]   (forma indexada)
        // Además asegúrese de que la clave sea una cadena para evitar warnings.
        if (is_string($k) && preg_match('/^personality_test(?::q\d+|\[q\d+\]|\[\d+\])$/', $k)) {
            $uses_numeric_array = true;
            break;
        }
    }
}

$processed = 0;
if ($uses_numeric_array) {
    // Seguir el ejemplo proporcionado: agrupar preguntas por dimensión y sumar valores enteros
    $personality_test_a = array();
    // Leer respuestas numéricas (si faltan se toman como 0). Soportar varias variantes de nombre de campo:
    //  - 'personality_test:q1' (clave plana)
    //  - $_POST['personality_test']['q1'] (array anidado con prefijo 'q')
    //  - $_POST['personality_test'][1] (array anidado indexado por número)
    for ($i = 1; $i <= 93; $i++) {
        $key = 'personality_test:q' . $i;
        if (isset($_POST[$key])) {
            // valor directo según clave plana
            $personality_test_a[$i] = (int)$_POST[$key];
            $processed++;
        } elseif (isset($_POST['personality_test']) && is_array($_POST['personality_test']) && isset($_POST['personality_test']['q' . $i])) {
            // forma nested: personality_test['q1']
            $personality_test_a[$i] = (int)$_POST['personality_test']['q' . $i];
            $processed++;
        } elseif (isset($_POST['personality_test']) && is_array($_POST['personality_test']) && isset($_POST['personality_test'][$i])) {
            // forma nested indexada: personality_test[1]
            $personality_test_a[$i] = (int)$_POST['personality_test'][$i];
            $processed++;
        } else {
            $personality_test_a[$i] = 0;
        }
    }

    // Definición de arreglos por rasgo (tal como en el ejemplo). Ajustar si es necesario.
    $extra = [5,7,10,13,23,25,61,68,71];
    $intra = [2,9,49,54,63,65,67,69,72];
    $sensi = [15,45,45,51,53,56,59,66,70];
    $intui = [37,39,41,44,47,52,57,62,64];
    $ratio = [1,4,6,18,20,48,50,55,58];
    $emoti = [3,8,11,14,27,31,33,35,40];
    $estru = [19,21,24,26,29,34,36,42,46];
    $perce = [12,16,17,22,28,30,32,38,60];

    // Inicializar acumuladores locales
    $extra_res = 0;
    $intra_res = 0;
    $sensi_res = 0;
    $intui_res = 0;
    $ratio_res = 0;
    $emoti_res = 0;
    $estru_res = 0;
    $perce_res = 0;

    foreach ($extra as $value) { $extra_res += (isset($personality_test_a[$value]) ? $personality_test_a[$value] : 0); }
    foreach ($intra as $value) { $intra_res += (isset($personality_test_a[$value]) ? $personality_test_a[$value] : 0); }
    foreach ($sensi as $value) { $sensi_res += (isset($personality_test_a[$value]) ? $personality_test_a[$value] : 0); }
    foreach ($intui as $value) { $intui_res += (isset($personality_test_a[$value]) ? $personality_test_a[$value] : 0); }
    foreach ($ratio as $value) { $ratio_res += (isset($personality_test_a[$value]) ? $personality_test_a[$value] : 0); }
    foreach ($emoti as $value) { $emoti_res += (isset($personality_test_a[$value]) ? $personality_test_a[$value] : 0); }
    foreach ($estru as $value) { $estru_res += (isset($personality_test_a[$value]) ? $personality_test_a[$value] : 0); }
    foreach ($perce as $value) { $perce_res += (isset($personality_test_a[$value]) ? $personality_test_a[$value] : 0); }

    // Mapear estos totales a los campos de $scores para mantener compatibilidad
    $scores['E'] = $extra_res;
    $scores['I'] = $intra_res;
    $scores['S'] = $sensi_res;
    $scores['N'] = $intui_res;
    $scores['T'] = $ratio_res;
    $scores['F'] = $emoti_res;
    $scores['J'] = $estru_res;
    $scores['P'] = $perce_res;

} else {
    // Procesar todas las preguntas q1..q93 (nombres generados por view.php)
    for ($i = 1; $i <= 93; $i++) {
        $qname = 'q' . $i;
        $answer = optional_param($qname, null, PARAM_ALPHA);

        // Si la pregunta no está presente en el formulario (no existe en esta instalación), la saltamos
        if ($answer === null) {
            continue;
        }

        // Validar respuesta
        if ($answer !== 'a' && $answer !== 'b') {
            redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));
        }

        // Procesar la respuesta usando la configuración (get_assigned_trait dentro)
        $traits = PersonalityTestConfig::process_answer($i, $answer);
        foreach ($traits as $trait => $points) {
            if (isset($scores[$trait])) {
                $scores[$trait] += $points;
            }
        }
        $processed++;
    }
}

// Si no procesamos ninguna pregunta, probablemente hubo un problema con el form
if ($processed === 0) {
    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));
}

// Calcular porcentajes finales
$percentages = PersonalityTestConfig::calculate_percentages($scores);

// Determinar tipo MBTI
    $mbti_type = PersonalityTestConfig::determine_mbti_type($percentages);

// Preparar el registro para la base de datos
$record = new stdClass();
$record->user = $USER->id;
$record->course = $courseid;
$record->mbti_type = $mbti_type;

// Guardar los porcentajes individuales
$record->extraversion = $percentages['E'];
$record->introversion = $percentages['I'];
$record->sensing = $percentages['S'];
$record->intuition = $percentages['N'];
$record->thinking = $percentages['T'];
$record->feeling = $percentages['F'];
$record->judging = $percentages['J'];
$record->perceptive = $percentages['P']; // Nota: en la BD es perceptive, no perceiving

// Campos adicionales requeridos por la tabla
// Si procesamos usando el arreglo numérico, las variables *_res estarán definidas.
$record->extra_res = isset($extra_res) ? $extra_res : 0;
$record->intra_res = isset($intra_res) ? $intra_res : 0;
$record->sensi_res = isset($sensi_res) ? $sensi_res : 0;
$record->intui_res = isset($intui_res) ? $intui_res : 0;
$record->ratio_res = isset($ratio_res) ? $ratio_res : 0;
$record->emoti_res = isset($emoti_res) ? $emoti_res : 0;
$record->estru_res = isset($estru_res) ? $estru_res : 0;
$record->perce_res = isset($perce_res) ? $perce_res : 0;

// Campo 'state' requerido por la tabla (valor por defecto razonable: 0)
$record->state = 0;

// Timestamps
$record->created_at = time();
$record->updated_at = time();

try {
    // Registrar en logs el record que vamos a insertar para diagnóstico
    error_log('Inserting personality test record: ' . print_r($record, true));
    // Guardar el registro en la base de datos usando la tabla estándar del bloque
    $DB->insert_record('personality_test', $record);

    // Redireccionar a la página del curso después de guardar exitosamente
    redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
} catch (Exception $e) {
    // Si hay un error al guardar, escribir en log y redireccionar a la página del test con mensaje de error
    error_log('Error inserting personality test record: ' . $e->getMessage());
    error_log('Record attempted: ' . print_r($record, true));
    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));
}