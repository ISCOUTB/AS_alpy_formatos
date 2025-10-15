<?php

require_once(__DIR__ . '/../../config.php');

require_once(__DIR__ . '/lib.php');require_once(__DIR__ . '/../../config.php');require_once(__DIR__ . '/../../config.php');

require_once($CFG->dirroot . '/blocks/personality_test/classes/personality_test_config.php');

require_once(__DIR__ . '/lib.php');require_once(__DIR__ . '/lib.php');

require_login();

require_once($CFG->dirroot . '/blocks/personality_test/classes/personality_test_config.php');require_once($CFG->dirroot . '/blocks/personality_test/classes/personality_test_config.php');

// Validación básica de seguridad y método

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !confirm_sesskey()) {

    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => optional_param('cid', 0, PARAM_INT), 'error' => 1]));

}require_login();require_login();



global $DB, $USER, $CFG;



$courseid = optional_param('cid', 0, PARAM_INT);// Validación básica de seguridad y método// Validación básica de seguridad y método

if (!$courseid) {

    redirect($CFG->wwwroot);if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !confirm_sesskey()) {if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !confirm_sesskey()) {

}

    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => optional_param('cid', 0, PARAM_INT), 'error' => 1]));    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => optional_param('cid', 0, PARAM_INT), 'error' => 1]));

// Validación de la base de datos y curso

try {}}

    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

    $dbman = $DB->get_manager();

    if (!$dbman->table_exists('mdl_test_personalidades_93')) {

        redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));global $DB, $USER, $CFG;global $DB, $USER, $CFG;

    }

} catch (Exception $e) {

    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));

}$courseid = optional_param('cid', 0, PARAM_INT);$courseid = optional_param('cid', 0, PARAM_INT);



// Verificar si ya existe un registro para este usuario en este cursoif (!$courseid) {if (!$courseid) {

try {

    if ($DB->record_exists('mdl_test_personalidades_93', ['user' => $USER->id, 'course' => $courseid])) {    redirect($CFG->wwwroot);    redirect($CFG->wwwroot);

        redirect(new moodle_url('/course/view.php', ['id' => $courseid]));

    }}}

} catch (Exception $e) {

    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));

}

// Validación de la base de datos y curso// Validación de la base de datos y curso

// Inicializar acumuladores de puntajes

$scores = [try {try {

    'E' => 0, 'I' => 0,

    'S' => 0, 'N' => 0,    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

    'T' => 0, 'F' => 0,

    'J' => 0, 'P' => 0    $dbman = $DB->get_manager();    $dbman = $DB->get_manager();

];

    if (!$dbman->table_exists('mdl_test_personalidades_93')) {    if (!$dbman->table_exists('mdl_test_personalidades_93')) {

// Definir secciones para procesar

$sections = [        redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));        redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));

    ['start' => 1, 'end' => 26, 'prefix' => 'section1_q'],   // E/I

    ['start' => 27, 'end' => 58, 'prefix' => 'section2_q'],  // S/N    }    }

    ['start' => 59, 'end' => 75, 'prefix' => 'section3_q'],  // T/F

    ['start' => 76, 'end' => 93, 'prefix' => 'section4_q']   // J/P} catch (Exception $e) {} catch (Exception $e) {

];

    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));

// Procesar cada sección

foreach ($sections as $section) {}}

    for ($i = $section['start']; $i <= $section['end']; $i++) {

        $qname = $section['prefix'] . $i;

        $answer = optional_param($qname, null, PARAM_ALPHA);

// Verificar si ya existe un registro para este usuario en este curso// Verificar si ya existe un registro para este usuario en este curso

        // Validar respuesta

        if ($answer !== 'a' && $answer !== 'b') {try {try {

            redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));

        }    if ($DB->record_exists('mdl_test_personalidades_93', ['user' => $USER->id, 'course' => $courseid])) {    if ($DB->record_exists('mdl_test_personalidades_93', ['user' => $USER->id, 'course' => $courseid])) {



        // Procesar la respuesta usando la configuración        redirect(new moodle_url('/course/view.php', ['id' => $courseid]));        redirect(new moodle_url('/course/view.php', ['id' => $courseid]));

        $traits = PersonalityTestConfig::process_answer($i, $answer);

        foreach ($traits as $trait => $points) {    }    }

            $scores[$trait] += $points;

        }} catch (Exception $e) {} catch (Exception $e) {

    }

}    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));



