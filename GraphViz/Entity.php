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

    public function __construct($em, $class, $graph)
    {
        $this->metadata = $em->getMetadataFactory()->getMetadataFor($class);
        $this->graph = $graph;
        $this->init();
    }

    protected function init()
    {
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

    protected function getAssociations()
    {
        $associations = array();
        foreach ($this->associations as $field) {
            if ($field->isOwningSide()) {
                $other = $field->getOther();
                $associations[] = array(
                    'fields' => array(
                        sprintf('%s:%s', $this->getSafeName(), $field->getName()),
                        sprintf('%s:%s', $this->getSafeName($other['entity']), $other['field']),
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

    public function draw()
    {
        $this->graph->node($this->getSafeName(), array('label' => $this->getLabel()));
        foreach ($this->getAssociations() as $association) {
            $this->graph->edge($association['fields'], $association['attributes']);
        }
    }
}