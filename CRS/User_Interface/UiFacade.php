<?php
require_once(realpath(dirname(__FILE__)) . '/../../CRS/AppLogic/AlFacade.php');
require_once(realpath(dirname(__FILE__)) . '/../../CRS/Database/DbFacade.php');

/**
 * @access public
 * @author Nora
 * @package CRS.User_Interface
 */
class UiFacade {
	/**
	 * @AssociationType CRS.AppLogic.AlFacade
	 */
	public $_anAlFacade;
	/**
	 * @AssociationType CRS.Database.DbFacade
	 */
	public $_aDbFacade;
}
?>