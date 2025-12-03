<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_login();

$userid = required_param('userid', PARAM_INT);
$courseid = required_param('cid', PARAM_INT);

// Verificar permisos: solo administradores y profesores
$isadmin = is_siteadmin($USER);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($courseid);

$COURSE_ROLED_AS_TEACHER = $DB->get_record_sql("
    SELECT m.id
    FROM {user} m 
    LEFT JOIN {role_assignments} m2 ON m.id = m2.userid 
    LEFT JOIN {context} m3 ON m2.contextid = m3.id 
    LEFT JOIN {course} m4 ON m3.instanceid = m4.id 
    WHERE (m3.contextlevel = 50 AND m2.roleid IN (3, 4) AND m.id IN ({$USER->id})) 
    AND m4.id = {$courseid} 
");

if (!$isadmin && (!isset($COURSE_ROLED_AS_TEACHER->id) || !$COURSE_ROLED_AS_TEACHER->id)) {
    redirect(new moodle_url('/course/view.php', array('id' => $courseid)), 
             get_string('no_admin_access', 'block_mbti_93'));
}

// Obtener datos del usuario y test
$userfields = \core_user\fields::for_name()->with_userpic()->get_sql('', false, '', '', false)->selects;
$user = $DB->get_record('user', array('id' => $userid), $userfields, MUST_EXIST);
$test_result = $DB->get_record('personality_test', array('user' => $userid, 'course' => $courseid));

if (!$test_result) {
    redirect(new moodle_url('/blocks/mbti_93/admin_view.php', array('cid' => $courseid)), 
             get_string('no_test_results', 'block_mbti_93'));
}

// Calcular tipo MBTI
$mbti = '';
$mbti .= ($test_result->extraversion >= $test_result->introversion) ? 'E' : 'I';
$mbti .= ($test_result->sensing > $test_result->intuition) ? 'S' : 'N';
$mbti .= ($test_result->thinking >= $test_result->feeling) ? 'T' : 'F';
$mbti .= ($test_result->judging > $test_result->perceptive) ? 'J' : 'P';

// Configurar página
$PAGE->set_url(new moodle_url('/blocks/mbti_93/view_individual.php', array('userid' => $userid, 'cid' => $courseid)));
$PAGE->set_context($context);
$PAGE->set_title(get_string('individual_results', 'block_mbti_93') . ': ' . fullname($user));
$PAGE->set_heading(get_string('individual_results', 'block_mbti_93') . ': ' . fullname($user));

echo $OUTPUT->header();

// CSS personalizado
echo "<link rel='stylesheet' href='" . $CFG->wwwroot . "/blocks/mbti_93/styles.css'>";

// Breadcrumb navigation
echo '<nav aria-label="breadcrumb" class="mb-4">';
echo '<ol class="breadcrumb">';
echo '<li class="breadcrumb-item"><a href="' . new moodle_url('/course/view.php', array('id' => $courseid)) . '">' . $course->fullname . '</a></li>';
echo '<li class="breadcrumb-item"><a href="' . new moodle_url('/blocks/mbti_93/admin_view.php', array('cid' => $courseid)) . '">' . get_string('admin_manage_title', 'block_mbti_93') . '</a></li>';
echo '<li class="breadcrumb-item active" aria-current="page">' . get_string('individual_results', 'block_mbti_93') . '</li>';
echo '</ol>';
echo '</nav>';

echo "<div class='container-fluid'>";

// Header con información del estudiante
echo "<div class='row mb-4'>";
echo "<div class='col-12'>";
echo "<div class='card'>";
echo "<div class='card-header bg-primary text-white'>";
echo "<h3 class='card-title mb-0'>";
echo "<i class='fa fa-user'></i> " . get_string('individual_results', 'block_mbti_93');
echo "</h3>";
echo "</div>";
echo "<div class='card-body'>";
echo "<div class='row'>";
echo "<div class='col-md-8'>";
echo "<h4>" . fullname($user) . "</h4>";
echo "<p class='text-muted mb-1'><strong>" . get_string('email', 'block_mbti_93') . ":</strong> " . $user->email . "</p>";
echo "<p class='text-muted mb-1'><strong>" . get_string('test_date', 'block_mbti_93') . ":</strong> " . date('d/m/Y H:i', $test_result->time_done) . "</p>";
echo "<p class='text-muted mb-1'><strong>" . get_string('mbti_type', 'block_mbti_93') . ":</strong> <span class='badge bg-primary fs-6'>" . $mbti . "</span></p>";
echo "</div>";
echo "<div class='col-md-4 text-end'>";
echo "<a href='" . new moodle_url('/blocks/mbti_93/admin_view.php', array('cid' => $courseid)) . "' class='btn btn-secondary'>";
echo "<i class='fa fa-arrow-left'></i> " . get_string('back_to_admin', 'block_mbti_93');
echo "</a>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

// Resultados detallados
echo "<div class='row'>";

// Dimensiones principales
echo "<div class='col-lg-8 mb-4'>";
echo "<div class='card h-100'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'><i class='fa fa-chart-bar'></i> " . get_string('personality_dimensions', 'block_mbti_93') . "</h5>";
echo "</div>";
echo "<div class='card-body'>";

// Función para mostrar barras de progreso comparativas
function render_dimension_bar($label1, $value1, $label2, $value2, $max_value = 100) {
    $total = $value1 + $value2;
    $percent1 = $total > 0 ? ($value1 / $total) * 100 : 50;
    $percent2 = $total > 0 ? ($value2 / $total) * 100 : 50;
    
    echo "<div class='mb-4'>";
    echo "<div class='d-flex justify-content-between mb-2'>";
    echo "<span><strong>" . $label1 . "</strong> (" . $value1 . ")</span>";
    echo "<span><strong>" . $label2 . "</strong> (" . $value2 . ")</span>";
    echo "</div>";
    echo "<div class='progress' style='height: 25px;'>";
    echo "<div class='progress-bar bg-info' role='progressbar' style='width: " . $percent1 . "%' aria-valuenow='" . $percent1 . "' aria-valuemin='0' aria-valuemax='100'>";
    echo round($percent1, 1) . "%";
    echo "</div>";
    echo "<div class='progress-bar bg-warning' role='progressbar' style='width: " . $percent2 . "%' aria-valuenow='" . $percent2 . "' aria-valuemin='0' aria-valuemax='100'>";
    echo round($percent2, 1) . "%";
    echo "</div>";
    echo "</div>";
    echo "</div>";
}

render_dimension_bar(
    get_string('extraversion', 'block_mbti_93'), 
    $test_result->extraversion,
    get_string('introversion', 'block_mbti_93'), 
    $test_result->introversion
);

render_dimension_bar(
    get_string('sensing', 'block_mbti_93'), 
    $test_result->sensing,
    get_string('intuition', 'block_mbti_93'), 
    $test_result->intuition
);

render_dimension_bar(
    get_string('thinking', 'block_mbti_93'), 
    $test_result->thinking,
    get_string('feeling', 'block_mbti_93'), 
    $test_result->feeling
);

render_dimension_bar(
    get_string('judging', 'block_mbti_93'), 
    $test_result->judging,
    get_string('perceptive', 'block_mbti_93'), 
    $test_result->perceptive
);

echo "</div>";
echo "</div>";
echo "</div>";

// Resumen y acciones
echo "<div class='col-lg-4 mb-4'>";
echo "<div class='card h-100'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'><i class='fa fa-info-circle'></i> " . get_string('summary_actions', 'block_mbti_93') . "</h5>";
echo "</div>";
echo "<div class='card-body'>";

// Mostrar tipo MBTI con descripción
$mbti_explanations = [
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
];

echo "<div class='text-center mb-4'>";
echo "<h2 class='text-primary'>" . $mbti . "</h2>";
echo "<p class='text-muted'>" . ($mbti_explanations[$mbti] ?? '') . "</p>";
echo "</div>";

echo "<div class='d-grid gap-2'>";
echo "<a href='" . new moodle_url('/blocks/mbti_93/download_pdf.php', 
        array('userid' => $userid, 'cid' => $courseid)) . 
        "' class='btn btn-success'>";
echo "<i class='fa fa-download'></i> " . get_string('download_pdf', 'block_mbti_93');
echo "</a>";

echo "<a href='" . new moodle_url('/blocks/mbti_93/admin_view.php', 
        array('cid' => $courseid, 'action' => 'delete', 'userid' => $userid, 'sesskey' => sesskey())) . 
        "' class='btn btn-danger' onclick='return confirm(\"" . get_string('confirm_delete_individual', 'block_mbti_93') . "\")'>";
echo "<i class='fa fa-trash'></i> " . get_string('delete_results', 'block_mbti_93');
echo "</a>";
echo "</div>";

echo "</div>";
echo "</div>";
echo "</div>";

echo "</div>";

// Datos detallados en tabla
echo "<div class='row'>";
echo "<div class='col-12'>";
echo "<div class='card'>";
echo "<div class='card-header'>";
echo "<h5 class='mb-0'><i class='fa fa-table'></i> " . get_string('detailed_scores', 'block_mbti_93') . "</h5>";
echo "</div>";
echo "<div class='card-body'>";
echo "<div class='table-responsive'>";
echo "<table class='table table-striped'>";
echo "<thead>";
echo "<tr>";
echo "<th>" . get_string('dimension', 'block_mbti_93') . "</th>";
echo "<th>" . get_string('score', 'block_mbti_93') . "</th>";
echo "<th>" . get_string('preference', 'block_mbti_93') . "</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

$dimensions = [
    ['E/I', $test_result->extraversion, $test_result->introversion, 'Extraversión', 'Introversión'],
    ['S/N', $test_result->sensing, $test_result->intuition, 'Sensación', 'Intuición'],
    ['T/F', $test_result->thinking, $test_result->feeling, 'Pensamiento', 'Sentimiento'],
    ['J/P', $test_result->judging, $test_result->perceptive, 'Juicio', 'Percepción']
];

foreach ($dimensions as $dim) {
    $dominant = $dim[1] >= $dim[2] ? $dim[3] : $dim[4];
    $score = max($dim[1], $dim[2]);
    echo "<tr>";
    echo "<td><strong>" . $dim[0] . "</strong></td>";
    echo "<td>" . $dim[1] . " / " . $dim[2] . "</td>";
    echo "<td><span class='badge bg-secondary'>" . $dominant . "</span></td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "</div>";

echo $OUTPUT->footer();
?>