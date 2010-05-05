<?php
/**
 * MOC Date class
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 13.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
class MOC_Date {
    public static function duration($seconds, $use = null, $zeros = false) {
        // Define time periods
        $periods = array(
            'years' => 31556926, 
            'Months' => 2629743, 
            'weeks' => 604800, 
            'days' => 86400, 
            'hours' => 3600, 
            'minutes' => 60, 
            'seconds' => 1
        );

        // Break into periods
        $seconds = (float)$seconds;
        foreach ($periods as $period => $value) {
            if ($use && strpos($use, $period[0]) === false) {
                continue;
            }

            $count = floor($seconds / $value);
            if ($count == 0 && !$zeros) {
                continue;
            }
            $segments[strtolower($period)] = $count;
            $seconds = $seconds % $value;

        }

        // Build the string
        foreach ($segments as $key => $value) {
            $segment_name = substr($key, 0, -1);
            $segment = $value . ' ' . $segment_name;
            if ($value != 1) {
                $segment .= 's';
            }
            $array[] = $segment;
        }

        $str = implode(', ', $array);
        return $str;
    }
}