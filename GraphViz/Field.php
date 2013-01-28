<?php

namespace Warseph\Bundle\DoctrineDiagramBundle\GraphViz;

class Field
{
    protected $metadata;
    protected $name;
    protected $identifier = false;
    protected $type;
    protected $length;

    public function __construct($field, $metadata)
    {
        $this->metadata = $metadata;
        $this->name = $field;
        $this->init();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLabel()
    {
        $id = '';
        $length = '';
        if ($this->identifier) {
            $id = '* ';
        }
        if ($this->length > 0) {
            $length = sprintf(' (%d)', $this->length);
        }
        $label = sprintf('%s%s : %s', $id, $this->name, $this->type, $length);
        return $label;
    }

    protected function init()
    {
        if ($this->metadata->isIdentifier($this->name)) {
            $this->identifier = true;
        }
        $mapping = $this->metadata->getFieldMapping($this->name);
        $this->type = $mapping['type'];
        if (!empty($mapping['length'])) {
            $this->length = $mapping['length'];
        }
    }

}
