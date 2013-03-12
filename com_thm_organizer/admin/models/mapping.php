<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelMapping
 * @description THM_OrganizerModelMapping component admin model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Class THM_OrganizerModelMapping for component com_thm_organizer
 *
 * Class provides methods to deal with mapping
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelMapping extends JModelAdmin
{
	/**
	 * Method to get the associated semester
	 *
	 * @param   Integer  $assetID  Assest id
	 * @param   Integer  $majorID  Major id
	 *
	 * @return  Array
	 */
	public function getAssociatedSemester($assetID, $majorID)
	{
		$query = $this->_db->getQuery(true);
		$query->select("#__thm_organizer_assets_semesters.semesters_majors_id");
		$query->from('#__thm_organizer_assets_semesters');
		$query->innerJoin('#__thm_organizer_semesters_majors ON #__thm_organizer_semesters_majors.id = #__thm_organizer_assets_semesters.semesters_majors_id');
		$query->where("#__thm_organizer_assets_semesters.assets_tree_id = $assetID");
		$query->where("#__thm_organizer_semesters_majors.major_id= $majorID");
		$this->_db->setQuery($query);
		$rows = $this->_db->loadResultArray();

		return $rows;
	}

	/**
	 * Method to copy
	 *
	 * @param   Array    $cid        An array of ids
	 * @param   Integer  $parent_id  Parent id
	 *
	 * @return  void
	 */
	public function copy($cid, $parent_id)
	{
		if (count($cid) > 1)
		{
			foreach ($cid as $assetTreeID)
			{
				$record = self::getAssetRecord($assetTreeID);
			}

		}
		else
		{
			$record = self::getAssetRecord($cid[0]);
			$majorID = $_SESSION['stud_id'];

			$associatedSemester = self::getAssociatedSemester($cid[0], $majorID);
			JRequest::setVar('semesters', $associatedSemester);
			JRequest::setVar("id", 0);

			$arr['asset'] = $record[0]->asset;
			$arr['color_id'] = $record[0]->color_id;
			$arr['parent_id'] = $parent_id;
			$arr['ecollaboration_link'] = $record[0]->ecollaboration_link;
			$arr['menu_link'] = $record[0]->menu_link;

			self::save($arr);
		}
	}

	/**
	 * Method to adjust parent assets
	 *
	 * @param   Integer  $assetId    Asset id
	 * @param   Array    $semesters  Semesters
	 *
	 * @return  void
	 */
	public function adjustParentAssets($assetId, $semesters)
	{
		if ($assetId == 0)
		{
			return;
		}

		$majorID = $_SESSION['stud_id'];

		$query = $this->_db->getQuery(true);
		$query->select("*, at.id as asset_tree_id");
		$query->from('#__thm_organizer_assets_tree AS at');
		$query->innerJoin('#__thm_organizer_assets_semesters AS asem ON at.id = asem.assets_tree_id');
		$query->innerJoin('#__thm_organizer_semesters_majors AS sm ON sm.id = asem.semesters_majors_id');
		$query->where("at.parent_id = $assetId");
		$query->where("sm.major_id= $majorID");
		$this->_db->setQuery($query);
		$children = $this->_db->loadAssocList();

		if (count($children) > 0)
		{
			foreach ($children as $row)
			{
				$asset_id = $row['asset_tree_id'];
				$semesters_majors_id = $row['semesters_majors_id'];

				if (!in_array($row->semesters_majors_id, $semesters))
				{
					// Build the query
					$query = $this->_db->getQuery(true);
					$query->delete("#__thm_organizer_assets_semesters");
					$query->where("assets_tree_id = $asset_id");
					$query->where("semesters_majors_id = $semesters_majors_id");
					$this->_db->setQuery($query);
					$this->_db->query($query);
				}

				foreach ($semesters as $semester)
				{

					// Maps the actual asset to a additional semester
					$query = $this->_db->getQuery(true);
					$query->insert('#__thm_organizer_assets_semesters');
					$query->set("assets_tree_id = $asset_id");
					$query->set("semesters_majors_id = $semester");
					$this->_db->setQuery($query);
					$this->_db->query();
				}

				self::adjustParentAssets($row['asset'], $semesters);
			}
		}
	}

	/**
	 * Method to get the max ordering
	 *
	 * @param   Integer  $parent  Parent id
	 * @param   Integer  $major   Major id
	 *
	 * @return  Object
	 */
	public function getMaxOrdering($parent, $major)
	{
		$query = $this->_db->getQuery(true);
		$query->select("MAX(ordering) as max_ordering");
		$query->from('#__thm_organizer_assets_tree AS at');
		$query->innerJoin('#__thm_organizer_assets_semesters AS asem ON at.id = asem.assets_tree_id');
		$query->innerJoin('#__thm_organizer_semesters_majors AS sm ON sm.id = asem.semesters_majors_id');
		$query->where("at.parent_id = $parent");
		$query->where("sm.major_id= $major");
		$this->_db->setQuery($query);
		$rows = $this->_db->loadAssocList();

		return $rows[0]['max_ordering'];
	}

	/**
	 * Method to overwrite the save method
	 *
	 * @param   Array  $data  Data
	 *
	 * @return  Boolean
	 */
	public function save($data)
	{
		$dbo = JFactory::getDbo();
		$stud_id = $_SESSION['stud_id'];

		if ($data['parent_id'] == 0)
		{
			self::adjustParentAssets($data['asset'], JRequest::getVar('semesters'));
		}

		$ordering = self::getMaxOrdering($data['parent_id'], $stud_id) + 1;

		// Save the POST data to the mapping table
		if (!isset($data['ecollaboration_link_flag']))
		{
			$ecollabLinkFlag = 0;
		}
		else
		{
			$ecollabLinkFlag = 1;
		}

		if (!isset($data['menu_link_flag']))
		{
			$menu_link_flag = 0;
		}
		else
		{
			$menu_link_flag = 1;
		}

		if (!isset($data['color_id_flag']))
		{
			$color_id_flag = 0;
		}
		else
		{
			$color_id_flag = 1;
		}

		if (!isset($data['note_flag']))
		{
			$note_flag = 0;
		}
		else
		{
			$note_flag = 1;
		}

		if (JRequest::getVar('id'))
		{
			$asset = $data['asset'];
			$parent_id = $data['parent_id'];
			$color = $data['color_id'];
			$ecollab = $data['ecollaboration_link'];
			$menu = $data['menu_link'];
			$assetTreeID = JRequest::getVar('id');
			$note = $data['note'];

			// Wir erstellen einen neuen Query
			$sql = $this->_db->getQuery(true);
			$sql->update("#__thm_organizer_assets_tree");

			if ($color != "")
			{
				$sql->set('color_id=' . $color);
			}

			if ($asset != 0)
			{
				$sql->set("asset= $asset");
			}

			$sql->set("parent_id= $parent_id");

			if ($ecollab != "")
			{
				$sql->set("ecollaboration_link= '$ecollab'");
			}

			if ($menu != null)
			{
				$sql->set("menu_link= '$menu'");
			}

			if ($note != "")
			{
				$sql->set("note= 'note'");
			}

			$sql->set("ecollaboration_link_flag= $ecollabLinkFlag ");
			$sql->set("menu_link_flag= $menu_link_flag ");
			$sql->set("color_id_flag= $color_id_flag ");
			$sql->set("note_flag= $note_flag ");
			$sql->where("id= $assetTreeID");
			$this->_db->setQuery((string) $sql);
			$this->_db->query();

			echo (string) $sql;
		}
		else
		{
			$asset = $data['asset'];
			$parent_id = $data['parent_id'];
			$color = $data['color_id'];
			$ecollap = $data['ecollaboration_link'];
			$menu = $data['menu_link'];
			$note = $data['note'];

			// Wir erstellen einen neuen Query
			$sql = $this->_db->getQuery(true);
			$sql->insert("#__thm_organizer_assets_tree");
			$sql->set('color_id=' . $color);
			$sql->set("asset= $asset");
			$sql->set("parent_id= $parent_id");
			$sql->set("ordering= $ordering");
			$sql->set("ecollaboration_link= '$ecollap'");
			$sql->set("ecollaboration_link_flag= $ecollabLinkFlag ");
			$sql->set("menu_link= '$menu'");
			$sql->set("menu_link_flag= $menu_link_flag ");
			$sql->set("color_id_flag= $color_id_flag ");
			$sql->set("note_flag= $note_flag ");
			$sql->set("note= '$note' ");
			$this->_db->setQuery((string) $sql);
			$this->_db->query();

			echo (string) $sql;
		}

		// Get the last inserted id from the previous stored row
		$insertid = $this->_db->insertid();

		// Get the post data
		$semesters = JRequest::getVar('semesters');

		if ($semesters == null)
		{

		}
		else
		{
			// Edit of an existent row
			if (JRequest::getVar('id'))
			{
				// Get the current id of the edited asset
				$insertid = JRequest::getVar('id');

				// Determine all mapped semesters of this asset
				$query = $this->_db->getQuery(true);
				$query->select("*");
				$query->from("#__thm_organizer_assets_semesters");
				$query->where("assets_tree_id = $insertid");
				$this->_db->setQuery($query);
				$rows = $this->_db->loadObjectList();

				// Iterate over each found mapping
				foreach ($rows as $row)
				{
					// Delete the mapping if the current mapping isn't part of the post data
					if (!in_array($row->stud_sem_id, $semesters))
					{
						// Build the query
						$query = $this->_db->getQuery(true);
						$query->delete("#__thm_organizer_assets_semesters");
						$query->where("assets_tree_id = $insertid");
						$query->where("semesters_majors_id = $row->semesters_majors_id");
						$this->_db->setQuery($query);
						$this->_db->query($query);
					}
				}
			}
			else
			{
				$dbo = JFactory::getDbo();
				$insertid = $this->_db->insertid();
			}

			// Iterate over each semester of the post request
			foreach ($semesters as $semester)
			{
				// Maps the actual asset to a additional semester
				$query = $this->_db->getQuery(true);
				$query->insert('#__thm_organizer_assets_semesters');
				$query->set("assets_tree_id = $insertid");
				$query->set("semesters_majors_id = $semester");
				$this->_db->setQuery($query);
				$this->_db->query();

				echo (string) $query;
			}
		}

		return true;
	}

	/**
	 * Method to get the asset record
	 *
	 * @param   Integer  $assetTreeID  Id
	 *
	 * @return  Object
	 */
	public function getAssetRecord($assetTreeID)
	{
		// Determine all mapped semesters of this asset
		$query = $this->_db->getQuery(true);
		$query->select("*");
		$query->from("#__thm_organizer_assets_tree");
		$query->where("id = $assetTreeID");
		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();

		return $rows;
	}

	/**
	 * Method to determine the path of the given tree node
	 *
	 * @param   Integer  $node  Node
	 *
	 * @return  Array
	 */
	public function get_path($node)
	{
		$query = $this->_db->getQuery(true);

		// Get the selected major id
		$stud_sem_id = $_SESSION['stud_id'];

		// Determine all node by a given asset id
		$query->select("*");
		$query->from("#__thm_organizer_assets_tree AS at");
		$query->innerJoin('#__thm_organizer_assets_semesters AS asem ON asem.assets_tree_id = at.id');
		$query->innerJoin('#__thm_organizer_semesters_majors AS sm ON sm.id = asem.semesters_majors_id');
		$query->where("asset = $node");
		$query->where("major_id = $stud_sem_id");
		$this->_db->setQuery($query);
		$row = $this->_db->loadAssocList();

		// This array will contain the actual path
		$path = array();

		// Builds the current paths and return it
		if ($row[0]['parent_id'] != null)
		{
			$path[] = $row[0]['parent_id'] . "/";

			// Find all parent nodes recursively
			$path = array_merge(self::get_path($row[0]['parent_id']), $path);
		}
		else
		{
			$path[] = "/";
		}

		return $path;
	}

	/**
	 * Method to set lineage
	 *
	 * @return  void
	 */
	public function setLineage()
	{
		// Get the current major id
		$stud_sem_id = $_SESSION['stud_id'];

		// Select the tree of the current major
		$query = $this->_db->getQuery(true);

		$query->select("*");
		$query->from("#__thm_organizer_assets_tree AS at");
		$query->innerJoin('#__thm_organizer_assets_semesters AS asem ON asem.assets_tree_id = at.id');
		$query->innerJoin('#__thm_organizer_semesters_majors AS sm ON sm.id = asem.semesters_majors_id');
		$query->where("asset <> 0");
		$query->where("major_id = $stud_sem_id");
		$query->group(" asset");

		$this->_db->setQuery($query);
		$rows = $this->_db->loadObjectList();

		// Iterate over each node of the tree
		foreach ($rows as $row)
		{
			// Determine the path and depth level of the current node
			$depth = count(self::get_path($row->asset)) - 1;
			$path = implode(self::get_path($row->asset));

			// Write it to the database
			$query = $this->_db->getQuery(true);
			$query->update($this->_db->qn('#__thm_organizer_assets_tree'));
			$query->join("#__thm_organizer_assets_semesters ON #__thm_organizer_assets_semesters.assets_tree_id = #__thm_organizer_assets_tree.id");
			$query->join("#__thm_organizer_semesters_majors ON #__thm_organizer_semesters_majors.id = #__thm_organizer_assets_semesters.semesters_majors_id");
			$query->set("lineage = '$path'");
			$query->set("depth = $depth");
			$query->where("asset = $row->asset");
			$query->where("major_id = $stud_sem_id");
			
			$this->_db->setQuery($query);
			$this->_db->query();
		}
	}

	/**
	 * Method to get the table
	 *
	 * @param   String  $type    Type  			(default: 'mapping')
	 * @param   String  $prefix  Prefix  		(default: 'THM_OrganizerTable')
	 * @param   Array   $config  Configuration  (default: 'Array')
	 *
	 * @return  JTable object
	 */
	public function getTable($type = 'mapping', $prefix = 'THM_OrganizerTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the form
	 *
	 * @param   Array    $data      Data  	   (default: Array)
	 * @param   Boolean  $loadData  Load data  (default: true)
	 *
	 * @return  A Form object
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_thm_organizer.mapping', 'mapping', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	/**
	 * Method to load the form data
	 *
	 * @return  Object
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_thm_organizer.edit.mapping.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;
	}

	/**
	 * Method to overwrite publish method. Publish a given tree node
	 *
	 * @return  Boolean
	 */
	public function publish()
	{
		// Get the post data
		$cid = JRequest::getVar('cid', array(), '', 'array');
		
		// Iterate over each tree node, if multiple node were selected
		foreach ($cid as $assetTreeID)
		{
			// Create the update sql statement
			$query = $this->_db->getQuery(true);
			$query->update($this->_db->qn('#__thm_organizer_assets_tree'));
			$query->set("published` = '1'");
			$query->where("id = $assetTreeID");
			$this->_db->setQuery($query . $assetTreeID);
			if (!$this->_db->query())
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Method to overwrite unpublish method. Unpublishs a given tree node
	 *
	 * @return  Boolean
	 */
	public function unpublish()
	{
		// Get the post data
		$cid = JRequest::getVar('cid', array(), '', 'array');

		// Iterate over each tree node, if multiple node were selected
		foreach ($cid as $assetTreeID)
		{
			// Create the update sql statement
			$query = $this->_db->getQuery(true);
			$query->update($this->_db->qn('#__thm_organizer_assets_tree'));
			$query->set("published` = '0'");
			$query->where("id = $assetTreeID");
			$this->_db->setQuery($query . $assetTreeID);
			if (!$this->_db->query())
			{
				return false;
			}
		}
		return true;
	}
}
