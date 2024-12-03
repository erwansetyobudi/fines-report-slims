#fines_report.php --> fines report in slims with total fines each week in a month

#fines_report_year.php --> fines report in slims eith total fines each month in a year
# How too use?
1. You can replace file fines_report.php in your slims folder. The path is admin/modules/reporting/customs.
2. You can add file fines_report_year.php at admin/modules/reporting/customs/. And then add submenu in admin/modules/reporting/customs/customs_report_list.inc.php
   add this code at last row.
   $menu[] = array(__('Fines Report Year'), MWB.'reporting/customs/fines_report_year.php', __('Fines Report Year'));

fines report month
![fine report month](https://github.com/user-attachments/assets/8c8cb83c-61fd-4a25-864e-0ef77608e2bf)


fines report year

![Screenshot 2024-12-03 at 09-49-49 PERPUSTAKAAN SMA KOLESE LOYOLA Sistem Manajemen Perpustakaan Senayan](https://github.com/user-attachments/assets/3b1cc798-5c90-4caa-8cf0-1f7d6a30ea8c)
