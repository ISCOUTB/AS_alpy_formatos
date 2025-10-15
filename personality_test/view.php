<?php
// Vista del formulario del test de personalidad (MBTI, 93 preguntas)
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

require_login();
$courseid = required_param('cid', PARAM_INT);
$error = optional_param('error', 0, PARAM_INT);

// Moodle globals used below
global $DB, $USER, $CFG, $OUTPUT, $PAGE;

// Obtener el manejador de cadenas
$string_manager = get_string_manager();

if ($courseid == SITEID || !$courseid) {
    redirect($CFG->wwwroot);
}
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id);

// Definir las secciones del test
$sections = array(
    'section1' => array(
        'title' => get_string('section1_title', 'block_personality_test'),
        'start' => 1,
        'end' => 26
    ),
    'section2' => array(
        'title' => get_string('section2_title', 'block_personality_test'),
        'start' => 27,
        'end' => 58
    ),
    'section3' => array(
        'title' => get_string('section3_title', 'block_personality_test'),
        'start' => 59,
        'end' => 75
    ),
    'section4' => array(
        'title' => get_string('section4_title', 'block_personality_test'),
        'start' => 76,
        'end' => 93
    )
);

try {
    $PAGE->set_context($context);
    $PAGE->set_course($course);
    $PAGE->set_url(new moodle_url('/blocks/personality_test/view.php', array('cid' => $courseid)));
    $title = get_string('pluginname', 'block_personality_test');
    $PAGE->set_title($title . ' : ' . format_string($course->fullname));
    $PAGE->set_heading($title . ' : ' . format_string($course->fullname));

    // Agregar los estilos y JavaScript necesarios
    $PAGE->requires->css(new moodle_url('/blocks/personality_test/styles.css'));
    $PAGE->requires->js_call_amd('block_personality_test/navigation', 'init');

    echo $OUTPUT->header();

    // Contenedor principal con grid
    echo '<div class="personality-test-container">';

    // Barra lateral de navegación
    echo '<div class="section-navigation">';
    echo '<div class="section-navigation-header">'.get_string('test_sections', 'block_personality_test').'</div>';
    echo '<ul class="section-navigation-list">';
    $section_num = 1;
    foreach ($sections as $id => $section) {
        $num_questions = $section['end'] - $section['start'] + 1;
        echo '<li class="section-navigation-item" data-section="'.$id.'">';
        echo '<a href="#'.$id.'" class="section-link">Sección '.$section_num.'</a>';
        echo '<span class="progress-indicator">0/'.$num_questions.'</span>';
        echo '</li>';
        $section_num++;
    }
    echo '</ul>';
    echo '</div>';

    // Contenido principal
    echo '<div class="test-content">';
    echo '<form id="personality-test-form" method="post" action="'.new moodle_url('/blocks/personality_test/save.php').'">';
    echo '<input type="hidden" name="courseid" value="'.$courseid.'">';
    echo '<input type="hidden" name="sesskey" value="'.sesskey().'">';

    // Contenedor para todas las secciones
    echo '<div class="test-sections-container">';
    
    // Mostrar todas las secciones
    foreach ($sections as $id => $section) {
        $section_number = substr($id, -1); // Obtener el número de sección (1-4)
        
        echo '<div id="'.$id.'" class="test-section" '.($section_number == 1 ? 'data-active="true"' : '').'>';
        echo '<div class="section-header">';
        // Mostrar el título/instrucción de la sección
        $section_title_key = 'section'.$section_number.'_title';
        if ($string_manager->string_exists($section_title_key, 'block_personality_test')) {
            echo '<h3 class="section-title">'.format_string(get_string($section_title_key, 'block_personality_test')).'</h3>';
        }
        echo '</div>';
        echo '<div class="section-questions">';
        
        // Mostrar las preguntas de la sección
        for ($qnum = $section['start']; $qnum <= $section['end']; $qnum++) {
            // Obtener las cadenas usando el número de sección correcto
            $question_key = 'section'.$section_number.'_q'.$qnum;
            $option_a_key = 'section'.$section_number.'_q'.$qnum.'_a';
            $option_b_key = 'section'.$section_number.'_q'.$qnum.'_b';
            
            try {
                $question_text = get_string($question_key, 'block_personality_test');
                $option_a = get_string($option_a_key, 'block_personality_test');
                $option_b = get_string($option_b_key, 'block_personality_test');
                
                echo '<div class="question-container" data-question="'.$qnum.'">';
                echo '<p class="question-text">'.format_text($question_text).'</p>';
                echo '<div class="question-options">';
                echo '<label class="radio-option"><input type="radio" name="q'.$qnum.'" value="a"> '.format_text($option_a).'</label>';
                echo '<label class="radio-option"><input type="radio" name="q'.$qnum.'" value="b"> '.format_text($option_b).'</label>';
                echo '</div>';
                echo '</div>';
            } catch (Exception $e) {
                debugging('Error al obtener la pregunta ' . $qnum . ': ' . $e->getMessage());
            }
        }
        echo '</div>'; // Cierre de section-questions
        echo '</div>'; // Cierre de test-section

        // Botones de navegación
        echo '<div class="navigation-buttons">';
        if ($id !== array_key_first($sections)) {
            echo '<button type="button" class="btn btn-secondary prev-section" data-section="'.$id.'">'.get_string('previous_section', 'block_personality_test').'</button>';
        }
        if ($id !== array_key_last($sections)) {
            // Encontrar la siguiente sección
            $section_keys = array_keys($sections);
            $current_key = array_search($id, $section_keys);
            $next_section = $section_keys[$current_key + 1];
            echo '<button type="button" class="btn btn-primary next-section" data-section="'.$next_section.'">'.get_string('next_section', 'block_personality_test').'</button>';
        } else {
            echo '<button type="submit" class="btn btn-success submit-test" disabled>'.get_string('submit_test', 'block_personality_test').'</button>';
        }
        echo '</div>';
        
    }

    echo '</form>';
    echo '</div>'; // Fin test-content
    echo '</div>'; // Fin personality-test-container

    // Agregar script de depuración
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Habilitar debug
        const debugContainer = document.getElementById("personality-test-debug");
        debugContainer.style.display = "block";
        
        // Función para mostrar información de debug
        function showDebug(message) {
            const debugContent = document.getElementById("debug-content");
            debugContent.innerHTML += "<p>" + message + "</p>";
        }
        
        // Contar preguntas visibles
        const sections = document.querySelectorAll(".test-section");
        sections.forEach(section => {
            const questions = section.querySelectorAll(".question-container");
            showDebug(`Sección ${section.id}: ${questions.length} preguntas encontradas`);
            
            // Verificar visibilidad
            const display = window.getComputedStyle(section).display;
            showDebug(`Sección ${section.id} - display: ${display}`);
        });
    });
    </script>';
    
    echo $OUTPUT->footer();

} catch (Exception $e) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification($e->getMessage(), "error");
    echo $OUTPUT->footer();
}