// Calcular porcentajes finales}}

$percentages = PersonalityTestConfig::calculate_percentages($scores);



// Determinar tipo MBTI

$mbti_type = PersonalityTestConfig::determine_mbti_type($percentages);// Inicializar acumuladores de puntajes// Inicializar acumuladores de puntajes



// Preparar registro para guardar$scores = [$scores = [

try {

    $record = new stdClass();    'E' => 0, 'I' => 0,    'E' => 0, 'I' => 0,

    $record->user = $USER->id;

    $record->course = $courseid;    'S' => 0, 'N' => 0,    'S' => 0, 'N' => 0,

    $record->state = 1; // Estado completado

        'T' => 0, 'F' => 0,    'T' => 0, 'F' => 0,

    // Guardar porcentajes individuales

    $record->extraversion = $percentages['E'];    'J' => 0, 'P' => 0    'J' => 0, 'P' => 0

    $record->introversion = $percentages['I'];

    $record->sensing = $percentages['S'];];];

    $record->intuition = $percentages['N'];

    $record->thinking = $percentages['T'];

    $record->feeling = $percentages['F'];

    $record->judging = $percentages['J'];// Definir secciones para procesar// Definir secciones para procesar

    $record->perceptive = $percentages['P']; // Nota: en la BD es perceptive, no perceiving

$sections = [$sections = [

    // Campos adicionales requeridos por la tabla

    $record->extra_res = 0;    ['start' => 1, 'end' => 26, 'prefix' => 'section1_q'],   // E/I    ['start' => 1, 'end' => 26, 'prefix' => 'section1_q'],   // E/I

    $record->intra_res = 0;

    $record->sensi_res = 0;    ['start' => 27, 'end' => 58, 'prefix' => 'section2_q'],  // S/N    ['start' => 27, 'end' => 58, 'prefix' => 'section2_q'],  // S/N

    $record->intui_res = 0;

    $record->ratio_res = 0;    ['start' => 59, 'end' => 75, 'prefix' => 'section3_q'],  // T/F    ['start' => 59, 'end' => 75, 'prefix' => 'section3_q'],  // T/F

    $record->emoti_res = 0;

    $record->estru_res = 0;    ['start' => 76, 'end' => 93, 'prefix' => 'section4_q']   // J/P    ['start' => 76, 'end' => 93, 'prefix' => 'section4_q']   // J/P

    $record->perce_res = 0;

    $record->created_at = time();];];

    $record->updated_at = time();



    $DB->insert_record('mdl_test_personalidades_93', $record);

} catch (Exception $e) {// Procesar cada sección// Procesar cada sección

    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));

}foreach ($sections as $section) {foreach ($sections as $section) {



// Redireccionar al curso    for ($i = $section['start']; $i <= $section['end']; $i++) {    for ($i = $section['start']; $i <= $section['end']; $i++) {

redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
        $qname = $section['prefix'] . $i;        $qname = $section['prefix'] . $i;

        $answer = optional_param($qname, null, PARAM_ALPHA);        $answer = optional_param($qname, null, PARAM_ALPHA);



        // Validar respuesta        // Validar respuesta

        if ($answer !== 'a' && $answer !== 'b') {        if ($answer !== 'a' && $answer !== 'b') {

            redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));            redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));

        }        }



        // Procesar la respuesta usando la configuración        // Procesar la respuesta usando la configuración

        $traits = PersonalityTestConfig::process_answer($i, $answer);        $traits = PersonalityTestConfig::process_answer($i, $answer);

        foreach ($traits as $trait => $points) {        foreach ($traits as $trait => $points) {

            $scores[$trait] += $points;            $scores[$trait] += $points;

        }        }

    }    }

}}



