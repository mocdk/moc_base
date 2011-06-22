<?php
/*
 * MOC_Event_Dispatcher implements a dispatcher object.
 *
 * @see http://developer.apple.com/documentation/Cocoa/Conceptual/Notifications/index.html Apple's Cocoa framework
 *
 * @package    symfony
 * @subpackage event_dispatcher
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class MOC_Event_Dispatcher {
	protected static $listeners = array();

  	/**
   	 * Connects a listener to a given event name.
   	 *
   	 * @param string  $name      An event name
   	 * @param mixed   $listener  A PHP callable
	 */
  	public static function connect($name, $listener) {
    	if (!isset(self::$listeners[$name])) {
      		self::$listeners[$name] = array();
    	}
    	self::$listeners[$name][] = $listener;
	}

  	/**
   	 * Disconnects a listener for a given event name.
     *
   	 * @param string   $name      An event name
     * @param mixed    $listener  A PHP callable
   	 * 
     * @return mixed false if listener does not exist, null otherwise
     */
  	public static function disconnect($name, $listener) {
    	if (!isset(self::$listeners[$name])) {
      		return false;
    	}

    	foreach (self::$listeners[$name] as $i => $callable) {
      		if ($listener === $callable) {
        		unset(self::$listeners[$name][$i]);
      		}
    	}
  	}

  	/**
     * Notifies all listeners of a given event.
     *
     * @param MOC_Event $event A MOC_Event instance
     *
     * @return MOC_Event The MOC_Event instance
     */
	public static function notify(MOC_Event $event) {
    	foreach (self::getListeners($event->getName()) as $listener) {
      		call_user_func($listener, $event);
    	}

    	return $event;
  	}

	/**
   	 * Notifies all listeners of a given event until one returns a non null value.
   	 *
   	 * @param  MOC_Event $event A MOC_Event instance
     *
     * @return MOC_Event The MOC_Event instance
     */
  	public static function notifyUntil(MOC_Event $event) {
    	foreach (self::getListeners($event->getName()) as $listener) {
      		if (call_user_func($listener, $event)) {
        		$event->setProcessed(true);
        		break;
      		}
    	}

    	return $event;
	}

	/**
     * Filters a value by calling all listeners of a given event.
     *
     * @param  MOC_Event  $event   A MOC_Event instance
     * @param  mixed    $value   The value to be filtered
     *
     * @return MOC_Event The MOC_Event instance
     */
  	public static function filter(MOC_Event $event, $value) {
    	foreach (self::getListeners($event->getName()) as $listener) {
      		$value = call_user_func_array($listener, array($event, $value));
    	}

    	$event->setReturnValue($value);

    	return $event;
  	}

  	/**
     * Returns true if the given event name has some listeners.
     *
     * @param  string   $name    The event name
     *
     * @return Boolean true if some listeners are connected, false otherwise
     */
  	public static function hasListeners($name) {
    	if (!isset(self::$listeners[$name])) {
      		self::$listeners[$name] = array();
    	}

    	return (boolean) count(self::$listeners[$name]);
  	}

  	/**
   	 * Returns all listeners associated with a given event name.
   	 *
   	 * @param  string   $name    The event name
   	 *
   	 * @return array  An array of listeners
   	 */
  	public static function getListeners($name) {
		if (!isset(self::$listeners[$name])) {
      		return array();
    	}

    	return self::$listeners[$name];
  	}
}