<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Theme Boost eadifrn - Library
 *
 * @package    theme_boost_eadifrn
 * @copyright  2017 Kathrin Osswald, Ulm University <kathrin.osswald@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_ledor_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'plain.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/plain.scss');
    } else if ($filename == 'eadifrn.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost_eadifrn/scss/preset/eadifrn.scss');

    } else if ($filename == 'eadifrn_presencial.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost_eadifrn/scss/preset/eadifrn_presencial.scss');

    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_boost_eadifrn', 'preset', 0, '/', $filename))) {
        // This preset file was fetched from the file area for theme_boost_eadifrn and not theme_boost (see the line above).
        $scss .= $presetfile->get_content();
    } else {
        // Safety fallback - maybe new installs etc.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    }

    // Pre CSS - this is loaded AFTER any prescss from the setting but before the main scss.
    // $pre = file_get_contents($CFG->dirroot . '/theme/boost_eadifrn/scss/pre.scss');
    // Post CSS - this is loaded AFTER the main scss but before the extra scss from the setting.
    // $post = file_get_contents($CFG->dirroot . '/theme/boost_eadifrn/scss/post.scss');

    // Combine them together.
    return $scss;
}

/**
 * Override to add CSS values from settings to pre scss file.
 *
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return array
 */
function theme_boost_eadifrn_get_pre_scss($theme) {
    global $CFG;
    // MODIFICATION START.
    require_once($CFG->dirroot . '/theme/boost_eadifrn/locallib.php');
    // MODIFICATION END.

    $scss = '';
    $configurable = [
        // Config key => [variableName, ...].
        'brandcolor' => ['brand-primary'],
        // MODIFICATION START: Add own variables.
        'section0title' => ['section0title'],
        'showswitchedroleincourse' => ['showswitchedroleincourse'],
        'loginform' => ['loginform'],
        'footerhidehelplink' => ['footerhidehelplink'],
        'footerhidelogininfo' => ['footerhidelogininfo'],
        'footerhidehomelink' => ['footerhidehomelink'],
        'blockicon' => ['blockicon'],
        'brandsuccesscolor' => ['brand-success'],
        'brandinfocolor' => ['brand-info'],
        'brandwarningcolor' => ['brand-warning'],
        'branddangercolor' => ['brand-danger'],
        'darknavbar' => ['darknavbar'],
        'footerblocks' => ['footerblocks'],
        'imageareaitemsmaxheight' => ['imageareaitemsmaxheight'],
        'showsettingsincourse' => ['showsettingsincourse'],
        'incoursesettingsswitchtorole' => ['incoursesettingsswitchtorole']
        // MODIFICATION END.
    ];

    // Prepend variables first.
    foreach ($configurable as $configkey => $targets) {
        $value = isset($theme->settings->{$configkey}) ? $theme->settings->{$configkey} : null;
        if (empty($value)) {
            continue;
        }
        array_map(function($target) use (&$scss, $value) {
            $scss .= '$' . $target . ': ' . $value . ";\n";
        }, (array) $targets);
    }

    // MODIFICATION START: Add login background images that are uploaded to the setting 'loginbackgroundimage' to CSS.
    // $scss .= theme_boost_eadifrn_get_loginbackgroundimage_scss();
    // MODIFICATION END.

    // Prepend pre-scss.
    if (!empty($theme->settings->scsspre)) {
        $scss .= $theme->settings->scsspre;
    }

    return $scss;
}

/**
 * Implement pluginfile function to deliver files which are uploaded in theme settings
 *
 * @param stdClass $course course object
 * @param stdClass $cm course module
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 */
function theme_boost_eadifrn_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        $theme = theme_config::load('boost_eadifrn');
        if ($filearea === 'favicon') {
            return $theme->setting_file_serve('favicon', $args, $forcedownload, $options);
        } else if ($filearea === 'loginbackgroundimage') {
            return $theme->setting_file_serve('loginbackgroundimage', $args, $forcedownload, $options);
        } else if ($filearea === 'fontfiles') {
            return $theme->setting_file_serve('fontfiles', $args, $forcedownload, $options);
        } else if ($filearea === 'imageareaitems') {
            return $theme->setting_file_serve('imageareaitems', $args, $forcedownload, $options);
        } else {
            send_file_not_found();
        }
    } else {
        send_file_not_found();
    }
}

/**
 * If setting is updated, use this callback to clear the theme_boost_eadifrn' own application cache.
 */
function theme_boost_eadifrn_reset_app_cache() {
    // Get the cache from area.
    $themeboosteadifrncache = cache::make('theme_boost_eadifrn', 'imagearea');
    // Delete the cache for the imagearea.
    $themeboosteadifrncache->delete('imageareadata');
    // To be safe and because there can only be one callback function added to a plugin setting,
    // we also delete the complete theme cache here.
    theme_reset_all_caches();
}


