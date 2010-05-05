<?php
MOC_Annotation::load();

/**
 * MOC Api Annotation Alias class
 * 
 * Use this annotation to create class alias between a function name and the class method
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 * @since 06.01.2010
 */
class MOC_Api_Annotation_Alias extends Annotation {
    public $method;
    public $alias;
}