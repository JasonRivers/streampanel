<?php

    function humansize($bytes, $network = false, $precision = 2) {
        $units = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
        $step = 1024;
        $i = 0;
        while (($bytes / $step) > 0.9) {
            $bytes = $bytes / $step;
            $i++;
        }
        $unit = $units[$i];
        if ($network) {
            $unit = ' ' . strtolower($unit) . 'ps';
        }
        return round($bytes, $precision) . $unit;
    }