function get_ledor_commom_moodle_template_context()
{
    global $OUTPUT, $PAGE, $COURSE, $SITE;

    if (isloggedin()) {
        $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
    } else {
        $navdraweropen = false;
    }
    $extraclasses = [];
    if ($navdraweropen) {
        $extraclasses[] = 'drawer-open-left';
    }
    $bodyattributes = $OUTPUT->body_attributes($extraclasses);
    $blockshtml = $OUTPUT->blocks('side-pre');
    $hasblocks = strpos($blockshtml, 'data-block=') !== false;
    $regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
    $in_course_page = $PAGE->pagelayout == "course";
    $not_in_course_page = $PAGE->pagelayout != "course";
    $within_course_page = $PAGE->pagelayout == "incourse";
    $not_within_course_page = $PAGE->pagelayout != "incourse";
    $course_name = $COURSE->fullname;
    $course_code = $COURSE->shortname;
    $inte_suap = is_siteadmin() ? "show_suap"  : '';

    // TODO: quando tem que mostrar os blocos mesmo?
    $hasblocks = false;
    return [
        'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
        'output' => $OUTPUT,
        'sidepreblocks' => $blockshtml,
        'hasblocks' => $hasblocks,
        'bodyattributes' => $bodyattributes,
        'navdraweropen' => $navdraweropen,
        'regionmainsettingsmenu' => $regionmainsettingsmenu,
        'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
        'link_calendar' => (new moodle_url('/calendar/view.php?view=month'))->out(),
        'link_sala_aula' => (new moodle_url('/my'))->out(),
        'link_suap' => (new moodle_url('/suap'))->out(),
        'link_mural' => (new moodle_url('/mural'))->out(),
        'link_secretaria' => (new moodle_url('/secretaria'))->out(),
        'in_course_page' => $in_course_page,
        'not_in_course_page' => $not_in_course_page,
        'incourse' => $COURSE,
        'course' => $COURSE,
        'within_course_page' => $within_course_page,
        'not_within_course_page' => $not_within_course_page,
        'course_name' => $course_name,
        'inte_suap' => $inte_suap
    ];
}

function get_ledor_calendario() {
    global $CFG, $COURSE;
    $calendar = \calendar_information::create(time(), $COURSE->id, $COURSE->category);
    list($data, $template) = calendar_get_view($calendar, 'upcoming_mini');
    if (sizeof($data->events) == 0) {
        return false;
    }
    $result = [];
    foreach ($data->events as $key => $value) {
        $shortdate = date('d M', $value->timestart);
        if (!array_key_exists($shortdate, $result)) {
            $result[$shortdate] = new stdClass();
            $result[$shortdate]->shortdate = $shortdate;

            $data_mes = explode(" ", $shortdate);

            $result[$shortdate]->shortdate_dia = $data_mes[0];
            $result[$shortdate]->shortdate_mes = $data_mes[1];
            $result[$shortdate]->viewurl = $value->viewurl;
            $result[$shortdate]->events = [];
        }
        $result[$shortdate]->events[] = $value;
    }
    return new ArrayIterator($result);
}

function get_ledor_course_summary()
{
    global $PAGE, $COURSE;
    if ($PAGE->pagelayout == "course" || $PAGE->pagelayout == "incourse") {
        $flatnav = [];
        foreach ($PAGE->flatnav as $child_key) {
            if ($child_key->type == 30) {
                $flatnav[] = $child_key;
            }
        }
        return new ArrayIterator($flatnav);
    }
}

class ledor_menu_item {
    public $action_url;
    public $icon;
    public $label;

    public function __construct($label, $url, array $params = null, $anchor = null) {
        $this->label = $label;
        $this->url = new moodle_url($url, $params, $anchor);
    }
}

function get_ledor_bottom_menu()
{
    /***
     * Isto deveria vir de configuração do tema salvo no banco.
     */
    global $PAGE, $COURSE, $USER;
    $result = [];
    if ($PAGE->pagelayout == "course" || $PAGE->pagelayout == "incourse") {
        $result[] = new ledor_menu_item("Notas", "/grade/report/index.php", ['id'=>$COURSE->id]);
        $result[] = new ledor_menu_item("Participantes", "/user/index.php", ['id'=>$COURSE->id]);
        $result[] = new ledor_menu_item("Emblemas", "/badges/view.php", ['type'=>2, 'id'=>$COURSE->id]);
        $result[] = new ledor_menu_item("Competências", "/admin/tool/lp/coursecompetencies.php", ['courseid'=>$COURSE->id]);
    }

    $result[] = new ledor_menu_item("Página inicial", "/", []);
    $result[] = new ledor_menu_item("Perfil", "/user/profile.php", ['id'=>$USER->id]);
    $result[] = new ledor_menu_item("Mensagens", "/message/", []);
    $result[] = new ledor_menu_item("Notificações", "/message/output/popup/notifications.php", []);
    $result[] = new ledor_menu_item("Calendário geral", "/calendar/", []);
    $result[] = new ledor_menu_item("Conhecer as teclas de atalho", "/", []);
    $result[] = new ledor_menu_item("Sair", "/login/logout.php", []);
    return new ArrayIterator($result);
}

function get_ledor_template_context()
{
    global $PAGE;

    $templatecontext = get_ledor_commom_moodle_template_context();

    if ($templatecontext['in_course_page'] || $templatecontext['within_course_page']) {
        $templatecontext['course_summary'] = get_ledor_course_summary();
    }
    $templatecontext['bottom_menu'] = get_ledor_bottom_menu();
    // $templatecontext['nosso_calendario'] = get_ledor_calendario();
    return $templatecontext;
};
