<?php

namespace Warseph\Bundle\DoctrineDiagramBundle\GraphViz;

use Alom\Graphviz\Digraph;

class Generator
{
    protected $tmpFiles = array();
    protected $cmd = 'dot';
    protected $format = 'svg';
    protected $entities = array();
    protected $em;

    public function __construct($em, $entities)
    {
        $this->em = $em;
        $this->entities = $entities;
    }

    public function setCommand($cmd)
    {
        $this->cmd = $cmd;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    protected function run($dot)
    {
        $dotFile = $this->getTmpFile();
        $graphFile = $this->getTmpFile();
        file_put_contents($dotFile, $dot);
        `$this->cmd -T $this->format -o $graphFile $dotFile`;
        $graph = file_get_contents($graphFile);

        $this->cleanup();
        return $graph;
    }

    public function generateDot()
    {
        $graph = new Digraph('G');
        $graph
            ->attr('graph', array('rankdir' => 'LR'))
            ->attr('node', array('shape' => 'plaintext'))
            ->attr('edge', array('arrowhead' => 'none'))
        ;

        foreach ($this->entities as $entity) {
            $entity = new Entity($this->em, $entity, $graph);
            $entity->draw();
        }
        return $graph->render();
    }

    public function generate()
    {
        $dot = $this->generateDot();
        return $this->run($dot);
    }

    protected function getTmpFile()
    {
        $tmpFile = tempnam("/tmp", "WDD");
        $this->tmpFiles[] = $tmpFile;
        return $tmpFile;
    }

    protected function cleanup()
    {
        foreach ($this->tmpFiles as $file) {
            unlink($file);
        }
    }
}