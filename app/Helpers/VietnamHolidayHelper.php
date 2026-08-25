<?php

namespace App\Helpers;

use Carbon\Carbon;

class VietnamHolidayHelper
{
    /**
     * Lấy tên ngày lễ Việt Nam (Dương lịch & Âm lịch)
     */
    public static function getHolidayName(Carbon $date): ?string
    {
        $solarDay = $date->day;
        $solarMonth = $date->month;
        $solarYear = $date->year;

        // 1. Kiểm tra các ngày lễ Dương Lịch cố định
        $solarKey = sprintf('%02d-%02d', $solarMonth, $solarDay);
        $solarHolidays = [
            '01-01' => 'Tết Dương Lịch (01/01)',
            '04-30' => 'Ngày Giải Phóng Miền Nam (30/04)',
            '05-01' => 'Ngày Quốc Tế Lao Động (01/05)',
            '09-02' => 'Ngày Quốc Khánh (02/09)',
            '09-03' => 'Ngày Quốc Khánh (03/09)',
        ];

        if (isset($solarHolidays[$solarKey])) {
            return $solarHolidays[$solarKey];
        }

        // 2. Kiểm tra các ngày lễ Âm Lịch (Chuyển đổi Dương lịch -> Âm lịch UTC+7)
        $lunar = self::convertSolarToLunar($solarDay, $solarMonth, $solarYear);
        $lDay = $lunar['day'];
        $lMonth = $lunar['month'];

        // Giỗ Tổ Hùng Vương: 10/03 Âm lịch
        if ($lMonth == 3 && $lDay == 10) {
            return 'Giỗ Tổ Hùng Vương (10/03 Âm Lịch)';
        }

        // Tết Nguyên Đán: Tất niên (29 hoặc 30 tháng 12 AL) và Mùng 1 đến Mùng 5 tháng 1 AL
        if ($lMonth == 12 && ($lDay == 29 || $lDay == 30)) {
            return 'Đêm Giao Thừa / Tất Niên Tết Nguyên Đán';
        }

        if ($lMonth == 1 && $lDay >= 1 && $lDay <= 5) {
            return 'Tết Nguyên Đán (Mùng ' . $lDay . ')';
        }

        return null;
    }

    public static function isHoliday(Carbon $date): bool
    {
        return self::getHolidayName($date) !== null;
    }

    /**
     * Thuật toán chuyển đổi ngày Dương lịch sang Âm lịch chuẩn múi giờ Việt Nam (UTC+7)
     */
    private static function INT($d) {
        return floor($d);
    }

    private static function jdFromDate($dd, $mm, $yy) {
        $a = self::INT((14 - $mm) / 12);
        $y = $yy + 4800 - $a;
        $m = $mm + 12 * $a - 3;
        return $dd + self::INT((153 * $m + 2) / 5) + 365 * $y + self::INT($y / 4) - self::INT($y / 100) + self::INT($y / 400) - 32045;
    }

    private static function getNewMoonDay($k, $timeZone = 7) {
        $T = $k / 1236.85;
        $T2 = $T * $T;
        $T3 = $T2 * $T;
        $dr = M_PI / 180;
        $Jd1 = 2415020.75933 + 29.53058868 * $k + 0.0001178 * $T2 - 0.000000155 * $T3;
        $Jd1 += 0.00033 * sin((166.56 + 132.87 * $T - 0.009173 * $T2) * $dr);
        $M = 359.2242 + 29.10535608 * $k - 0.0000333 * $T2 - 0.00000347 * $T3;
        $Mpr = 306.0253 + 385.81691806 * $k + 0.0107306 * $T2 + 0.00001236 * $T3;
        $F = 21.2964 + 390.67050646 * $k - 0.0016528 * $T2 - 0.00000239 * $T3;
        $C1 = (0.1734 - 0.000393 * $T) * sin($M * $dr) + 0.0021 * sin(2 * $M * $dr);
        $C1 = $C1 - 0.4068 * sin($Mpr * $dr) + 0.0161 * sin(2 * $Mpr * $dr);
        $C1 = $C1 - 0.0004 * sin(3 * $Mpr * $dr);
        $C1 = $C1 + 0.0104 * sin(2 * $F * $dr) - 0.0051 * sin(($M + $Mpr) * $dr);
        $C1 = $C1 - 0.00074 * sin(($M - $Mpr) * $dr) + 0.0004 * sin((2 * $F + $M) * $dr);
        $C1 = $C1 - 0.0004 * sin((2 * $F - $M) * $dr) - 0.0006 * sin((2 * $F + $Mpr) * $dr);
        $C1 = $C1 + 0.0010 * sin((2 * $F - $Mpr) * $dr) + 0.0005 * sin(($M + 2 * $Mpr) * $dr);
        if ($T < -11) {
            $deltat = 0.001 + 0.000839 * $T + 0.0002261 * $T2 - 0.00000845 * $T3 - 0.000000081 * $T * $T3;
        } else {
            $deltat = -0.00002 + 0.000297 * $T + 0.001029 * $T2 + 0.000941 * $T3 + 0.000256 * $T * $T3;
        }
        $JdNew = $Jd1 + $C1 - $deltat;
        return self::INT($JdNew + 0.5 + $timeZone / 24);
    }

