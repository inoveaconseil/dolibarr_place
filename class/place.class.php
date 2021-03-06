<?php
/* Copyright (C) 2013-2016	Jean-François Ferry	<jfefe@aternatik.fr>
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
 *  \file      	place/class/place.class.php
 *  \ingroup    place
 *  \brief      CRUD class file (Create/Read/Update/Delete) for place object
 *				Initialy built by build_class_from_table on 2013-07-24 16:03.
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/resource/class/dolresource.class.php';

/**
 *	DAO Place object.
 */
class Place extends Dolresource
{
    public $db;                            //!< To store db handler
    public $error;                            //!< To return error code (or message)
    public $errors = array();                //!< To return several error codes (or messages)
    public $element = 'place';            //!< Id that identify managed objects
    public $table_element = 'place';        //!< Name of table without prefix where object is stored

    public $id;

    public $ref;
    public $fk_soc;
    public $fk_socpeople;
    public $description;
    public $lat;
    public $lng;
    public $note_public;
    public $note_private;
    public $fk_user_creat;
    public $tms = '';

    /**
     *  Constructor.
     *
     *  @param	DoliDb		$db      Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        return 1;
    }

    /**
     *  Create object into database.
     *
     *  @param	User	$user        User that creates
     *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
     *
     *  @return int      		   	 <0 if KO, Id of created object if OK
     */
    public function create($user, $notrigger = 0)
    {
        global $conf, $langs;
        $error = 0;

        // Clean parameters

        if (isset($this->ref)) {
            $this->ref = trim($this->ref);
        }
        if (isset($this->fk_soc)) {
            $this->fk_soc = trim($this->fk_soc);
        }
        if (isset($this->fk_socpeople)) {
            $this->fk_socpeople = trim($this->fk_socpeople);
        }
        if (isset($this->description)) {
            $this->description = trim($this->description);
        }
        if (isset($this->lat)) {
            $this->lat = trim($this->lat);
        }
        if (isset($this->lng)) {
            $this->lng = trim($this->lng);
        }
        if (isset($this->note_public)) {
            $this->note_public = trim($this->note_public);
        }
        if (isset($this->note_private)) {
            $this->note_private = trim($this->note_private);
        }

        // Insert request
        $sql = 'INSERT INTO '.MAIN_DB_PREFIX.'place(';

        $sql .= 'ref,';
        $sql .= 'fk_soc,';
        $sql .= 'fk_socpeople,';
        $sql .= 'description,';
        $sql .= 'lat,';
        $sql .= 'lng,';
        $sql .= 'note_public,';
        $sql .= 'note_private,';
        $sql .= 'fk_user_creat,';
        $sql .= ' entity';

        $sql .= ') VALUES (';

        $sql .= ' '.(!isset($this->ref) ? 'NULL' : "'".$this->db->escape($this->ref)."'").',';
        $sql .= ' '.(empty($this->fk_soc) ? 'NULL' : $this->fk_soc).',';
        $sql .= ' '.(empty($this->fk_socpeople) ? '0' : $this->fk_socpeople).',';
        $sql .= ' '.(!isset($this->description) ? 'NULL' : "'".$this->db->escape($this->description)."'").',';
        $sql .= ' '.(empty($this->lat) ? 'NULL' : "'".$this->lat."'").',';
        $sql .= ' '.(empty($this->lng) ? 'NULL' : "'".$this->lng."'").',';
        $sql .= ' '.(!isset($this->note_public) ? 'NULL' : "'".$this->db->escape($this->note_public)."'").',';
        $sql .= ' '.(!isset($this->note_private) ? 'NULL' : "'".$this->db->escape($this->note_private)."'").',';
        $sql .= ' '.$user->id;
        $sql .= ', '.$conf->entity;

        $sql .= ')';

        $this->db->begin();

        dol_syslog(get_class($this).'::create sql='.$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (!$resql) {
            ++$error;
            $this->errors[] = 'Error '.$this->db->lasterror();
        }

        if (!$error) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.'place');

            if (!$notrigger) {
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
        if ($error) {
            foreach ($this->errors as $errmsg) {
                dol_syslog(get_class($this).'::create '.$errmsg, LOG_ERR);
                $this->error .= ($this->error ? ', '.$errmsg : $errmsg);
            }
            $this->db->rollback();

            return -1 * $error;
        } else {
            $this->db->commit();

            return $this->id;
        }
    }

    /**
     *  Load object in memory from the database.
     *
     *  @param	int		$id    Id object
     *
     *  @return int          	<0 if KO, >0 if OK
     */
    public function fetch($id)
    {
        global $langs;
        $sql = 'SELECT';
        $sql .= ' t.rowid,';
        $sql .= ' t.ref,';
        $sql .= ' t.fk_soc,';
        $sql .= ' t.fk_socpeople,';
        $sql .= ' t.description,';
        $sql .= ' t.lat,';
        $sql .= ' t.lng,';
        $sql .= ' t.note_public,';
        $sql .= ' t.note_private,';
        $sql .= ' t.fk_user_creat,';
        $sql .= ' t.tms';

        $sql .= ' FROM '.MAIN_DB_PREFIX.'place as t';
        $sql .= ' WHERE t.rowid = '.$id;

        dol_syslog(get_class($this).'::fetch sql='.$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);

                $this->id = $obj->rowid;

                $this->ref = $obj->ref;
                $this->fk_soc = $obj->fk_soc;
                $this->fk_socpeople = $obj->fk_socpeople;
                $this->description = $obj->description;
                $this->lat = $obj->lat;
                $this->lng = $obj->lng;
                $this->note_public = $obj->note_public;
                $this->note_private = $obj->note_private;
                $this->fk_user_creat = $obj->fk_user_creat;
                $this->tms = $this->db->jdate($obj->tms);
            }
            $this->db->free($resql);

            return 1;
        } else {
            $this->error = 'Error '.$this->db->lasterror();
            dol_syslog(get_class($this).'::fetch '.$this->error, LOG_ERR);

            return -1;
        }
    }

    /**
     *	Load all objects into $this->lines.
     *
     *  @param	string		$sortorder    sort order
     *  @param	string		$sortfield    sort field
     *  @param	int			$limit		  limit page
     *  @param	int			$offset    	  page
     *  @param	array		$filter    	  filter output
     *
     *  @return int          	<0 if KO, >0 if OK
     */
    public function fetch_all($sortorder, $sortfield, $limit, $offset, $filter = '')
    {
        global $conf;
        $sql = 'SELECT ';
        $sql .= ' t.rowid,';
        $sql .= ' t.ref,';
        $sql .= ' t.fk_soc,';
        $sql .= ' t.fk_socpeople,';
        $sql .= ' t.description,';
        $sql .= ' t.lat,';
        $sql .= ' t.lng,';
        $sql .= ' t.note_public,';
        $sql .= ' t.note_private,';
        $sql .= ' t.fk_user_creat,';
        $sql .= ' t.tms';
        $sql .= ' FROM '.MAIN_DB_PREFIX.'place as t ';
        $sql .= ' WHERE t.entity IN ('.getEntity('resource', true).')';

        //Manage filter
        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                if (strpos($key, 'date')) {
                    $sql .= ' AND '.$key.' = \''.$this->db->idate($value).'\'';
                } else {
                    $sql .= ' AND '.$key.' LIKE \'%'.$value.'%\'';
                }
            }
        }
        $sql .= ' GROUP BY t.rowid,t.ref';
        $sql .= " ORDER BY $sortfield $sortorder ".$this->db->plimit($limit + 1, $offset);
        dol_syslog(get_class($this).'::fetch_all sql='.$sql, LOG_DEBUG);

        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            if ($num) {
                $i = 0;
                while ($i < $num) {
                    $obj = $this->db->fetch_object($resql);
                    $line = new self($this->db);
                    $line->id = $obj->rowid;
                    $line->ref = $obj->ref;
                    $line->fk_soc = $obj->fk_soc;
                    $line->fk_socpeople = $obj->fk_socpeople;
                    $line->description = $obj->description;
                    $line->lat = $obj->lat;
                    $line->lng = $obj->lng;
                    $line->fk_user_create = $obj->fk_user_create;

                    $this->lines[$i] = $line;
                    ++$i;
                }
                $this->db->free($resql);
            }

            return $num;
        } else {
            $this->error = $this->db->lasterror();

            return -1;
        }
    }

    /**
     *  Update object into database.
     *
     *  @param	User	$user        User that modifies
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    public function update($user = 0, $notrigger = 0)
    {
        global $conf, $langs;
        $error = 0;

        // Clean parameters

        if (isset($this->ref)) {
            $this->ref = trim($this->ref);
        }
        if (isset($this->fk_soc)) {
            $this->fk_soc = trim($this->fk_soc);
        }
        if (isset($this->fk_socpeople)) {
            $this->fk_socpeople = trim($this->fk_socpeople);
        }
        if (isset($this->description)) {
            $this->description = trim($this->description);
        }
        if (isset($this->lat)) {
            $this->lat = trim($this->lat);
        }
        if (isset($this->lng)) {
            $this->lng = trim($this->lng);
        }
        if (isset($this->note_public)) {
            $this->note_public = trim($this->note_public);
        }
        if (isset($this->note_private)) {
            $this->note_private = trim($this->note_private);
        }
        if (isset($this->fk_user_creat)) {
            $this->fk_user_creat = trim($this->fk_user_creat);
        }

        // Check parameters
        // Put here code to add a control on parameters values

        // Update request
        $sql = 'UPDATE '.MAIN_DB_PREFIX.'place SET';

        $sql .= ' ref='.(isset($this->ref) ? "'".$this->db->escape($this->ref)."'" : 'null').',';
        $sql .= ' fk_soc='.(isset($this->fk_soc) ? $this->fk_soc : 'null').',';
        $sql .= ' fk_socpeople='.(isset($this->fk_socpeople) ? $this->fk_socpeople : 'null').',';
        $sql .= ' description='.(isset($this->description) ? "'".$this->db->escape($this->description)."'" : 'null').',';
        $sql .= ' lat='.(isset($this->lat) ? $this->lat : 'null').',';
        $sql .= ' lng='.(isset($this->lng) ? $this->lng : 'null').',';
        $sql .= ' note_public='.(isset($this->note_public) ? "'".$this->db->escape($this->note_public)."'" : 'null').',';
        $sql .= ' note_private='.(isset($this->note_private) ? "'".$this->db->escape($this->note_private)."'" : 'null').',';
        $sql .= ' fk_user_creat='.(isset($this->fk_user_creat) ? $this->fk_user_creat : 'null').',';
        $sql .= ' tms='.(dol_strlen($this->tms) != 0 ? "'".$this->db->idate($this->tms)."'" : 'null').'';

        $sql .= ' WHERE rowid='.$this->id;

        $this->db->begin();

        dol_syslog(get_class($this).'::update sql='.$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (!$resql) {
            ++$error;
            $this->errors[] = 'Error '.$this->db->lasterror();
        }

        if (!$error) {
            if (!$notrigger) {
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
        if ($error) {
            foreach ($this->errors as $errmsg) {
                dol_syslog(get_class($this).'::update '.$errmsg, LOG_ERR);
                $this->error .= ($this->error ? ', '.$errmsg : $errmsg);
            }
            $this->db->rollback();

            return -1 * $error;
        } else {
            $this->db->commit();

            return 1;
        }
    }

    /**
     *  Delete object in database.
     *
     *	@param  User	$user        User that deletes
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *
     *  @return	int					 <0 if KO, >0 if OK
     */
    public function delete($user, $notrigger = 0)
    {
        global $conf, $langs;
        $error = 0;

        $this->db->begin();

        if (!$error) {
            if (!$notrigger) {
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

        if (!$error) {
            $sql = 'DELETE FROM '.MAIN_DB_PREFIX.'place';
            $sql .= ' WHERE rowid='.$this->id;

            dol_syslog(get_class($this).'::delete sql='.$sql);
            $resql = $this->db->query($sql);
            if (!$resql) {
                ++$error;
                $this->errors[] = 'Error '.$this->db->lasterror();
            }
        }

        // Commit or rollback
        if ($error) {
            foreach ($this->errors as $errmsg) {
                dol_syslog(get_class($this).'::delete '.$errmsg, LOG_ERR);
                $this->error .= ($this->error ? ', '.$errmsg : $errmsg);
            }
            $this->db->rollback();

            return -1 * $error;
        } else {
            $this->db->commit();

            return 1;
        }
    }

    /**
     *	Return clicable link of object (with eventually picto).
     *
     *	@param      int		$withpicto		Add picto into link
     *	@param      string	$option			Where point the link ('compta', 'expedition', 'document', ...)
     *	@param      string	$get_params    	Parametres added to url
     *
     *	@return     string          		String with URL
     */
    public function getNomUrl($withpicto = 0, $option = '', $get_params = '')
    {
        global $langs;

        $result = '';

        switch ($option) {
            case 'building@place':
                $lien = '<a href="'.dol_buildpath('/place/building/fiche.php', 1).'?id='.$this->id.$get_params.'">';
                $picto = 'building@place';
                $label = $langs->trans('ShowBuilding').': '.$this->ref;
                break;
            case 'room@place':
                $lien = '<a href="'.dol_buildpath('/place/room/card.php', 1).'?id='.$this->id.$get_params.'">';
                $picto = 'room@place';
                $label = $langs->trans('ShowRoom').': '.$this->ref;
                break;
            default:
                $lien = '<a href="'.dol_buildpath('/place/fiche.php', 1).'?id='.$this->id.$get_params.'">';
                $picto = 'place@place';
                $label = $langs->trans('ShowPlace').': '.$this->ref;
                break;
        }

        $lienfin = '</a>';

        if ($withpicto) {
            $result .= ($lien.img_object($label, $picto).$lienfin);
        }
        if ($withpicto && $withpicto != 2) {
            $result .= ' ';
        }
        $result .= $lien.$this->ref.$lienfin;

        return $result;
    }

    /**
     *  Show html array with short informations of object.
     *
     *	@param  User	$user        User that deletes
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *
     *  @return	int					 <0 if KO, >0 if OK
     */
    public function printInfoTable()
    {
        global $langs;
        echo '<table width="100%" class="border">';

        // Ref / label
        echo '<tr>';
        echo '<td  width="20%">'.$langs->trans('NameOfThePlace').'</td>';
        echo '<td   width="30%">';
        echo $this->getNomUrl(1);
        echo '</td>';
        echo '</tr>';

        echo '</table>';
    }

    /**
     *  Load object place in $this->place from the database.
     *
     *  @param	int		$fk_place    Id place
     *
     *  @return int    	<0 if KO, >0 if OK
     */
    public function fetch_place($fk_place)
    {
        if (empty($fk_place)) {
            return 0;
        }

        $placestat = new self($this->db);
        if ($placestat->fetch($fk_place) > 0) {
            $this->place = $placestat;

            return 1;
        } else {
            return 0;
        }
    }

    /**
     *  Load object building in $this->building from the database.
     *
     *  @param	int		$fk_building    Id building
     *
     *  @return int     <0 if KO, >0 if OK
     */
    public function fetch_building($fk_building)
    {
        dol_include_once('place/class/building.class.php');

        if (empty($fk_building)) {
            return 0;
        }

        if (!class_exists('Building')) {
            dol_include_once('/place/class/building.class.php');
        }

        $buildingstat = new Building($this->db);
        if ($buildingstat->fetch($fk_building) > 0) {
            $this->building = $buildingstat;

            return 1;
        } else {
            return 0;
        }
    }

    /**
     *  Load object floor in $this->floor from the database.
     *
     *  @param	int		$fk_floor    Id floor
     *
     *  @return int     <0 if KO, >0 if OK
     */
    public function fetch_floor($fk_floor)
    {
        global $langs;

        if (empty($fk_floor)) {
            return 0;
        }

        $sql = 'SELECT';
        $sql .= ' t.rowid,';
        $sql .= ' t.ref,';
        $sql .= ' t.pos,';
        $sql .= ' t.fk_building,';
        $sql .= ' t.tms';
        $sql .= ' FROM '.MAIN_DB_PREFIX.'place_floor as t';
        $sql .= ' WHERE t.rowid = '.$fk_floor;

        dol_syslog(get_class($this).'::fetch sql='.$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);

                $this->floor = $obj;
            }
            $this->db->free($resql);

            return 1;
        } else {
            $this->error = 'Error '.$this->db->lasterror();
            dol_syslog(get_class($this).'::fetch '.$this->error, LOG_ERR);

            return -1;
        }
    }

    /**
     *	Load an object from its id and create a new one in database.
     *
     *	@param	int		$fromid     Id of object to clone
     *
     * 	@return	int					New id of clone
     */
    public function createFromClone($fromid)
    {
        global $user,$langs;

        $error = 0;

        $object = new self($this->db);

        $this->db->begin();

        // Load source object
        $object->fetch($fromid);
        $object->id = 0;
        $object->statut = 0;

        // Clear fields
        // ...

        // Create clone
        $result = $object->create($user);

        // Other options
        if ($result < 0) {
            $this->error = $object->error;
            ++$error;
        }

        if (!$error) {
        }

        // End
        if (!$error) {
            $this->db->commit();

            return $object->id;
        } else {
            $this->db->rollback();

            return -1;
        }
    }

    /**
     *	Initialise object with example values
     *	Id must be 0 if object instance is a specimen.
     */
    public function initAsSpecimen()
    {
        $this->id = 0;

        $this->ref = '';
        $this->fk_soc = '';
        $this->fk_socpeople = '';
        $this->description = '';
        $this->lat = '';
        $this->lng = '';
        $this->note_public = '';
        $this->note_private = '';
        $this->fk_user_creat = '';
        $this->tms = '';
    }
}
