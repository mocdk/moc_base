<?php
MOC_Annotation::load();

/**
 * MOC Api Annotation Api class
 * 
 * Addendum needs this object to be able to recongnize MOC_Api_Annotation_Api annotations
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 * @since 06.01.2010
 */
class MOC_Api_Annotation_Api extends Annotation {
    public $method;
    public $alias;
}