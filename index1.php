<?php
/**
 * Buddhist Lunar calendar
 * Version "index1.php" is somehow optimized, but less easily understandable;
 * for comments and clarity, see the original version in "index.php".
 */
$year = file("year.html");
$dates = file("dates.txt", FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
$datesS = ["/\s*новолуние\s+(\d\d)\.0?(\d+)\.(\d\d\d\d)(?:\D*)(\d\d):(?:.*)/",
           "/\s*первая четверть\s+(\d\d)\.0?(\d+)\.(\d\d\d\d)(?:\D*)(\d\d):(?:.*)/",
           "/\s*полнолуние\s+(\d\d)\.0?(\d+)\.(\d\d\d\d)(?:\D*)(\d\d):(?:.*)/",
           "/\s*третья четверть\s+(\d\d)\.0?(\d+)\.(\d\d\d\d)(?:\D*)(\d\d):(?:.*)/"];
$datesR = ["n,$3-$2-$1,$4", "q1,$3-$2-$1,$4", "f,$3-$2-$1,$4", "q4,$3-$2-$1,$4"];
$markDays = preg_filter($datesS, $datesR, $dates);
$yearS = [];
$yearR = [];
foreach($markDays as $date) {
    $dayAsArray = explode(",", $date);
    $yearS[] = '/(.*)class=\"(?:.+?)\"(.*)(' . $dayAsArray[1] . ')(.*)/';
    $yearR[] = '$1class="' . $dayAsArray[0] . '"$2$3$4';
    if ($dayAsArray[0] === "n" || $dayAsArray[0] === "f" ) {
        $yearR[] = '$1class="' . $dayAsArray[0] . '2' . '"$2$3$4';
        if ($dayAsArray[2] < 12)
            $yearS[] = '/(.*)class=\"(?:.+?)\"(.*)(' . date('Y-n-d', strtotime($dayAsArray[1] .' -1 day')) . ')(.*)/';
        else
            $yearS[] = '/(.*)class=\"(?:.+?)\"(.*)(' . date('Y-n-d', strtotime($dayAsArray[1] .' +1 day')) . ')(.*)/';
    }
}
$year = preg_replace($yearS, $yearR, $year, 1);
foreach($year as $nextString) echo $nextString;
