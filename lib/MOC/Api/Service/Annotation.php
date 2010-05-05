<?php
/**
 * MOC Api Service Annotation class
 * 
 * Overwrites MOC_Api_Abstract::provides with an annotation check instead of a simple method_exists
 * 
 * Any method that has @MOC_Api_Annotation_Api annotation is accepted as a provider method
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 * @since 06.01.2010
 */
abstract class MOC_Api_Service_Annotation extends MOC_Api_Abstract {

    /**
     * A map between a method alias and the real method
     * 
     * @var array
     */
    protected $alias = array();

    /**
     * Called in parent constructor
     * 
     * Build a list of annotation aliases (if any) 
     */
    protected function bootstrap() {
        $ClassAnnotation = MOC_Annotation::forClass($this);
        foreach ($ClassAnnotation->getAllAnnotations() as $Annotation) {
            // We are only interested in annotation aliases
            if (get_class($Annotation) !== 'MOC_Api_Annotation_Alias') {
                continue;
            }

            $alias = $Annotation->alias;
            $method = $Annotation->method;

            $this->alias[$method] = $alias;
        }
    }

    /**
     * Method invoked by MOC_Api_Service
     * 
     * If the method returns true, the MOC_Api_Service object calls
     * invoke on this object
     * 
     * @return boolean
     */
    public function provides($method) {
        try {
            $MethodAnnotation = MOC_Annotation::forClassMethod($this, $this->getMethodName($method));
            return $MethodAnnotation->hasAnnotation('MOC_Api_Annotation_Api');
        }
        catch (ReflectionException $E) {
            return false;
        }
    }

    /**
     * Enable alias check of method names
     * 
     * @see MOC_Api_Abstract::getMethodName
     * @param string $name
     * @param string $prefix
     * @return string
     */
    protected function getMethodName($name, $prefix = 'execute') {
        if (array_key_exists($name, $this->alias)) {
            $name = $this->alias[$name];
        }
        return parent::getMethodName($name, $prefix);
    }
}
