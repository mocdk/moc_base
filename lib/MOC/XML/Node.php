<?php
/**
 * MOC XML Node class
 * 
 * An extension to SimpleXMLElement 
 * 
 * @author Christian Winther <cwin@mocsystems.com>
 * @since 24.11.2009
 * @version $Revision$
 * @modifiedby $LastChangedBy$
 * @lastmodified $Date$
 */ 
class MOC_XML_Node extends SimpleXMLElement {
    
    /**
     * Get an instance of MOC_XML_Node with already initialized doc type and container tag
     * 
     * @static
     * @param string $name The name of the outmost xml tag
     * @param boolean $sendXMLHeader Should the script send the text/xml header?
     * @return MOC_XML_Node
     */ 
    public static function getInstance($name, $sendXMLHeader = true, $xmlHeader = true) {
        if ($sendXMLHeader) {
            header('Content-Type: text/xml');
        }
		$xmlBody = sprintf('<%1$s></%1$s>', $name);
		if ($xmlHeader) {
			$xmlBody = sprintf('<?xml version="1.0" encoding="UTF-8"?>%s', $xmlBody);
		}
		
        return new MOC_XML_Node($xmlBody);
    }
    
	public function AddXMLElement(SimpleXMLElement $Node) {
		$node1 = dom_import_simplexml($this); 
		$dom_sxe = dom_import_simplexml($Node); 
		$node2 = $node1->ownerDocument->importNode($dom_sxe, true); 
		$node1->appendChild($node2);
		return $this;
    }

    /**
     * Add a new node with a value wrapped in CDATA tags
     * 
     * @param string $name 
     * @param string $value
     * @return MOC_XML_Node
     */ 
    public function addCData($name, $value) { 
        $ChildNode = $this->addChild($name); //Added a nodename to create inside the function 
        $DomNode = dom_import_simplexml($ChildNode); 
        $DomOwnerDocument = $DomNode->ownerDocument; 

        if (!MOC_String::isValidUTF8($value)) {
            $value = mb_convert_encoding($value, 'UTF-8');
        }

        $DomNode->appendChild($DomOwnerDocument->createCDATASection($value));
        return $ChildNode;
    }  
}