// Calcular porcentajes finales// Calcular porcentajes finales

$percentages = PersonalityTestConfig::calculate_percentages($scores);$percentages = PersonalityTestConfig::calculate_percentages($scores);



// Determinar tipo MBTI// Determinar tipo MBTI

$mbti_type = PersonalityTestConfig::determine_mbti_type($percentages);$mbti_type = PersonalityTestConfig::determine_mbti_type($percentages);



// Guardar resultados// Guardar resultados

try {try {

    $record = new stdClass();    $record = new stdClass();

    $record->user = $USER->id;    $record->user = $USER->id;

    $record->course = $courseid;    $record->course = $courseid;

    $record->timemodified = time();    $record->timemodified = time();

    $record->type = $mbti_type;    $record->type = $mbti_type;

        

    // Guardar porcentajes individuales    // Guardar porcentajes individuales

    $record->extraversion = $percentages['E'];    $record->extraversion = $percentages['E'];

    $record->introversion = $percentages['I'];    $record->introversion = $percentages['I'];

    $record->sensing = $percentages['S'];    $record->sensing = $percentages['S'];

    $record->intuition = $percentages['N'];    $record->intuition = $percentages['N'];

    $record->thinking = $percentages['T'];    $record->thinking = $percentages['T'];

    $record->feeling = $percentages['F'];    $record->feeling = $percentages['F'];

    $record->judging = $percentages['J'];    $record->judging = $percentages['J'];

    $record->perceiving = $percentages['P'];    $record->perceiving = $percentages['P'];



    $DB->insert_record('mdl_test_personalidades_93', $record);    $DB->insert_record('mdl_test_personalidades_93', $record);

} catch (Exception $e) {} catch (Exception $e) {

    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));

}}



// Redireccionar al curso// Redireccionar al curso

redirect(new moodle_url('/course/view.php', ['id' => $courseid]));redirect(new moodle_url('/course/view.php', ['id' => $courseid]));

    redirect(new moodle_url('/blocks/personality_test/view.php', 

        ['cid' => optional_param('cid', 0, PARAM_INT), 'error' => 1]));$courseid = optional_param('cid', 0, PARAM_INT);

}

require_login();$personality_test_a = array();

$courseid = optional_param('cid', 0, PARAM_INT);



if (!$courseid) {

    redirect($CFG->wwwroot);// Ensure this is a POST and has a valid sesskey to avoid CSRF/moodle exceptions.$extra = [5,7,10,13,23,25,61,68,71];

}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !confirm_sesskey()) {$intra = [2,9,49,54,63,65,67,69,72];

// Defensive DB checks

try {    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => optional_param('cid', 0, PARAM_INT), 'error' => 1]));$sensi = [15,45,45,51,53,56,59,66,70];

    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

    $dbman = $DB->get_manager();}$intui = [37,39,41,44,47,52,57,62,64];

    if (!$dbman->table_exists('personality_test')) {

        redirect(new moodle_url('/blocks/personality_test/view.php', $ratio = [1,4,6,18,20,48,50,55,58];

            ['cid' => $courseid, 'error' => 1]));

    }global $DB, $USER, $CFG;$emoti = [3,8,11,14,27,31,33,35,40];

} catch (Exception $e) {

    redirect(new moodle_url('/blocks/personality_test/view.php', $estru = [19,21,24,26,29,34,36,42,46];

        ['cid' => $courseid, 'error' => 1]));

}$courseid = optional_param('cid', 0, PARAM_INT);$perce = [12,16,17,22,28,30,32,38,60];



// Check if user already took the test

