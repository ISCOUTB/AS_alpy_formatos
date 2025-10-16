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
    echo '<script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            // Ocultar todas las secciones excepto la primera
            document.querySelectorAll(".test-section").forEach(function(section) {
                section.style.display = "none";
            });
            document.getElementById("section1").style.display = "block";
            
            // Ocultar botones inicialmente
            document.querySelectorAll(".prev-section").forEach(function(btn) {
                btn.style.display = "none";
            });
            document.querySelectorAll(".submit-test").forEach(function(btn) {
                btn.style.display = "none";
            });
            
            // Función para actualizar el progreso
            function updateProgress(section) {
                var total = section.querySelectorAll(".question-container").length;
                var answered = section.querySelectorAll("input[type=radio]:checked").length;
                var sectionId = section.getAttribute("id");
                var indicator = document.querySelector(".section-navigation-item[data-section=\'" + sectionId + "\'] .progress-indicator");
                if (indicator) {
                    indicator.textContent = answered + "/" + total;
                }
            }
            
            // Manejar navegación entre secciones
            document.querySelectorAll(".next-section").forEach(function(btn) {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    var currentSection = document.querySelector(".test-section[style*=\'display: block\']");
                    var nextSection = currentSection.nextElementSibling;
                    while (nextSection && !nextSection.classList.contains("test-section")) {
                        nextSection = nextSection.nextElementSibling;
                    }
                    
                    if (nextSection) {
                        currentSection.style.display = "none";
                        nextSection.style.display = "block";
                        document.querySelectorAll(".prev-section").forEach(function(prevBtn) {
                            prevBtn.style.display = "block";
                        });
                        
                        // Si es la última sección, ocultar botón siguiente
                        var hasNextSection = false;
                        var temp = nextSection.nextElementSibling;
                        while (temp) {
                            if (temp.classList.contains("test-section")) {
                                hasNextSection = true;
                                break;
                            }
                            temp = temp.nextElementSibling;
                        }
                        if (!hasNextSection) {
                            btn.style.display = "none";
                            document.querySelectorAll(".submit-test").forEach(function(submitBtn) {
                                submitBtn.style.display = "block";
                            });
                        }
                    }
                });
            });
            
            document.querySelectorAll(".prev-section").forEach(function(btn) {
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    var currentSection = document.querySelector(".test-section[style*=\'display: block\']");
                    var prevSection = currentSection.previousElementSibling;
                    while (prevSection && !prevSection.classList.contains("test-section")) {
                        prevSection = prevSection.previousElementSibling;
                    }
                    
                    if (prevSection) {
                        currentSection.style.display = "none";
                        prevSection.style.display = "block";
                        document.querySelectorAll(".next-section").forEach(function(nextBtn) {
                            nextBtn.style.display = "block";
                        });
                        document.querySelectorAll(".submit-test").forEach(function(submitBtn) {
                            submitBtn.style.display = "none";
                        });
                        
                        // Si es la primera sección, ocultar botón anterior
                        var hasPrevSection = false;
                        var temp = prevSection.previousElementSibling;
                        while (temp) {
                            if (temp.classList.contains("test-section")) {
                                hasPrevSection = true;
                                break;
                            }
                            temp = temp.previousElementSibling;
                        }
                        if (!hasPrevSection) {
                            btn.style.display = "none";
                        }
                    }
                });
            });
            
            // Monitorear cambios en las respuestas
            document.querySelectorAll("input[type=radio]").forEach(function(input) {
                input.addEventListener("change", function() {
                    var section = this.closest(".test-section");
                    updateProgress(section);
                    
                    // Verificar si todas las preguntas están respondidas
                    var allAnswered = true;
                    document.querySelectorAll(".test-section").forEach(function(section) {
                        var total = section.querySelectorAll(".question-container").length;
                        var answered = section.querySelectorAll("input[type=radio]:checked").length;
                        if (answered < total) {
                            allAnswered = false;
                        }
                    });
                    
                    // Habilitar/deshabilitar botón de enviar
                    document.querySelectorAll(".submit-test").forEach(function(btn) {
                        btn.disabled = !allAnswered;
                    });
                });
            });
        });
    </script>';

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
    $total_sections = count($sections);
    foreach ($sections as $id => $section) {
        $section_number = substr($id, -1); // Obtener el número de sección (1-4)
        $is_first_section = $section_number == 1;
        
        echo '<div id="'.$id.'" class="test-section" '.($is_first_section ? 'style="display:block;"' : 'style="display:none;"').'>';
        
        // Botones de navegación superiores
        echo '<div class="navigation-buttons top-buttons">';
        if ($section_number == 1) {
            // Primera sección: solo botón siguiente
            echo '<button type="button" class="btn btn-primary next-section" data-target="section2">'.
                get_string('next_section', 'block_personality_test').'</button>';
        } else if ($section_number == $total_sections) {
            // Última sección: solo botón anterior
            echo '<button type="button" class="btn btn-secondary prev-section" data-target="section'.($section_number-1).'">'.
                get_string('previous_section', 'block_personality_test').'</button>';
        } else {
            // Secciones intermedias: botón anterior y siguiente
            echo '<button type="button" class="btn btn-secondary prev-section" data-target="section'.($section_number-1).'">'.
                get_string('previous_section', 'block_personality_test').'</button>';
            echo '<button type="button" class="btn btn-primary next-section" data-target="section'.($section_number+1).'">'.
                get_string('next_section', 'block_personality_test').'</button>';
        }
        echo '</div>';
        
        echo '<div class="section-header">';
        // Mostrar el título/instrucción de la sección
        $section_title_key = 'section'.$section_number.'_title';
        if ($string_manager->string_exists($section_title_key, 'block_personality_test')) {
            echo '<h3 class="section-title">'.format_string(get_string($section_title_key, 'block_personality_test')).'</h3>';
        }
        echo '</div>';
        echo '<div class="section-questions">';        // Mostrar las preguntas de la sección
        for ($qnum = $section['start']; $qnum <= $section['end']; $qnum++) {
            try {
                $question_text = '';
                $option_a = '';
                $option_b = '';

                // Las secciones 2 y 4 usan la instrucción genérica
                if ($section_number == 2 || $section_number == 4) {
                    $question_text = get_string('section'.$section_number.'_instruction', 'block_personality_test');
                }

                // Obtener el texto de la pregunta y opciones según la sección
                $section_key = 'section'.$section_number.'_q'.$qnum;
                
                // Si existe la pregunta específica para esta sección, la usamos
                if ($string_manager->string_exists($section_key, 'block_personality_test')) {
                    $question_text = get_string($section_key, 'block_personality_test');
                }
                
                // Obtener las opciones
                $option_a_key = 'section'.$section_number.'_q'.$qnum.'_a';
                $option_b_key = 'section'.$section_number.'_q'.$qnum.'_b';
                
                if ($string_manager->string_exists($option_a_key, 'block_personality_test') && 
                    $string_manager->string_exists($option_b_key, 'block_personality_test')) {
                    $option_a = get_string($option_a_key, 'block_personality_test');
                    $option_b = get_string($option_b_key, 'block_personality_test');
                    
                    echo '<div class="question-container" data-question="'.$qnum.'">';
                    if (!empty($question_text)) {
                        echo '<p class="question-text">'.format_text($question_text).'</p>';
                    }
                    echo '<div class="question-options">';
                    echo '<label class="radio-option"><input type="radio" name="q'.$qnum.'" value="a" required> '.format_text($option_a).'</label>';
                    echo '<label class="radio-option"><input type="radio" name="q'.$qnum.'" value="b" required> '.format_text($option_b).'</label>';
                    echo '</div>';
                    echo '</div>';
                }
            } catch (Exception $e) {
                debugging('Error al obtener la pregunta ' . $qnum . ': ' . $e->getMessage());
            }
        }
        echo '</div>'; // Cierre de section-questions

        // Botones de navegación inferiores
        echo '<div class="navigation-buttons bottom-buttons">';
        if ($section_number == 1) {
            // Primera sección: solo botón siguiente
            echo '<button type="button" class="btn btn-primary next-section" data-target="section2">'.
                get_string('next_section', 'block_personality_test').'</button>';
        } else if ($section_number == $total_sections) {
            // Última sección: botón anterior y enviar
            echo '<button type="button" class="btn btn-secondary prev-section" data-target="section'.($section_number-1).'">'.
                get_string('previous_section', 'block_personality_test').'</button>';
            echo '<button type="submit" class="btn btn-success submit-test" disabled>'.
                get_string('submit_test', 'block_personality_test').'</button>';
        } else {
            // Secciones intermedias: botón anterior y siguiente
            echo '<button type="button" class="btn btn-secondary prev-section" data-target="section'.($section_number-1).'">'.
                get_string('previous_section', 'block_personality_test').'</button>';
            echo '<button type="button" class="btn btn-primary next-section" data-target="section'.($section_number+1).'">'.
                get_string('next_section', 'block_personality_test').'</button>';
        }
        echo '</div>';
        
        echo '</div>'; // Cierre de test-section
    }

    echo '</div>'; // Cierre de test-sections-container
    echo '</form>';
    echo '</div>'; // Fin test-content
    echo '</div>'; // Fin personality-test-container

    // Script para la navegación
    echo '<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Función para cambiar de sección
        function switchToSection(fromSectionId, toSectionId) {
            // Ocultar sección actual
            var fromSection = document.getElementById(fromSectionId);
            if (fromSection) {
                fromSection.style.display = "none";
            }
            
            // Mostrar nueva sección
            var toSection = document.getElementById(toSectionId);
            if (toSection) {
                toSection.style.display = "block";
                
                // Actualizar estado activo en la navegación lateral
                document.querySelectorAll(".section-navigation-item").forEach(function(item) {
                    item.classList.remove("active");
                });
                var navItem = document.querySelector(`.section-navigation-item[data-section="${toSectionId}"]`);
                if (navItem) {
                    navItem.classList.add("active");
                }
                
                // Actualizar estado del botón Submit
                var submitBtn = document.querySelector(".submit-test");
                if (submitBtn) {
                    // Verificar si todas las preguntas están respondidas
                    var allSections = document.querySelectorAll(".test-section");
                    var allAnswered = true;
                    allSections.forEach(function(section) {
                        var total = section.querySelectorAll(".question-container").length;
                        var answered = section.querySelectorAll("input[type=radio]:checked").length;
                        if (answered < total) {
                            allAnswered = false;
                        }
                    });
                    submitBtn.disabled = !allAnswered;
                }
                
                // Scroll suave al inicio de la sección
                toSection.scrollIntoView({ behavior: "smooth", block: "start" });
                
                // Actualizar URL hash sin scrollear
                var currentURL = window.location.href.split("#")[0];
                window.history.pushState({}, "", currentURL + "#" + toSectionId);
            }
        }

        // Manejar navegación en los botones
        document.querySelectorAll(".next-section, .prev-section").forEach(function(button) {
            button.addEventListener("click", function(e) {
                e.preventDefault();
                var currentSection = document.querySelector(".test-section[style*=\'display: block\']");
                var targetSection = this.getAttribute("data-target");
                if (currentSection && targetSection) {
                    switchToSection(currentSection.id, targetSection);
                }
            });
        });

        // Manejar navegación en la barra lateral
        document.querySelectorAll(".section-link").forEach(function(link) {
            link.addEventListener("click", function(e) {
                e.preventDefault();
                var currentSection = document.querySelector(".test-section[style*=\'display: block\']");
                var targetId = this.getAttribute("href").substring(1);
                if (currentSection && targetId) {
                    switchToSection(currentSection.id, targetId);
                }
            });
        });

        // Manejar cambios en las respuestas
        document.querySelectorAll("input[type=radio]").forEach(function(radio) {
            radio.addEventListener("change", function() {
                var section = this.closest(".test-section");
                if (section) {
                    var sectionId = section.id;
                    var total = section.querySelectorAll(".question-container").length;
                    var answered = section.querySelectorAll("input[type=radio]:checked").length;
                    
                    // Actualizar indicador de progreso
                    var progressIndicator = document.querySelector(
                        `.section-navigation-item[data-section="${sectionId}"] .progress-indicator`
                    );
                    if (progressIndicator) {
                        progressIndicator.textContent = `${answered}/${total}`;
                    }

                    // Verificar si todas las preguntas están respondidas
                    var allSections = document.querySelectorAll(".test-section");
                    var allAnswered = true;
                    allSections.forEach(function(section) {
                        var sectionTotal = section.querySelectorAll(".question-container").length;
                        var sectionAnswered = section.querySelectorAll("input[type=\'radio\']:checked").length;
                        if (sectionAnswered < sectionTotal) {
                            allAnswered = false;
                        }
                    });

                    // Habilitar/deshabilitar botón de envío
                    var submitButton = document.querySelector(".submit-test");
                    if (submitButton) {
                        submitButton.disabled = !allAnswered;
                    }
                }
            });
        });
    });
    </script>';

        // Aquí terminan los scripts de navegación
        
        echo $OUTPUT->footer();
    } catch (Exception $e) {
        echo $OUTPUT->header();
        echo $OUTPUT->notification($e->getMessage(), "error");
        echo $OUTPUT->footer();
    }