<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Address;

use Contact;
use Splash\Core\SplashCore      as Splash;
use User;

/**
 * Dolibarr Contacts Address CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return Contact|false
     */
    public function load($objectId)
    {
        global $db;
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Init Object
        $object = new Contact($db);
        //====================================================================//
        // Fatch Object
        if (1 != $object->fetch((int) $objectId)) {
            $this->catchDolibarrErrors($object);

            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to load Contact Address (" . $objectId . ")."
            );
        }
        //====================================================================//
        // Check Object Entity Access (MultiCompany)
        if (!self::isMultiCompanyAllowed($object)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to load Contact Address (" . $objectId . ")."
            );
        }

        return $object;
    }

    /**
     * Create Request Object
     *
     * @return Contact|false
     */
    public function create()
    {
        global $db, $user;
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Check Customer Name is given
        if (empty($this->in["firstname"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "firstname");
        }
        //====================================================================//
        // LOAD USER FROM DATABASE
        if (!($user instanceof User) || empty($user->login)) {
            return Splash::log()->err("ErrLocalUserMissing", __CLASS__, __FUNCTION__);
        }
        //====================================================================//
        // Init Object
        $this->object = new \Contact($db);
        //====================================================================//
        // Pre-Setup of Dolibarr infos
        $this->setSimple("firstname", $this->in["firstname"]);
        //====================================================================//
        // Create Object In Database
        if ($this->object->create($user) <= 0) {
            $this->catchDolibarrErrors();

            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                "Unable to create new Contact Address. "
            );
        }
        
        return $this->object;
    }
    
    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return false|string Object Id
     */
    public function update($needed)
    {
        global $user;
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        if (!$needed) {
            return $this->getObjectIdentifier();
        }
        //====================================================================//
        // LOAD USER FROM DATABASE
        if (!($user instanceof User) || empty($user->login)) {
            return Splash::log()->err("ErrLocalUserMissing", __CLASS__, __FUNCTION__);
        }
        //====================================================================//
        // Update Object
        if ($this->object->update($this->object->id, $user) <= 0) {
            $this->catchDolibarrErrors();

            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to Update Contact Address (" . $this->object->id . ")"
            ) ;
        }
        //====================================================================//
        // Update Object Extra Fields
        if ($this->object->insertExtraFields()  <= 0) {
            $this->catchDolibarrErrors();
        }

        return $this->getObjectIdentifier();
    }
    
    /**
     * Delete requested Object
     *
     * @param string $objectId Object Id.  If NULL, Object needs to be created.
     *
     * @return bool
     */
    public function delete($objectId = null)
    {
        global $db,$user;
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Load Object
        $object = new Contact($db);
        //====================================================================//
        // LOAD USER FROM DATABASE
        if (empty($user->login)) {
            return Splash::log()->err("ErrLocalUserMissing", __CLASS__, __FUNCTION__);
        }
        //====================================================================//
        // Set Object Id, fetch not needed
        $object->id = (int) $objectId;
        //====================================================================//
        // Check Object Entity Access (MultiCompany)
        $object->entity = 0;
        if (!self::isMultiCompanyAllowed($object)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to delete Product (" . $objectId . ")."
            );
        }
        //====================================================================//
        // Delete Object
        if ($object->delete() <= 0) {
            return $this->catchDolibarrErrors($object);
        }

        return true;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier()
    {
        if (!isset($this->object->id)) {
            return false;
        }

        return (string) $this->object->id;
    }
}