try {

    if ($DB->record_exists('personality_test', ['user' => $USER->id, 'course' => $courseid])) {if (!$courseid) {$extra_res = 0;

        redirect(new moodle_url('/course/view.php', ['id' => $courseid]));

    }    redirect($CFG->wwwroot);$intra_res = 0;

} catch (Exception $e) {

    redirect(new moodle_url('/blocks/personality_test/view.php', }$sensi_res = 0;

        ['cid' => $courseid, 'error' => 1]));

}$intui_res = 0;



// Recolectar respuestas// Defensive DB checks: if DB not ready, send user back to the form with error flag.$ratio_res = 0;

$answers = array();

$sections = array(try {$emoti_res = 0;

    array('title' => 'Seccion 1', 'start' => 1, 'end' => 26, 'prefix' => 'section1_q'),

    array('title' => 'Seccion 2', 'start' => 27, 'end' => 58, 'prefix' => 'section2_q'),    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);$estru_res = 0;

    array('title' => 'Seccion 3', 'start' => 59, 'end' => 75, 'prefix' => 'section3_q'),

    array('title' => 'Seccion 4', 'start' => 76, 'end' => 93, 'prefix' => 'section4_q')    $dbman = $DB->get_manager();$perce_res = 0;

);

    if (!$dbman->table_exists('personality_test')) {

// Validar y recolectar todas las respuestas

foreach ($sections as $section) {        redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));for ($i=1;$i<=72;$i++){

    for ($i = $section['start']; $i <= $section['end']; $i++) {

        $qname = $section['prefix'] . $i;    }    $personality_test_a[$i] = optional_param("personality_test:q".$i, 0, PARAM_INT);

        $answer = optional_param($qname, null, PARAM_ALPHA);

        } catch (Exception $e) {}

        if ($answer !== 'a' && $answer !== 'b') {

            // Faltan respuestas: volver al formulario con indicador de error    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));

            redirect(new moodle_url('/blocks/personality_test/view.php', 

                ['cid' => $courseid, 'error' => 1]));}//var_dump($personality_test_a);

        }

        

        $answers[$i] = $answer;

    }// Si ya existe un registro para este usuario en este curso, redirige al cursoforeach($extra as $index => $value){

}

try {    $extra_res = $extra_res + $personality_test_a[$value];

// Calcular resultados usando el nuevo sistema de pesos

$results = PersonalityTestConfig::calculateTraits($answers);    if ($DB->record_exists('personality_test', ['user' => $USER->id, 'course' => $courseid])) {}



// Preparar datos para guardar        redirect(new moodle_url('/course/view.php', ['id' => $courseid]));foreach($intra as $index => $value){

$data = new stdClass();

$data->user = $USER->id;    }    $intra_res = $intra_res + $personality_test_a[$value];

$data->course = $courseid;

$data->state = "1";} catch (Exception $e) {}

$data->extraversion = round($results['E']);

$data->introversion = round($results['I']);    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));foreach($sensi as $index => $value){

$data->sensing = round($results['S']);

$data->intuition = round($results['N']);}    $sensi_res = $sensi_res + $personality_test_a[$value];

$data->thinking = round($results['T']);

$data->feeling = round($results['F']);}

$data->judging = round($results['J']);

$data->perceptive = round($results['P']);// Inicializar contadores MBTIforeach($intui as $index => $value){

$data->created_at = time();

$data->updated_at = time();$extraversion = 0; $introversion = 0;    $intui_res = $intui_res + $personality_test_a[$value];



// Guardar resultados$sensing = 0; $intuition = 0;}

try {

    $data->id = $DB->insert_record('personality_test', $data);$thinking = 0; $feeling = 0;foreach($ratio as $index => $value){

} catch (Exception $e) {

    redirect(new moodle_url('/blocks/personality_test/view.php', $judging = 0; $perceptive = 0;    $ratio_res = $ratio_res + $personality_test_a[$value];

        ['cid' => $courseid, 'error' => 1]));

}}



// Volver al curso// Definicion de secciones como en view.phpforeach($emoti as $index => $value){