    private static function getSunLongitude($dayNumber, $timeZone = 7) {
        $T = ($dayNumber - 2451545.5 - $timeZone / 24) / 36525;
        $T2 = $T * $T;
        $dr = M_PI / 180;
        $M = 357.52910 + 35999.05030 * $T - 0.0001559 * $T2 - 0.00000048 * $T * $T2;
        $L0 = 280.46645 + 36000.76983 * $T + 0.0003032 * $T2;
        $DL = (1.914602 - 0.004817 * $T - 0.000014 * $T2) * sin($M * $dr);
        $DL += (0.019993 - 0.000101 * $T) * sin(2 * $M * $dr) + 0.000289 * sin(3 * $M * $dr);
        $L = $L0 + $DL;
        $L = $L - 360 * self::INT($L / 360);
        return self::INT($L / 30);
    }

    private static function getLunarMonth11($yy, $timeZone = 7) {
        $off = self::jdFromDate(31, 12, $yy) - 2415021;
        $k = self::INT($off / 29.5305888);
        $nm = self::getNewMoonDay($k, $timeZone);
        $sunLong = self::getSunLongitude($nm, $timeZone);
        if ($sunLong >= 9) {
            $nm = self::getNewMoonDay($k - 1, $timeZone);
        }
        return $nm;
    }

    public static function convertSolarToLunar($dd, $mm, $yy, $timeZone = 7) {
        $dayNumber = self::jdFromDate($dd, $mm, $yy);
        $k = self::INT(($dayNumber - 2415021) / 29.5305888);
        $monthStart = self::getNewMoonDay($k + 1, $timeZone);
        if ($monthStart > $dayNumber) {
            $monthStart = self::getNewMoonDay($k, $timeZone);
        }
        $a11 = self::getLunarMonth11($yy, $timeZone);
        $b11 = $a11;
        if ($a11 >= $monthStart) {
            $lunarYear = $yy;
            $a11 = self::getLunarMonth11($yy - 1, $timeZone);
        } else {
            $lunarYear = $yy + 1;
            $b11 = self::getLunarMonth11($yy + 1, $timeZone);
        }
        $lunarDay = $dayNumber - $monthStart + 1;
        $diff = self::INT(($monthStart - $a11) / 29);
        $lunarLeap = 0;
        $lunarMonth = $diff + 11;

        if ($b11 - $a11 > 365) {
            $leapMonthDiff = self::getLeapMonthOffset($a11, $timeZone);
            if ($diff >= $leapMonthDiff) {
                $lunarMonth = $diff + 10;
                if ($diff == $leapMonthDiff) {
                    $lunarLeap = 1;
                }
            }
        }
        if ($lunarMonth > 12) {
            $lunarMonth = $lunarMonth - 12;
        }
        return [
            'day' => (int)$lunarDay,
            'month' => (int)$lunarMonth,
            'year' => (int)$lunarYear,
            'leap' => $lunarLeap
        ];
    }

    private static function getLeapMonthOffset($a11, $timeZone = 7) {
        $k = self::INT(($a11 - 2415021) / 29.5305888 + 0.5);
        $last = 0;
        $i = 1;
        $arc = self::getSunLongitude(self::getNewMoonDay($k + $i, $timeZone), $timeZone);
        do {
            $last = $arc;
            $i++;
            $arc = self::getSunLongitude(self::getNewMoonDay($k + $i, $timeZone), $timeZone);
        } while ($arc != $last && $i < 14);
        return $i - 1;
    }
}