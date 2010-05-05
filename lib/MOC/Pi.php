<?php
class MOC_Pi {
    public static function getPidList($container, $depth = 0) {
        $PiBase = MOC_EID::getInstanceOfPiBase();
        return $PiBase->pi_getPidList($container, $depth);
    }
}
