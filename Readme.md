Installation
============

Add the following to composer.json

    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/warseph/DoctrineDiagramBundle"
        }
    ],
    "require": {
        "warseph/doctrine-diagram-bundle": "dev-master"

    }

Update your AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Warseph\Bundle\DoctrineDiagramBundle\WarsephDoctrineDiagramBundle(),
            // ...
        );
    }

Add routings (preferably to routing_dev.yml)

    _warseph_doctrine_diagram:
        resource: "@WarsephDoctrineDiagramBundle/Resources/config/routing.xml"
        prefix:   /warseph-diagram

Add entities to the graph (in config.yml)

    parameters:
        warseph_doctrine_diagram.generator.entities:
            - 'NamespaceNameBundle:EntityName'

Go to: /warseph-diagram/graph (if you used that prefix) to see the graph!