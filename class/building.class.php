<?php
/* Copyright (C) 2013	Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file       place/class/building.class.php
 *  \ingroup    place
 *  \brief      This file is an example for a CRUD class file (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Building extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='building';			//!< Id that identify managed objects
	var $table_element='place_building';		//!< Name of table without prefix where object is stored

    var $id;

	var $entity;
	var $ref;
	var $label;
	var $fk_place;
	var $description;
	var $lat;
	var $lng;
	var $note_public;
	var $note_private;
	var $fk_user_creat;
	var $tms='';




    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
        return 1;
    }


    /**
     *  Create object into database
     *
     *  @param	User	$user        User that creates
     *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
     *  @return int      		   	 <0 if KO, Id of created object if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters

		if (isset($this->entity)) $this->entity=trim($this->entity);
		if (isset($this->ref)) $this->ref=trim($this->ref);
		if (isset($this->label)) $this->label=trim($this->label);
		if (isset($this->fk_place)) $this->fk_place=trim($this->fk_place);
		if (isset($this->description)) $this->description=trim($this->description);
		if (isset($this->lat)) $this->lat=trim($this->lat);
		if (isset($this->lng)) $this->lng=trim($this->lng);
		if (isset($this->note_public)) $this->note_public=trim($this->note_public);
		if (isset($this->note_private)) $this->note_private=trim($this->note_private);
		if (isset($this->fk_user_creat)) $this->fk_user_creat=trim($this->fk_user_creat);



		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."place_building(";

		$sql.= "entity,";
		$sql.= "ref,";
		$sql.= "label,";
		$sql.= "fk_place,";
		$sql.= "description,";
		$sql.= "lat,";
		$sql.= "lng,";
		$sql.= "note_public,";
		$sql.= "note_private,";
		$sql.= "fk_user_creat";


        $sql.= ") VALUES (";

		$sql.= " ".getEntity('building').",";
		$sql.= " ".(! isset($this->ref)?'NULL':"'".$this->db->escape($this->ref)."'").",";
		$sql.= " ".(! isset($this->label)?'NULL':"'".$this->db->escape($this->label)."'").",";
		$sql.= " ".(! isset($this->fk_place)?'NULL':"'".$this->fk_place."'").",";
		$sql.= " ".(! isset($this->description)?'NULL':"'".$this->db->escape($this->description)."'").",";
		$sql.= " ".(! isset($this->lat)?'NULL':"'".$this->lat."'").",";
		$sql.= " ".(! isset($this->lng)?'NULL':"'".$this->lng."'").",";
		$sql.= " ".(! isset($this->note_public)?'NULL':"'".$this->db->escape($this->note_public)."'").",";
		$sql.= " ".(! isset($this->note_private)?'NULL':"'".$this->db->escape($this->note_private)."'").",";
		$sql.= " ".$user->id."";


		$sql.= ")";

		$this->db->begin();
print $sql;
	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."place_building");

			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }


    /**
     *  Load object in memory from the database
     *
     *  @param	int		$id    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id)
    {
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";

		$sql.= " t.entity,";
		$sql.= " t.ref,";
		$sql.= " t.label,";
		$sql.= " t.fk_place,";
		$sql.= " t.description,";
		$sql.= " t.lat,";
		$sql.= " t.lng,";
		$sql.= " t.note_public,";
		$sql.= " t.note_private,";
		$sql.= " t.fk_user_creat,";
		$sql.= " t.tms";


        $sql.= " FROM ".MAIN_DB_PREFIX."place_building as t";
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;

				$this->entity = $obj->entity;
				$this->ref = $obj->ref;
				$this->label = $obj->label;
				$this->fk_place = $obj->fk_place;
				$this->description = $obj->description;
				$this->lat = $obj->lat;
				$this->lng = $obj->lng;
				$this->note_public = $obj->note_public;
				$this->note_private = $obj->note_private;
				$this->fk_user_creat = $obj->fk_user_creat;
				$this->tms = $this->db->jdate($obj->tms);

				// Retrieve place info
				$this->fetch_place();

            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     *    	Load the place of object from id $this->fk_place into this->place
     *
     *		@return		int					<0 if KO, >0 if OK
     */
    function fetch_place()
    {
    	global $conf;

    	if (empty($this->fk_place)) return 0;

    	$place = new Place($this->db);
    	$result=$place->fetch($this->fk_place);
    	$this->place = $place;

    	return $result;
    }


    /**
     *  Update object into database
     *
     *  @param	User	$user        User that modifies
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters

		if (isset($this->entity)) $this->entity=trim($this->entity);
		if (isset($this->ref)) $this->ref=trim($this->ref);
		if (isset($this->label)) $this->label=trim($this->label);
		if (isset($this->fk_place)) $this->fk_place=trim($this->fk_place);
		if (isset($this->description)) $this->description=trim($this->description);
		if (isset($this->lat)) $this->lat=trim($this->lat);
		if (isset($this->lng)) $this->lng=trim($this->lng);
		if (isset($this->note_public)) $this->note_public=trim($this->note_public);
		if (isset($this->note_private)) $this->note_private=trim($this->note_private);
		if (isset($this->fk_user_creat)) $this->fk_user_creat=trim($this->fk_user_creat);



		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."place_building SET";

		$sql.= " entity=".(isset($this->entity)?$this->entity:"null").",";
		$sql.= " ref=".(isset($this->ref)?"'".$this->db->escape($this->ref)."'":"null").",";
		$sql.= " label=".(isset($this->label)?"'".$this->db->escape($this->label)."'":"null").",";
		$sql.= " fk_place=".(isset($this->fk_place)?$this->fk_place:"null").",";
		$sql.= " description=".(isset($this->description)?"'".$this->db->escape($this->description)."'":"null").",";
		$sql.= " lat=".(isset($this->lat)?$this->lat:"null").",";
		$sql.= " lng=".(isset($this->lng)?$this->lng:"null").",";
		$sql.= " note_public=".(isset($this->note_public)?"'".$this->db->escape($this->note_public)."'":"null").",";
		$sql.= " note_private=".(isset($this->note_private)?"'".$this->db->escape($this->note_private)."'":"null").",";
		$sql.= " fk_user_creat=".(isset($this->fk_user_creat)?$this->fk_user_creat:"null").",";
		$sql.= " tms=".(dol_strlen($this->tms)!=0 ? "'".$this->db->idate($this->tms)."'" : 'null')."";


        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
	    	}
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
    }


 	/**
	 *  Delete object in database
	 *
     *	@param  User	$user        User that deletes
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
		        // want this action calls a trigger.

		        //// Call triggers
		        //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
		        //$interface=new Interfaces($this->db);
		        //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
		        //if ($result < 0) { $error++; $this->errors=$interface->errors; }
		        //// End call triggers
			}
		}

		if (! $error)
		{
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."place_building";
    		$sql.= " WHERE rowid=".$this->id;

    		dol_syslog(get_class($this)."::delete sql=".$sql);
    		$resql = $this->db->query($sql);
        	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}



	/**
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Placebuilding($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->statut=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		if (! $error)
		{


		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Initialise object with example values
	 *	Id must be 0 if object instance is a specimen
	 *
	 *	@return	void
	 */
	function initAsSpecimen()
	{
		$this->id=0;

		$this->entity='';
		$this->ref='';
		$this->label='';
		$this->fk_place='';
		$this->description='';
		$this->lat='';
		$this->lng='';
		$this->note_public='';
		$this->note_private='';
		$this->fk_user_creat='';
		$this->tms='';


	}

}
?>
