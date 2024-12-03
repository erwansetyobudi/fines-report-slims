<?php
/**
 * Fines Report
 */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-reporting');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.('You don\'t have enough privileges to access this area!').'</div>');
}

require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO.'simbio_UTILS/simbio_date.inc.php';

// months array
$months = [
    '01' => __('Jan'), '02' => __('Feb'), '03' => __('Mar'),
    '04' => __('Apr'), '05' => __('May'), '06' => __('Jun'),
    '07' => __('Jul'), '08' => __('Aug'), '09' => __('Sep'),
    '10' => __('Oct'), '11' => __('Nov'), '12' => __('Dec')
];

$page_title = 'Fines Report';
$reportView = false;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
    <!-- Filter -->
    <div class="container mt-4">
        <div class="per_title">
            <h2><?php echo __('Fines Report'); ?></h2>
        </div>
        <div class="infoBox mb-3">
            <?php echo __('Report Filter'); ?>
        </div>
        <div class="sub_section">
            <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
                <div class="form-group">
                    <label for="year"><?php echo __('Year'); ?></label>
                    <?php
                    $current_year = date('Y');
                    $year_options = [];
                    for ($y = $current_year; $y > 1999; $y--) {
                        $year_options[] = [$y, $y];
                    }
                    echo simbio_form_element::selectList('year', $year_options, $current_year, 'class="form-control" id="year"');
                    ?>
                </div>
                <button type="submit" class="btn btn-primary"><?php echo __('Apply Filter'); ?></button>
                <input type="hidden" name="reportView" value="true" />
            </form>
        </div>
        <!-- Filter End -->
        <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
    </div>
<?php
} else {
    ob_start();
    $fines_data = [];
    $total_fines = 0;  // Variabel untuk menyimpan total denda

    // year
    $selected_year = date('Y');
    if (isset($_GET['year']) AND !empty($_GET['year'])) {
        $selected_year = (integer)$_GET['year'];
    }

    // Query fines data to database
    $_fines_q = $dbs->query("SELECT SUBSTRING(fines_date, 6, 2) AS month, SUM(debet) AS dtotal 
                              FROM fines 
                              WHERE fines_date LIKE '$selected_year-%' 
                              GROUP BY month");

    while ($_fines_d = $_fines_q->fetch_row()) {
        $month = $_fines_d[0];
        $fines_data[$month] = $_fines_d[1];
        $total_fines += $_fines_d[1];
    }

    // Generate Table
    echo '<div class="container mt-4">';
    echo '<h4>' . __('Fines Report for Year') . ' <strong>' . $selected_year . '</strong></h4>';
    echo '<a class="btn btn-secondary mb-3" onclick="window.print()" href="#">' . __('Print Current Page') . '</a>';
    echo '<table class="table table-bordered table-striped">';
    echo '<thead class="thead-dark"><tr><th>' . __('Month') . '</th><th class="text-right">' . __('Fines Amount') . '</th></tr></thead>';
    echo '<tbody>';
    
    foreach ($months as $month_num => $month_name) {
        $fines_amount = isset($fines_data[$month_num]) ? $fines_data[$month_num] : 0;
        echo '<tr>';
        echo '<td>' . $month_name . '</td>';
        echo '<td class="text-right">' . currency($fines_amount) . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '<tfoot>';
    echo '<tr><th>' . __('Total') . '</th><th class="text-right">' . currency($total_fines) . '</th></tr>';
    echo '</tfoot>';
    echo '</table>';
    echo '</div>';

    $content = ob_get_clean();
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
}
