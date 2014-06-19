<?php
/**
 * tx_tcforum TYPO3 db callback / hook class
 *
 * @author Christian Winther <cwin@mocsystems.com>
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 * @since 10.01.2010
 */
class MOC_DB_Hook {

    /**
     * List of tables this class should handle
     *
     * @var array
     */
    protected static $tables = array('tx_idagroups_group');

    public static function bind() {
        $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'MOC_DB_Hook';
    }

    /**
     * TYPO3 hook
     *
     * Called when any database operation has been executed
     *
     * @param string $status insert / update
     * @param string $table
     * @param integer $id
     * @param array $fieldArray List of fields that was changed in the data operation
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $Obj Reference to the \TYPO3\CMS\Core\DataHandling\DataHandler object
     */
    public static function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, \TYPO3\CMS\Core\DataHandling\DataHandler $Obj) {
        MOC_Event_Dispatcher::notify(new MOC_Event($Obj, sprintf('%s.%s', $table, $status), compact('status', 'table', 'id', 'fieldArray')));
    }

    /**
     * TYPO3 hook
     *
     * @param array $incomingFieldArray
     * @param string $table
     * @param string|integer $id
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $Obj
     */
    public static function processDatamap_preProcessFieldArray($incomingFieldArray, $table, $id, \TYPO3\CMS\Core\DataHandling\DataHandler $Obj) {
        MOC_Event_Dispatcher::notify(new MOC_Event($Obj, sprintf('%s.%s', $table, 'pre_process'), compact('incomingFieldArray', 'table', 'id')));
    }

    /**
     * TYPO3 hook
     *
     * @param string $status
     * @param string $table
     * @param string|intenger $id
     * @param array $fieldArray
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $Obj
     */
    public static function processDatamap_postProcessFieldArray($status, $table, $id, $fieldArray, \TYPO3\CMS\Core\DataHandling\DataHandler $Obj) {
        //MOC::debug(compact('status', 'table', 'id', 'fieldArray'), true);
    }

    /**
     * TYPO3 hook
     *
     * @param string $command
     * @param string $table
     * @param integer $id
     * @param string value
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $Obj
     */
    public static function processCmdmap_preProcess($command, $table, $id, $value, \TYPO3\CMS\Core\DataHandling\DataHandler $Obj) {
        //MOC::debug(compact('command', 'table', 'id', 'value'));
    }

    /**
     * TYPO3 hook
     *
     * @param string $command
     * @param string $table
     * @param integer $id
     * @param mixed $value
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $Obj
     */
    public static function processCmdmap_postProcess($command, $table, $id, $value, \TYPO3\CMS\Core\DataHandling\DataHandler $Obj) {
        // Only handle specific tables
//        if (false === array_search($table, self::$tables)) {
//            return false;
//        }

        // We only want to handle delete events (dont care about moves)
//        if ($command !== 'delete') {
//            return false;
//        }

//        $ExtConfig = MOC_Chat::getConfiguration();
//        $file = $ExtConfig->get('room_configuration_directory') . DIRECTORY_SEPARATOR . $id . '.ini';
//        if (is_file($file)) {
//            unlink($file);
//        }

        // Truncate chat history for the channel
//        MOC_Chat_Model_History::truncate($id);

        //MOC::debug(compact('command', 'table', 'id', 'value'));
    }

    /**
     * TYPO3 hook
     *
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $Obj
     */
    public static function processDatamap_afterAllOperations(\TYPO3\CMS\Core\DataHandling\DataHandler $Obj) {
    }
}
?>