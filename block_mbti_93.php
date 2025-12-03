<?php
defined('MOODLE_INTERNAL') || die();

class block_mbti_93 extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_mbti_93');
    }

    public function applicable_formats() {
        return array('course' => true);
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function has_config() {
        return false;
    }

// FunciÃ³n auxiliar para mostrar un slider (puedes moverla fuera de la clase si lo prefieres)
public function my_slider($name, $min, $max, $value, $izq_val, $der_val) {
    $slider = '';
    $slider .= '<div class="slider-container" style="text-align:center">';
    $slider .= $izq_val  . " â†¹ " .  $der_val . "<br>";
    $slider .= '<input type="range" class="alpy" name="' . $name . '" min="' . $min . '" max="' . $max . '" value="' . $value . '" disabled>';
    $slider .= '</div>';
    return $slider;
}

// FunciÃ³n para la vista del profesor
protected function _get_teacher_content($DB, $COURSE) {
    global $OUTPUT;

    $content = new stdClass();
    $content->text = '';
    $content->footer = '';

    $students = $DB->get_records('personality_test', ['course' => $COURSE->id]);

    // DepuraciÃ³n: Mostrar nÃºmero de estudiantes encontrados
    // Estudiantes encontrados para el curso

    if(empty($students)) {
        $content->text = get_string('sin_datos_estudiantes', 'block_mbti_93');
        return $content;
    }

    $mbti_types = ["ISTJ", "ISFJ", "INFJ", "INTJ", "ISTP", "ISFP", "INFP", "INTP", "ESTP", "ESFP", "ENFP", "ENTP", "ESTJ", "ESFJ", "ENFJ", "ENTJ"];
    $mbti_count = array_fill_keys($mbti_types, 0);

    $aspect_counts = [
        "Introvertido" => 0, "Extrovertido" => 0,
        "Sensing" => 0, "IntuiciÃ³n" => 0,
        "Pensamiento" => 0, "Sentimiento" => 0,
        "Juicio" => 0, "PercepciÃ³n" => 0
    ];

    foreach ($students as $entry) {
        if (!isset($entry->extraversion, $entry->introversion, $entry->sensing, $entry->intuition, $entry->thinking, $entry->feeling, $entry->judging, $entry->perceptive)) {
            continue;
        }

        $mbti_score = "";
        $mbti_score .= ($entry->extraversion >= $entry->introversion) ? "E" : "I";
        $mbti_score .= ($entry->sensing > $entry->intuition) ? "S" : "N";
        $mbti_score .= ($entry->thinking >= $entry->feeling) ? "T" : "F";
        $mbti_score .= ($entry->judging > $entry->perceptive) ? "J" : "P";

        if (isset($mbti_count[$mbti_score])) {
            $mbti_count[$mbti_score]++;
        }

        $aspect_counts["Introvertido"] += ($entry->introversion > $entry->extraversion) ? 1 : 0;
        $aspect_counts["Extrovertido"] += ($entry->extraversion >= $entry->introversion) ? 1 : 0;
        $aspect_counts["Sensing"] += ($entry->sensing > $entry->intuition) ? 1 : 0;
        $aspect_counts["IntuiciÃ³n"] += ($entry->intuition >= $entry->sensing) ? 1 : 0;
        $aspect_counts["Pensamiento"] += ($entry->thinking > $entry->feeling) ? 1 : 0;
        $aspect_counts["Sentimiento"] += ($entry->feeling >= $entry->thinking) ? 1 : 0;
        $aspect_counts["Juicio"] += ($entry->judging > $entry->perceptive) ? 1 : 0;
        $aspect_counts["PercepciÃ³n"] += ($entry->perceptive >= $entry->judging) ? 1 : 0;
    }

    $mbti_json = json_encode(array_filter($mbti_count));
    $aspects_json = json_encode($aspect_counts);

    $str_titulo_distribucion_mbti = json_encode(get_string('titulo_distribucion_mbti', 'block_mbti_93'));
    $str_num_estudiantes = json_encode(get_string('num_estudiantes', 'block_mbti_93'));
    $str_introversion_extroversion = json_encode(get_string('introversion_extroversion', 'block_mbti_93'));
    $str_sensacion_intuicion = json_encode(get_string('sensacion_intuicion', 'block_mbti_93'));
    $str_pensamiento_sentimiento = json_encode(get_string('pensamiento_sentimiento', 'block_mbti_93'));
    $str_juicio_percepcion = json_encode(get_string('juicio_percepcion', 'block_mbti_93'));

    $content->text .= html_writer::tag('h6',get_string('titulo_resultados_estudiantes', 'block_mbti_93'),['style' => 'text-align: center;']);
    $content->text .= html_writer::tag('canvas', '', ['id' => 'mbtiChart', 'style' => 'max-width: 100%; height: auto;']);
    $content->text .= html_writer::tag('h6', get_string('titulo_distribucion_rasgos', 'block_mbti_93'), ['style' => 'text-align: center; margin-top: 20px;']);

    $content->text .= html_writer::start_div('d-flex flex-wrap justify-content-around');
    $chart_style = "width: 100%; max-width: 350px; margin: 10px; box-sizing: border-box;";
    $content->text .= html_writer::div(html_writer::tag('canvas', '', ['id' => 'generalTrendChart']), '', ['style' => $chart_style]);
    $content->text .= html_writer::div(html_writer::tag('canvas', '', ['id' => 'infoProcessingChart']), '', ['style' => $chart_style]);
    $content->text .= html_writer::div(html_writer::tag('canvas', '', ['id' => 'decisionMakingChart']), '', ['style' => $chart_style]);
    $content->text .= html_writer::div(html_writer::tag('canvas', '', ['id' => 'organizationChart']), '', ['style' => $chart_style]);
    $content->text .= html_writer::end_div();

    // Preparar datos para JavaScript
    $page = $this->page;
    $page->requires->js_call_amd('block_mbti_93/charts', 'init', array(
        json_decode($mbti_json),
        $aspect_counts,
        array(
            'titulo_distribucion_mbti' => get_string('titulo_distribucion_mbti', 'block_mbti_93'),
            'num_estudiantes_header' => get_string('num_estudiantes_header', 'block_mbti_93'),
            'introversion_extroversion' => get_string('introversion_extroversion', 'block_mbti_93'),
            'sensacion_intuicion' => get_string('sensacion_intuicion', 'block_mbti_93'),
            'pensamiento_sentimiento' => get_string('pensamiento_sentimiento', 'block_mbti_93'),
            'juicio_percepcion' => get_string('juicio_percepcion', 'block_mbti_93'),
            'Introvertido' => get_string('Introvertido', 'block_mbti_93'),
            'Extrovertido' => get_string('Extrovertido', 'block_mbti_93'),
            'Sensing' => get_string('Sensing', 'block_mbti_93'),
            'Intuicion' => get_string('Intuicion', 'block_mbti_93'),
            'Pensamiento' => get_string('Pensamiento', 'block_mbti_93'),
            'Sentimiento' => get_string('Sentimiento', 'block_mbti_93'),
            'Juicio' => get_string('Juicio', 'block_mbti_93'),
            'Percepcion' => get_string('Percepcion', 'block_mbti_93'),
            'sin_datos_estudiantes' => get_string('sin_datos_estudiantes', 'block_mbti_93')
        )
    ));

    $csv_url = new moodle_url('/blocks/mbti_93/download_csv.php', ['courseid' => $COURSE->id, 'sesskey' => sesskey()]);
    $pdf_url = new moodle_url('/blocks/mbti_93/download_pdf.php', ['courseid' => $COURSE->id, 'sesskey' => sesskey()]);

    $download_links = html_writer::start_div('text-center', ['style' => 'margin-top: 20px;']);
    $link_attributes = [
        'class' => 'btn btn-sm btn-success d-inline-block',
        'style' => 'margin: 5px;',
        'role' => 'button'
    ];
    $download_links .= html_writer::link($csv_url, get_string('CSV', 'block_mbti_93'), $link_attributes);
    $download_links .= html_writer::link($pdf_url, get_string('PDF', 'block_mbti_93'), $link_attributes);
    $download_links .= html_writer::end_div();

    $content->text .= $download_links;
    $content->footer = '';
    return $content;
}
    
    //  FunciÃ³n para la vista del Profesor
    


    function get_content()
    {
        // Declarar globales necesarios
        global $OUTPUT, $CFG, $DB, $USER, $COURSE, $SESSION;

        // --- Validaciones Iniciales ---
        // No mostrar en la pÃ¡gina principal del sitio
        if ($COURSE->id == SITEID) {
             $this->content = new stdClass; $this->content->text = ''; $this->content->footer = ''; // Necesario inicializar para evitar error
            return $this->content; // Devolver objeto vacÃ­o en lugar de nada
        }

        // Si el contenido ya se generÃ³, devolverlo
        if ($this->content !== NULL) {
            return $this->content;
        }

        // Inicializar objeto de contenido
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        // Si la instancia del bloque no estÃ¡ configurada (?)
        if (empty($this->instance)) {
            // PodrÃ­a mostrar un error o mensaje aquÃ­ si fuera necesario
            return $this->content;
        }

        // Si el usuario no estÃ¡ logueado
        if (!isloggedin()) {
            // No se devuelve nada explÃ­citamente, pero el contenido estarÃ¡ vacÃ­o.
            // Considera aÃ±adir un mensaje como: $this->content->text = get_string('logintoview');
             return $this->content; // Devolver objeto vacÃ­o
        }

        // --- LÃ³gica Principal ---

        // Comprobar si el usuario actual es un estudiante en este curso (usando rol ID 5)
        $sql = "SELECT m.id
                FROM {user} m
                LEFT JOIN {role_assignments} m2 ON m.id = m2.userid
                LEFT JOIN {context} m3 ON m2.contextid = m3.id
                LEFT JOIN {course} m4 ON m3.instanceid = m4.id
                WHERE m3.contextlevel = 50 AND m2.roleid = 5 AND m.id = ? AND m4.id = ?";
        $params = [$USER->id, $COURSE->id];
        $COURSE_ROLED_AS_STUDENT = $DB->get_record_sql($sql, $params);

        // Comprobar si la consulta devolviÃ³ un ID (es estudiante)
        if ($COURSE_ROLED_AS_STUDENT && $COURSE_ROLED_AS_STUDENT->id) {
            // --- LÃ“GICA PARA EL ESTUDIANTE ---

            // Verificar si el estudiante ya tiene una entrada en la tabla para este curso
            $entry = $DB->get_record('personality_test', array('user' => $USER->id, 'course' => $COURSE->id));

            if (!$entry) {
                // El estudiante NO ha realizado el test todavÃ­a - REDIRIGIR SIEMPRE
            		$redirect_url = new moodle_url('/blocks/mbti_93/view.php', array('cid' => $COURSE->id));
            		redirect($redirect_url);
            } else {
                // El estudiante YA HA realizado el test - Mostrar sus resultados

                 if (!isset($entry->extraversion, $entry->introversion, $entry->sensing, $entry->intuition, $entry->thinking, $entry->feeling, $entry->judging, $entry->perceptive)) {
                     $this->content->text = get_string('error_recuperando_resultados', 'block_mbti_93');
                 } else {
                    $scores = array(
                        "extraversion" => $entry->extraversion, "introversion" => $entry->introversion,
                        "sensing" => $entry->sensing, "intuition" => $entry->intuition,
                        "thinking" => $entry->thinking, "feeling" => $entry->feeling,
                        "judging" => $entry->judging, "perceptive" => $entry->perceptive
                    );

                    $mbti_explanations = array(
                        "ISTJ" => "La persona ISTJ se caracteriza por su sentido del deber, la responsabilidad y la disciplina. Tiende a actuar de forma lógica y ordenada, especialmente en situaciones que requieren estructura o planificación. En momentos de presión, mantiene la calma y busca soluciones prácticas, confiando en la experiencia y los hechos. Su fortaleza radica en su fiabilidad y compromiso, aunque puede mostrarse inflexible ante los cambios o las ideas poco convencionales. Necesita aprender a adaptarse con mayor soltura y a valorar también las perspectivas emocionales.",
                        "ISFJ" => "El ISFJ es una persona amable, detallista y dedicada a cuidar de los demás. En entornos donde hay tensión o incertidumbre, suele responder con empatía y serenidad, buscando mantener la armonía. Se guía por valores de responsabilidad y servicio, y rara vez pasa por alto las necesidades de quienes lo rodean. No obstante, tiende a sobrecargarse tratando de agradar o ayudar, olvidando sus propios límites. Le beneficia aprender a expresar sus propias necesidades y a aceptar que no puede controlarlo todo.",
                        "INFJ" => "Las personas INFJ actúan movidas por una fuerte intuición y un profundo sentido de propósito. En las relaciones, son empáticas y buscan conexiones auténticas; en momentos adversos, suelen retirarse a reflexionar y encontrar significado en la experiencia. Su fuerza está en su visión idealista y su capacidad para inspirar cambios positivos. Sin embargo, pueden ser perfeccionistas o frustrarse cuando otros no comparten su nivel de compromiso. Aprender a soltar el control y aceptar la imperfección fortalece su bienestar.",
                        "INTJ" => "El INTJ se distingue por su pensamiento estratégico, independencia y deseo de mejorar los sistemas que le rodean. En situaciones desafiantes, analiza con lógica, planifica y actúa con precisión. Es ambicioso y visionario, con gran confianza en sus ideas. Aun así, puede parecer distante o poco empático cuando prioriza la eficiencia sobre las emociones. Le conviene recordar que la sensibilidad también es una forma de inteligencia y que la colaboración enriquece sus proyectos.",
                        "ISTP" => "La persona ISTP responde con calma y rapidez ante los problemas prácticos. Le gusta experimentar, explorar y encontrar soluciones creativas en el momento. En situaciones adversas, confía en su capacidad para improvisar, más que en planificaciones rígidas. Su naturaleza independiente y lógica le permite actuar con precisión, aunque a veces puede mostrarse desapegada o evitar compromisos emocionales. Desarrollar la empatía y la comunicación le ayuda a fortalecer sus vínculos personales y laborales.",
                        "ISFP" => "El ISFP es sensible, espontáneo y amante de la libertad. Actúa de manera tranquila y auténtica, guiado por sus valores y emociones. En momentos difíciles, prefiere mantener la paz y buscar un equilibrio personal antes de reaccionar. Su fortaleza está en su calidez y creatividad, pero puede tener dificultades para enfrentar conflictos o tomar decisiones firmes. Aprender a expresar sus opiniones y a tolerar la incomodidad emocional le permite crecer sin perder su esencia amable.",
                        "INFP" => "La persona INFP es idealista, empática y guiada por una profunda necesidad de coherencia interna. En entornos armoniosos florece, mostrando su creatividad y compasión; pero cuando las cosas se vuelven tensas, puede replegarse y procesar en silencio lo que siente. Su mayor fortaleza es la autenticidad, aunque corre el riesgo de frustrarse ante la realidad cuando no se ajusta a sus ideales. Desarrollar tolerancia a la imperfección y una acción más práctica le ayuda a concretar sus sueños.",
                        "INTP" => "El INTP se caracteriza por su mente analítica y curiosa. Le encanta comprender los sistemas y teorías que explican el mundo. En situaciones de incertidumbre, recurre a la reflexión profunda antes de actuar. Su capacidad de análisis es una gran fortaleza, pero puede quedarse atrapado en la teoría, evitando la acción. Le beneficia equilibrar pensamiento y práctica, así como conectar más con las emociones propias y ajenas, sin verlas como una distracción del razonamiento.",
                        "ESTP" => "La persona ESTP es energética, audaz y amante de los desafíos. Actúa con rapidez, confianza y sentido práctico, especialmente bajo presión. En situaciones de crisis, suele mantener la cabeza fría y encontrar soluciones inmediatas. Su capacidad para adaptarse es una fortaleza natural, aunque puede ser impulsiva o impaciente con la rutina. Aprender a planificar y considerar las consecuencias a largo plazo potencia su eficacia y madurez personal.",
                        "ESFP" => "El ESFP es alegre, sociable y orientado al disfrute del presente. En ambientes adversos, busca mantener el ánimo alto y animar a los demás. Su entusiasmo contagioso lo convierte en un gran motivador, pero puede tener dificultad para enfrentar la monotonía o los compromisos a largo plazo. Desarrollar disciplina y tolerancia a la frustración le ayuda a mantener su brillo sin perder estabilidad.",
                        "ENFP" => "La persona ENFP combina pasión, imaginación y empatía. Se motiva por ideas y causas que le resultan significativas. En momentos difíciles, busca darle sentido positivo a la experiencia y ayudar a otros a hacer lo mismo. Su mayor fortaleza es su energía creativa y optimismo, aunque puede dispersarse o cambiar de dirección con frecuencia. Cultivar la constancia y la organización le permite concretar sus proyectos con éxito.",
                        "ENTP" => "El ENTP es ingenioso, curioso y amante del debate. Enfrenta los problemas como oportunidades para innovar y desafiar lo establecido. Disfruta explorando ideas, aunque no siempre las lleva a término. Su mente rápida es su principal fortaleza, pero su tendencia a la discusión o la falta de estructura puede generarle conflictos. Incorporar hábitos de seguimiento y empatía emocional mejora su efectividad y relaciones.",
                        "ESTJ" => "Las personas ESTJ son prácticas, decididas y enfocadas en los resultados. Ante la adversidad, toman el control y organizan el entorno con eficiencia. Les gusta que las cosas se hagan correctamente y conforme a las normas. Su liderazgo y sentido de responsabilidad los hacen confiables, pero pueden mostrarse autoritarios o poco tolerantes con las diferencias. Aprender a escuchar y flexibilizar su enfoque les permite ser líderes más integradores.",
                        "ESFJ" => "El ESFJ se define por su generosidad y preocupación por el bienestar de los demás. En situaciones difíciles, procura mantener la armonía y cuidar de quienes lo rodean. Es atento y cooperativo, con una fuerte orientación al deber. Sin embargo, tiende a depender de la aprobación ajena y a evitar los conflictos por temor a herir a otros. Trabajar en el asertividad y la autonomía emocional fortalece su equilibrio personal.",
                        "ENFJ" => "La persona ENFJ es carismática, solidaria y naturalmente orientada al liderazgo. Actúa con empatía y visión de conjunto, motivando a los demás incluso en los momentos más difíciles. Su habilidad para comprender y guiar es su mayor fortaleza, aunque puede sobrecargarse al intentar ayudar a todos. Aprender a establecer límites y priorizar su propio bienestar le permite sostener su energía y su vocación de servicio.",
                        "ENTJ" => "El ENTJ es decidido, estratégico y enfocado en alcanzar sus objetivos. En momentos de crisis, asume el liderazgo con determinación, buscando soluciones efectivas. Su capacidad de planificación y visión lo impulsa al éxito, pero a veces puede parecer dominante o impaciente con quienes no comparten su ritmo. Incorporar empatía y flexibilidad en su estilo de liderazgo lo convierte en una figura aún más poderosa e inspiradora."
                    );

                    $mbti_score = "";
                    $mbti_score .= ($scores["extraversion"] >= $scores["introversion"]) ? "E" : "I";
                    $mbti_score .= ($scores["sensing"] > $scores["intuition"]) ? "S" : "N";
                    $mbti_score .= ($scores["thinking"] >= $scores["feeling"]) ? "T" : "F";
                    $mbti_score .= ($scores["judging"] > $scores["perceptive"]) ? "J" : "P";

                    // Datos correspondientes a cada tipo MBTI, ordenados segÃºn las 8 dimensiones
                    
                    $mbti_data = [
                        'INTP' => [9,9,3,3,1,1,7,7],
                        'ESFJ' => [1,2,7,8,9,2,3,8],
                        'ISTJ' => [9,8,8,9,1,2,2,1],
                        'ISFJ' => [9,3,8,9,1,7,2,1],
                        'INFJ' => [9,5,2,9,1,5,8,1],
                        'INTJ' => [9,9,2,9,1,1,8,1],
                        'ISTP' => [9,8,7,2,1,2,3,8],
                        'ISFP' => [9,2,7,2,1,8,3,8],
                        'INFP' => [9,2,3,3,1,8,7,7],
                        'ESTP' => [1,8,8,2,9,2,8,8],
                        'ESFP' => [1,2,8,2,9,8,2,8],
                        'ENFP' => [1,2,3,3,9,8,7,7],
                        'ENTP' => [1,8,3,3,9,2,7,7],
                        'ESTJ' => [1,9,8,9,1,1,2,1],
                        'ENFJ' => [1,3,2,9,1,7,8,1],
                        'ENTJ' => [1,9,2,9,1,1,8,1],
                    ];


                    // Verificamos si el tipo MBTI del usuario tiene explicaciÃ³n definida
                    if (isset($mbti_explanations[$mbti_score])) {

                        // Creamos el texto explicativo que se mostrarÃ¡ antes del grÃ¡fico
                        $paragraph_content = "De acuerdo con el modelo de Myers-Briggs, todos tendemos a inclinarnos por cuatro facetas de personalidad predominantes.<br>";
                        $paragraph_content .= "En tu caso podemos concluir que eres un " .
                            html_writer::tag('strong', $mbti_score) . "." .html_writer::empty_tag('br') . html_writer::empty_tag('br') . html_writer::tag('strong', $mbti_explanations[$mbti_score]);

                        // Añadimos el párrafo generado al contenido del bloque
                        $this->content->text .= html_writer::tag('p', $paragraph_content);

                        // Definimos las etiquetas de las dimensiones para el gráfico
                        //$mbti_labels = ['Introvertido','Extrovertido','Sensación','Intuición','Pensamiento','Sentimiento','Juicio','Percepción'];
                        $mbti_labels = ['Introvertido','Sentimiento','Sensación','Percepción','Extrovertido','Pensamiento','Intuición','Juicio'];


                        // Obtenemos los valores de datos para el tipo MBTI actual
                        $user_data = $mbti_data[$mbti_score];

                        // Creamos un ID Ãºnico para el canvas del grÃ¡fico usando el ID del usuario
                        $chart_id = 'graficoRadar_' . $USER->id;

                        // Insertamos el <canvas> donde se dibujarÃ¡ el grÃ¡fico radar
                        $this->content->text .= html_writer::start_tag('canvas', [
                            'id' => $chart_id,
                            'width' => '400',
                            'height' => '400'
                        ]);
                        $this->content->text .= html_writer::end_tag('canvas');

                        // Agregamos el script JavaScript que dibuja el grÃ¡fico usando Chart.js
                        $this->content->text .= '
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                    // Obtenemos el contexto del canvas del grÃ¡fico
                    const ctx = document.getElementById("' . $chart_id . '").getContext("2d");

                    // Creamos un nuevo grÃ¡fico radar con los datos del usuario
                    const radarChart = new Chart(ctx, {
                        type: "radar",
                        data: {
                            labels: ' . json_encode($mbti_labels) . ', // Dimensiones del MBTI
                            datasets: [{
                                label: "Perfil MBTI: ' . $mbti_score . '",
                                data: ' . json_encode($user_data) . ', // Valores del tipo MBTI del usuario
                                backgroundColor: "rgba(54, 162, 235, 0.2)",
                                borderColor: "rgba(54, 162, 235, 1)",
                                pointBackgroundColor: "rgba(54, 162, 235, 1)",
                                pointBorderColor: "#fff",
                                pointHoverBackgroundColor: "#fff",
                                pointHoverBorderColor: "rgba(54, 162, 235, 1)"
                            }]
                        },
                        options: {
                            scales: {
                                r: {
                                    beginAtZero: true,
                                    min: 0,
                                    max: 10 // Escala mÃ¡xima para las dimensiones
                                }
                            }
                        }
                    });
                    </script>';
                    } else {
                        // Si no se encuentra el tipo MBTI del usuario, mostramos un mensaje de error
                        $this->content->text .= html_writer::tag('p', 'No se encontrÃ³ informaciÃ³n de perfil MBTI.');
                    }

                    // Retornamos el contenido del bloque
                    return $this->content;
                 } // Fin else (datos de entry existen)
            } // Fin else (entry existe)
        } else {
            // --- LÃ“GICA PARA NO ESTUDIANTES (PodrÃ­a ser Profesor u Otro Rol) ---

            $context = context_course::instance($COURSE->id);
            $is_teacher = has_capability('moodle/course:viewhiddensections', $context, $USER->id);

            if ($is_teacher) {
                // -- VISTA DEL PROFESOR --
                $teacher_content = $this->_get_teacher_content($DB, $COURSE);
                $this->content->text = $teacher_content->text;
                $this->content->footer = $teacher_content->footer;
                
                // Agregar enlace a la vista administrativa
                $admin_url = new moodle_url('/blocks/mbti_93/admin_view.php', array('cid' => $COURSE->id));
                $this->content->footer .= html_writer::div(
                    html_writer::link($admin_url, 
                        '<i class="fa fa-cog"></i> ' . get_string('admin_manage_title', 'block_mbti_93'),
                        array('class' => 'btn btn-primary btn-sm mt-2', 'target' => '_blank')
                    ),
                    'text-center'
                );
            } else {
                // -- OTROS ROLES (Ni estudiante detectado, ni profesor con capacidad) --
                // Mantener la lÃ³gica original para estos casos: mostrar mensajes de configuraciÃ³n.
                 if (isset($this->config->mbti_93_content) && !empty($this->config->mbti_93_content["text"])) {
                    $this->content->text = "<img src='" . $OUTPUT->pix_url('ok', 'block_mbti_93') . "'>" . get_string('mbti_93_actived', 'block_mbti_93');
                } else {
                    $this->content->text = "<img src='" . $OUTPUT->pix_url('warning', 'block_mbti_93') . "'>" . get_string('mbti_93_configempty', 'block_mbti_93');
                }
            } // Fin else ($is_teacher)
        } // Fin else ($COURSE_ROLED_AS_STUDENT)

        // Devolver el objeto de contenido construido
        return $this->content;
    } // Fin get_content
}
