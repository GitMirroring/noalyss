<?php
namespace Noalyss\XMLDocument;

/*
 *   This file is part of NOALYSS.
 *
 *   NOALYSS is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   NOALYSS is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with NOALYSS; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
// Copyright Author Dany De Bontridder danydb@aevalys.eu 22/10/23


/**
 * @file
 * @brief Document Reference Type ; documents included in a UBL21 
 * xml
 */
class Binary_Object
{
    public $filecontent;
    public $mimecode;
    public $filename;
    function __toString(): string
    {
        return "Binary_Object[filecontent=" . $this->filecontent
                . ", mimecode=" . $this->mimecode
                . ", filename=" . $this->filename
                . "]";
    }
}

class Document_Reference
{
    protected $id;
    protected $description;
    protected Binary_Object $binary_object;
    function __toString(): string
    {
        return "Document_Reference[id=" . $this->id
                . ", description=" . $this->description
                . ", binary_object=" . $this->binary_object
                . "]";
    }
    public function getId()
    {
        return $this->id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getBinary_object(): Binary_Object
    {
        return $this->binary_object;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setBinary_object(Binary_Object $binary_object)
    {
        $this->binary_object = $binary_object;
        return $this;
    }


    
}