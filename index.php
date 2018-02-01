<?php
/**
 * Buddhist Lunar calendar
 * Author: Chang Zhao
 * Date: 01.02.2018
 * Time: 6:37
 * Input files:
 *   year.html - calendar template for the year
 *   dates.txt - data on moon phases & festivals
 * Output: calendar for the year with moon phases & festivals
 */

// Read input files
$year = file("year.html");
$dates = file("dates.txt", FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);


// Searching the dates file, prepare the array of patterns to search & replace in the year file

// Search & replace patterns for the dates

// 1. Simplified version:
// $datesS = "/\s*(\D*)\s+(\d\d)\.0?(\d+)\.(\d\d\d\d)(?:\D*)(\d\d):(?:.*)/";
// $datesR = "$1,$4-$3-$2,$5";
// So we would transform
// "	новолуние 	09.09.2018 в 21:01 	и далее первая фаза луны"
// into
// "новолуние,2018-9-09,21"
//
// 2. This version:
// With an array of replacements, we replace phase name with proper CSS class name
// => "n,2018-9-09,21"
// n = new
// f = full
// q1, q4 = quarters

$datesS = ["/\s*новолуние\s+(\d\d)\.0?(\d+)\.(\d\d\d\d)(?:\D*)(\d\d):(?:.*)/",
           "/\s*первая четверть\s+(\d\d)\.0?(\d+)\.(\d\d\d\d)(?:\D*)(\d\d):(?:.*)/",
           "/\s*полнолуние\s+(\d\d)\.0?(\d+)\.(\d\d\d\d)(?:\D*)(\d\d):(?:.*)/",
           "/\s*третья четверть\s+(\d\d)\.0?(\d+)\.(\d\d\d\d)(?:\D*)(\d\d):(?:.*)/"];
$datesR = ["n,$3-$2-$1,$4", "q1,$3-$2-$1,$4", "f,$3-$2-$1,$4", "q4,$3-$2-$1,$4"];

$markDays = preg_filter($datesS, $datesR, $dates);

// Now $markDays is an array of strings like
// n,2018-1-17,05
// q1,2018-1-25,01
// f,2018-1-31,16
// q4,2018-2-07,18
//
// = "CSS class, date, hour"

// In Buddhist traditions, Uposatha goes 2 days for each full & new moon.
// Thus for each full and new moon we have to add the second day.
// if (hour < 12), we add the previous day; otherwise the next day.

$uposathaDays = [];

// Now fill it:
// $uposathaDays[n, 0] = class styles for days: n, q1, f, q4 (also n2 & f2 for 2nd days of new or full moon)
// $uposathaDays[n, 1] = dates: Y-n-d

foreach($markDays as $date) {
    $dayAsArray = explode(",", $date);
    $uposathaDays[] = [$dayAsArray[0], $dayAsArray[1]];

    if ($dayAsArray[0] === "n" || $dayAsArray[0] === "f" ) {    // full or new moon
        if ($dayAsArray[2] < 12)
            $uposathaDays[] = [$dayAsArray[0]."2", date('Y-n-d', strtotime($dayAsArray[1] .' -1 day'))];
        else
            $uposathaDays[] = [$dayAsArray[0]."2", date('Y-n-d', strtotime($dayAsArray[1] .' +1 day'))];
    }
}

// Now the array of $uposathaDays looks like:
// n, 2018-1-17   - new moon
// n2, 2018-1-16  - also new moon
// q1, 2018-1-25  - quarter
// f, 2018-1-31   - full moon
// f2, 2018-2-01  - also full moon
// q4, 2018-2-07  - quarter

// Finally, search and replace days' CSS styles in the calendar template for the year
// so that e.g.
//     <td class="month_day" id="2018-3-17">17</td>
// turns into
//     <td class="n" id="2018-3-17">17</td>
// showing that day as new moon

$yearS = []; // aray for search patterns
$yearR = []; // aray for replace patterns
foreach($uposathaDays as $day) {                                      // For example:
    $yearS[] = '/(.*)class=\"(?:.+?)\"(.*)(' . $day[1] . ')(.*)/';    // (.*)class=\"(?:.+?)\"(.*)(2018-3-17)(.*)
    $yearR[] = '$1class="' . $day[0] . '"$2$3$4';                     // $1class="n"$2$3$4
}
$year = preg_replace($yearS, $yearR, $year, 1);

foreach($year as $nextString) echo $nextString;

