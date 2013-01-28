<?php

namespace Warseph\Bundle\DoctrineDiagramBundle\GraphViz;

use Warseph\Bundle\DoctrineDiagramBundle\GraphViz\Field;
use Warseph\Bundle\DoctrineDiagramBundle\GraphViz\AssociationField;

class Entity
{
    protected $metadata;
    protected $graph;
    protected $fields = array();
    protected $name;
    protected $associations = array();
    protected $subClasses = array();

    public function __construct($em, $class, $graph)
    {
        $this->metadata = $em->getMetadataFactory()->getMetadataFor($class);
        $this->graph = $graph;
        $this->init();
    }

    protected function init()
    {
        foreach ($this->metadata->subClasses as $subClass) {
            $discr = array_flip($this->metadata->discriminatorMap);
            $this->subClasses[] = array(
                'entity' => $subClass,
                'discr'  => $discr[$subClass],
            );
        }
        foreach ($this->metadata->getFieldNames() as $field)
        {
            $this->fields[$field] = new Field($field, $this->metadata);
        }
        foreach ($this->metadata->getAssociationMappings() as $field => $values)
        {
            $this->fields[$field] = new AssociationField($field, $this->metadata);
            $this->associations[$field] = $this->fields[$field];
        }
    }

    public function getName($name = null)
    {
        return "{$this->getBundle($name)}:{$this->getEntity($name)}";
    }

    public function getSafeName($name = null)
    {
        return str_replace(array('\\', ':'), '_', $this->getName($name));
    }

    protected function getBundle($name = null)
    {
        if (empty($name)) {
            $name = $this->metadata->getName();
        }
        list($bundle, $entity) = explode('\Entity\\', $name);
        $bundle = explode('\\', $bundle);
        if ($bundle[1] == 'Bundle') {
            unset($bundle[1]);
        }
        return join('', $bundle);
    }

    protected function getEntity($name = null)
    {
        if (empty($name)) {
            $name = $this->metadata->getName();
        }
        list($bundle, $entity) = explode('\Entity\\', $name);
        return $entity;
    }

    protected function getLabel()
    {
        $base = '<<TABLE BORDER="0" CELLBORDER="1" CELLSPACING="0" CELLPADDING="4">%s</TABLE>>';
        $row = '<TR><TD PORT="%s">%s</TD></TR>';
        $titleRow = '<TR><TD BGCOLOR="#666666"><FONT COLOR="#EEEEEE">%s</FONT></TD></TR>';
        $parts = array();
        $rows = array();

        $rows[] = sprintf($titleRow, $this->getName());
        foreach ($this->fields as $field) {
            $parts[$field->getName()] = $field->getLabel();
        }

        foreach ($parts as $key => &$part) {
            $rows[] = sprintf($row, $key, $part);
        }
        return sprintf($base, join('', $rows));
    }

    protected function getFieldAssociation($entity, $field)
    {
        if (empty($field)) {
            return $entity;
        } else {
            return sprintf('%s:%s', $entity, $field);
        }
    }

    protected function getAssociations()
    {
        $associations = array();
        foreach ($this->associations as $field) {
            if ($field->isOwningSide()) {
                $other = $field->getOther();

                $associations[] = array(
                    'fields' => array(
                        $this->getFieldAssociation($this->getSafeName(), $field->getName()),
                        $this->getFieldAssociation($this->getSafeName($other['entity']), $other['field']),
                    ),
                    'attributes' => array(
                        'headlabel' => "\"{$field->getCardinality()}\"",
                        'taillabel' => "\"{$other['cardinality']}\"",
                    ),
                );
            }
        }
        return $associations;
    }

    protected function getSubClasses()
    {
        $subClasses = array();
        foreach ($this->subClasses as $subClass) {
            $subClasses[] = array(
                'fields' => array(
                    $this->getSafeName($subClass['entity']),
                    $this->getSafeName(),
                ),
                'attributes' => array(
                    'arrowhead' => "\"empty\"",
                    'taillabel' => "\"{$subClass['discr']}\"",
                ),
            );
        }
        return $subClasses;
    }

    public function draw()
    {
        $this->graph->node($this->getSafeName(), array('label' => $this->getLabel()));
        foreach ($this->getAssociations() as $association) {
            $this->graph->edge($association['fields'], $association['attributes']);
        }
        foreach ($this->getSubClasses() as $subClass) {
            $this->graph->edge($subClass['fields'], $subClass['attributes']);
        }
    }
}