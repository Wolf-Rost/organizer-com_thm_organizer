<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<script type="text/javascript" charset="utf-8">
<?php
	echo 'var externLinks = [];';
	echo 'externLinks.eventLink = \''.JRoute::_('index.php?option=com_thm_organizer&view=event_edit&schedulerCall=true&eventID=').'\';';
	echo 'externLinks.blankImageLink = \''.JURI::root(true).'/components/com_thm_organizer/views/scheduler/tmpl/ext_bak/resources/images/default/s.gif\';';
	echo 'externLinks.lectureAddButton = \''.JURI::root(true).'/components/com_thm_organizer/views/scheduler/tmpl/images/add.png\';';
	echo 'externLinks.lectureRemoveButton = \''.JURI::root(true).'/components/com_thm_organizer/views/scheduler/tmpl/images/delete.png\';';
	echo 'externLinks.mainPath = \''.JURI::root(true).'/components/com_thm_organizer/views/scheduler/tmpl/\';';
	echo 'externLinks.lsfLink = \''.JRoute::_('index.php?option=com_thm_lsf&view=details&layout=default&tmpl=component&mysched=true').'\';';
?>
</script>

<div id="MySchedMainW" class="MySchedMainW">
	<!--<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/preLoadingMessage.js"></script>-->

	<!-- Ext 4 framework -->
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/ext/ext-all-dev.js"></script>

	<!-- Ext 4 bootstrap -->
	<!--<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/ext/bootstrap.js"></script>-->

	<!-- Ext 3 Compatibility (remove after migration is complete) -->
	<!--<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/ext/compatibility/ext3-core-compat.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/ext/compatibility/ext3-compat.js"></script>-->

	<!--<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/ext/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/ext/ext-all.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/coreextension.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/MultiSelect.js"></script>-->
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/coreextension.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/libs.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/authorize.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/mapping.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/models.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/readers.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/grid.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/main.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/plugins.js"></script>
    <script type="text/javascript" charset="utf-8">

    <?php

    	if($this->canWriteEvents === true)
		{
			require_once("components/com_thm_organizer/views/scheduler/tmpl/mySched/advancedFunctions.js");
		}

    ?>

	<?php 	echo 'MySched.SessionId = \''.$this->jsid.'\';';
			echo 'MySched.class_semester_id = \''.$this->semesterID.'\';';
			echo 'MySched.class_semester_author = \''.$this->semAuthor.'\';';
			echo 'MySched.startup = \''.$this->startup.'\';';
	?>
		Ext.onReady(MySched.Base.init, MySched.Base);
	</script>
	</div>
	<iframe
		id="MySchedexternURL"
		name="MySchedexternURL"
		src="#"
		scrolling="auto"
		align="top"
		frameborder="0"
		class="MySchedexternURLClass_DIS">
	</iframe>
