<?php
/**
 * Laporan Denda Per Tahun
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

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

// Set judul halaman
$page_title = 'Laporan Denda Per Tahun';

if (!isset($_GET['reportView'])) {
?>
    <div class="per_title">
        <h2><?php echo __('Laporan Denda Per Tahun'); ?></h2>
    </div>
    <div class="infoBox">
        <?php echo __('Pilih tahun untuk melihat laporan.'); ?>
    </div>
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
        <div class="form-group">
            <label><?php echo __('Tahun'); ?></label>
            <?php
            $current_year = date('Y');
            $year_options = array();
            for ($y = $current_year; $y > 1999; $y--) {
                $year_options[] = array($y, $y);
            }
            echo simbio_form_element::selectList('year', $year_options, $current_year, 'class="form-control"');
            ?>
        </div>
        <input type="submit" class="btn btn-primary" value="<?php echo __('Lihat Laporan'); ?>" />
        <input type="hidden" name="reportView" value="true" />
    </form>
    <iframe name="reportView" id="reportView" src="" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    $selected_year = date('Y');
    if (isset($_GET['year']) && !empty($_GET['year'])) {
        $selected_year = (int)$_GET['year'];
    }

    // Query untuk mendapatkan total denda per bulan
    $query = "SELECT MONTH(fines_date) AS month, SUM(debet) AS total_denda 
              FROM fines 
              WHERE YEAR(fines_date) = $selected_year 
              GROUP BY MONTH(fines_date)";
    $result = $dbs->query($query);

    $monthly_fines = array_fill(1, 12, 0); // Inisialisasi denda per bulan
    while ($row = $result->fetch_assoc()) {
        $monthly_fines[(int)$row['month']] = (int)$row['total_denda'];
    }

    // Total denda setahun
    $total_fines = array_sum($monthly_fines);

    // Tampilkan tabel laporan
    echo '<h2>'.__('Laporan Denda Tahun').' '.$selected_year.'</h2>';
    echo '<table class="table table-bordered">';
    echo '<thead><tr>';
    foreach (range(1, 12) as $month) {
        echo '<th>'.date('F', mktime(0, 0, 0, $month, 1)).'</th>';
    }
    echo '</tr></thead><tbody><tr>';
    foreach ($monthly_fines as $fine) {
        echo '<td>'.currency($fine).'</td>';
    }
    echo '</tr></tbody>';
    echo '<tfoot><tr><th colspan="12">'.__('Total Denda').': '.currency($total_fines).'</th></tr></tfoot>';
    echo '</table>';
}
?>
