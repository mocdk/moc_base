<?php
/**
 * Interface for autoload delegates to implement
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 13.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */
interface MOC_Autoload_Interface {
    public function getPath($className);

}