redirect(new moodle_url('/course/view.php', ['id' => $courseid]));
$sections = array(    $emoti_res = $emoti_res + $personality_test_a[$value];

    array('title' => 'Seccion 1: Energia y Atencion', 'start' => 1, 'end' => 26, 'prefix' => 'section1_q'),}

    array('title' => 'Seccion 2: Informacion y Percepcion', 'start' => 27, 'end' => 58, 'prefix' => 'section2_q'),foreach($estru as $index => $value){

    array('title' => 'Seccion 3: Decisiones y Juicios', 'start' => 59, 'end' => 75, 'prefix' => 'section3_q'),    $estru_res = $estru_res + $personality_test_a[$value];

    array('title' => 'Seccion 4: Estructura y Organizacion', 'start' => 76, 'end' => 93, 'prefix' => 'section4_q')}

);foreach($perce as $index => $value){

    $perce_res = $perce_res + $personality_test_a[$value];

// Recolectar, validar y puntuar}

foreach ($sections as $section) {//echo "$extra_res -- $intra_res -- $sensi_res -- $intui_res -- $ratio_res -- $emoti_res -- $estru_res -- $perce_res";

    for ($i = $section['start']; $i <= $section['end']; $i++) {

        $qname = $section['prefix'] . $i;if ($courseid == SITEID && !$courseid) {

        $answer = optional_param($qname, null, PARAM_ALPHA);    redirect($CFG->wwwroot);

}

        if ($answer !== 'a' && $answer !== 'b') {

            // Faltan respuestas: volver al formulario con indicador de error

            redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));/*if( $accept != 1 ){

        }    //No accept, redirect

    $redirect = new moodle_url('/blocks/personality_test/view.php', array('cid'=>$courseid,'error'=>'1'));

        // Puntuacion segun seccion y paridad de la pregunta    redirect($redirect);

        if ($section['start'] == 1) { // Seccion 1: E vs I}

            if ($i % 2 == 1) { // impares favorecen E con A*/

                if ($answer === 'a') $extraversion++;$redirect = new moodle_url('/course/view.php', array('id'=>$courseid));

                if ($answer === 'b') $introversion++;

            } else {          // pares favorecen E con Bif(save_personality_test($courseid,$extra_res,$intra_res,$sensi_res,$intui_res,$ratio_res,$emoti_res,$estru_res,$perce_res)){

                if ($answer === 'a') $introversion++;    redirect($redirect, get_string('redirect_accept_success', 'block_personality_test') );

                if ($answer === 'b') $extraversion++;}else{

            }    redirect($redirect, get_string('redirect_accept_exist', 'block_personality_test') );

        } else if ($section['start'] == 27) { // Seccion 2: S vs N}

            if ($i % 2 == 1) {?>

                if ($answer === 'a') $sensing++;
                if ($answer === 'b') $intuition++;
            } else {
                if ($answer === 'a') $intuition++;
                if ($answer === 'b') $sensing++;
            }
        } else if ($section['start'] == 59) { // Seccion 3: T vs F
            if ($i % 2 == 1) {
                if ($answer === 'a') $thinking++;
                if ($answer === 'b') $feeling++;
            } else {
                if ($answer === 'a') $feeling++;
                if ($answer === 'b') $thinking++;
            }
        } else { // Seccion 4: J vs P
            if ($i % 2 == 1) {
                if ($answer === 'a') $judging++;
                if ($answer === 'b') $perceptive++;
            } else {
                if ($answer === 'a') $perceptive++;
                if ($answer === 'b') $judging++;
            }
        }
    }
}

// Guardar resultados y obtener registro guardado
try {
    $saved = save_personality_test($courseid, $extraversion, $introversion, $sensing, $intuition, $thinking, $feeling, $judging, $perceptive);
} catch (Exception $e) {
    // On error, redirect back to form
    redirect(new moodle_url('/blocks/personality_test/view.php', ['cid' => $courseid, 'error' => 1]));
}

// Volver al curso
redirect(new moodle_url('/course/view.php', ['id' => $courseid]));