define(['jquery', 'core/str', 'core/notification'], function($, Str, Notification) {
    return {
        init: function() {
            // Variables de estado
            var currentSection = 'section1'; // Empezar en la primera sección
            var sections = {};
            var totalQuestions = 0;
            var answeredQuestions = 0;

            // Ocultar todas las secciones excepto la activa al inicio
            console.log('Inicializando visibilidad de secciones...');
            $('.test-section').each(function() {
                var $section = $(this);
                console.log('Sección encontrada:', $section.attr('id'));
                console.log('Preguntas en la sección:', $section.find('.question-container').length);
            });
            
            $('.test-section').hide();
            var $activeSection = $('.test-section[data-active="true"]');
            console.log('Sección activa:', $activeSection.length ? $activeSection.attr('id') : 'ninguna');
            $activeSection.show();

            // Función para actualizar el progreso
            function updateProgress(sectionId) {
                var section = sections[sectionId];
                if (!section) return;

                // Actualizar progreso de la sección
                $('.section-navigation-item[data-section="' + sectionId + '"] .progress-indicator')
                    .text(section.answered + '/' + section.total);

                // Actualizar progreso general
                var totalProgress = Math.round((answeredQuestions / totalQuestions) * 100);
                $('.test-progress').css('width', totalProgress + '%')
                    .attr('aria-valuenow', totalProgress);

                // Actualizar estado del botón de envío
                if (answeredQuestions === totalQuestions) {
                    $('.submit-test').prop('disabled', false).removeClass('disabled');
                } else {
                    $('.submit-test').prop('disabled', true).addClass('disabled');
                }
            }

            // Función para mostrar una sección
            function showSection(sectionId) {
                if (!sectionId || currentSection === sectionId) return;

                var $newSection = $('#' + sectionId);
                if (!$newSection.length) return;

                // Ocultar sección actual y mostrar la nueva
                $('#' + currentSection).fadeOut(300, function() {
                    $newSection.fadeIn(300);
                });

                // Actualizar navegación
                $('.section-navigation-item').removeClass('active');
                $('.section-navigation-item[data-section="' + sectionId + '"]').addClass('active');

                // Actualizar estado
                currentSection = sectionId;
                updateProgress(sectionId);

                // Scroll suave
                $('html, body').animate({
                    scrollTop: $newSection.offset().top - 20
                }, 300);
            }

            // Inicializar datos de las secciones
            $('.test-section').each(function() {
                var sectionId = $(this).attr('id');
                sections[sectionId] = {
                    total: $(this).find('.question-container').length,
                    answered: 0
                };
                totalQuestions += sections[sectionId].total;
                sections[sectionId].answered = $(this).find('input[type="radio"]:checked').length;
                answeredQuestions += sections[sectionId].answered;
            });

            // Configurar manejadores de eventos
            
            // 1. Navegación en barra lateral
            $(document).on('click', '.section-navigation-item, .section-link', function(e) {
                e.preventDefault();
                var targetSection = $(this).closest('.section-navigation-item').data('section');
                if (targetSection) showSection(targetSection);
            });

            // 2. Botones siguiente/anterior
            $(document).on('click', '.next-section', function() {
                var targetSection = $(this).data('section');
                if (targetSection) showSection(targetSection);
            });

            $(document).on('click', '.prev-section', function() {
                var targetSection = $(this).data('section');
                if (targetSection) showSection(targetSection);
            });

            // 3. Manejo de respuestas
            $(document).on('change', 'input[type="radio"]', function() {
                var $question = $(this).closest('.question-container');
                var sectionId = $question.closest('.test-section').attr('id');
                
                if (!$question.hasClass('answered')) {
                    $question.addClass('answered');
                    sections[sectionId].answered++;
                    answeredQuestions++;
                }

                $(this).closest('.question-options').find('label').removeClass('selected');
                $(this).next('label').addClass('selected');
                
                updateProgress(sectionId);
                $question.removeClass('unanswered');
            });

            // 4. Validación del formulario
            $('#personality-test-form').on('submit', function(e) {
                var unanswered = $('.question-container').filter(function() {
                    return !$(this).find('input[type="radio"]:checked').length;
                });

                if (unanswered.length) {
                    e.preventDefault();
                    Str.get_string('please_answer_all', 'block_personality_test')
                        .then(function(message) {
                            Notification.alert('', message);
                            var firstUnansweredSection = $(unanswered[0]).closest('.test-section').attr('id');
                            showSection(firstUnansweredSection);
                            unanswered.addClass('unanswered');
                        })
                        .catch(Notification.exception);
                }
            });

            // Inicialización
            currentSection = $('.test-section').first().attr('id');
            $('.test-section').not('#' + currentSection).hide();
            $('.section-navigation-item[data-section="' + currentSection + '"]').addClass('active');
            updateProgress(currentSection);
        }
    };
});