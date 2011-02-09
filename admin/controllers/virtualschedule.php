<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');

class GiessenSchedulersControllervirtualschedule extends JController
{
	/**
 	 * constructor (registers additional tasks to methods)
 	 * @return void
 	 */
	function __construct() {
		parent::__construct();
		$this->registerTask('add', 'edit');
		$this->registerTask('deleteList', '');
	}

	/**
  	 * display the edit form
 	 * @return void
 	 */
	function edit(){
    	JRequest::setVar( 'view', 'virtual_schedule_edit' );
    	JRequest::setVar( 'hidemainmenu', 1);
    	parent::display();

	}

	function remove(){
		global $mainframe;
		$dbo = & JFactory::getDBO();
    	$cid = JRequest::getVar( 'cid',   array(), 'post', 'array' );
    	$cids = implode( ',', $cid );
    	$cids_temp = $cids;
		$cids_temp = str_replace(',', ', ', $cids_temp);
		$cids = str_replace(',', '","', $cids);
		$cids = '"'.$cids.'"';

		$query = 'DELETE FROM #__thm_organizer_virtual_schedules'
		         . ' WHERE vid IN ( '. $cids .' );';

		$dbo->setQuery( $query );
        $dbo->query();

        if ($dbo->getErrorNum())
		{
			$msg =   JText::_( 'Fehler beim L�schen.' );
		}
		else
		{
			$query = 'DELETE FROM #__thm_organizer_virtual_schedules_elements'
		         . ' WHERE vid IN ( '. $cids .' );';

			$dbo->setQuery( $query );
	        $dbo->query();
		}

		if(count($cid) > 1)
       		$msg =   JText::_( 'Virtuelle Stundenpl�ne '.$cids_temp.' gel�scht.' );
       	else
        	$msg =   JText::_( 'Virtuellen Stundenplan '.$cids_temp.' gel�scht.' );

        $this->setRedirect( 'index.php?option=com_thm_organizer&view=virtual_schedule_manager',$msg );

	}
}
?>
