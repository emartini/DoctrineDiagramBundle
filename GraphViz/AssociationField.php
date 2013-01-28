<?php

namespace Warseph\Bundle\DoctrineDiagramBundle\GraphViz;

use Warseph\Bundle\DoctrineDiagramBundle\GraphViz\Field;
use Doctrine\ORM\Mapping\ClassMetadataInfo as CMI;

class AssociationField extends Field
{
    protected $other;
    protected $owningSide;
    protected $cardinality;

    public function getLabel()
    {
        $id = '';
        if ($this->identifier) {
            $id = '* ';
        }
        return sprintf('%s%s', $id, $this->name);
    }

    public function isOwningSide()
    {
        return $this->owningSide;
    }

    public function getOther()
    {
        return $this->other;
    }

    public function getCardinality()
    {
        return $this->cardinality;
    }

    protected function init()
    {
        if ($this->metadata->isIdentifier($this->name)) {
            $this->identifier = true;
        }
        $mapping = $this->metadata->getAssociationMapping($this->name);
        $this->type = $mapping['type'];
        $this->owningSide = $mapping['isOwningSide'];
        switch ($this->type)
        {
            case CMI::ONE_TO_MANY:
                $this->cardinality = 1;
                $this->other = array(
                    'entity' => $mapping['targetEntity'],
                    'field' => $mapping['mappedBy'],
                    'cardinality' => '*',
                );
                break;
            case CMI::MANY_TO_ONE:
                $this->cardinality = '*';
                $this->other = array(
                    'entity' => $mapping['targetEntity'],
                    'field' => $mapping['inversedBy'],
                    'cardinality' => '1',
                );
                break;
        }
    }

}
