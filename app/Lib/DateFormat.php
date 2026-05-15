<?php

namespace App\Lib;

use Carbon\Carbon;

class DateFormat
{
    /**
     * Year only format eg：2023)
     *
     * @param string $date
     * @return string
     */
    public static function YearOnlyFormat($date)
    {
        $carbon = new Carbon($date);
        return $carbon->isoFormat('YYYY');
    }

    /**
     * Date format with slash (eg: 2023/12/28)
     *
     * @param string $date
     * @return string
     */
    public static function dateFormatSlash($date)
    {
        $carbon = new Carbon($date);
        if ($date) {
            return $carbon->format('Y/m/d');
        }
    }

    /**
     * Date format(eg：2023/2/3)
     * RemoveLeadingZero
     *
     * @param string $date
     * @return string
     */
    public static function dateFormatSlashWithRemoveLeadingZero($date)
    {
        $carbon = new Carbon($date);
        if ($date) {
            return $carbon->format('Y/n/j');
        }
    }

    /**
     * Date format with hyphen(eg：2023-12-28)
     *
     * @param string $date
     * @return string
     */
    public static function dateFormatHyphen($date)
    {
        $carbon = new Carbon($date);
        if ($date) {
            return $carbon->format('Y-m-d');
        }
    }

    /**
     * Datetime format(eg：2019/10/16 00:00)
     *
     * @param string $datetime
     * @return string
     */
    public static function dateTimeFormat($datetime)
    {
        $carbon = new Carbon($datetime);
        if ($datetime) {
            return $carbon->format('Y/m/d H:i');
        }
    }

    /**
     * Datetime format ISO(eg：2023-12-28 00:00:00)
     *
     * @param string $datetime
     * @return string
     */
    public static function dateTimeisoFormat($datetime)
    {
        $carbon = new Carbon($datetime);
        if ($datetime) {
            return $carbon->format('Y-m-d H:i:s');
        }
    }

    /**
     * Datetime hi format(eg：00:00)
     *
     * @param string $date
     * @return string
     */
    public static function dateTimehiFormat($date)
    {
        $carbon = new Carbon($date);
        if ($date) {
            return $carbon->format('H:i');
        }
    }
}
