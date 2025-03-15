<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

use integration_tests\fixture\TreenodeConfigurationsFixture;
use pvc\interfaces\struct\collection\factory\CollectionFactoryInterface;
use pvc\interfaces\struct\tree\factory\NodeTypeFactoryInterface;
use pvc\interfaces\struct\tree\factory\TreenodeFactoryInterface;
use pvc\interfaces\struct\tree\factory\TreenodeValueObjectFactoryInterface;
use pvc\interfaces\struct\tree\search\SearchFilterInterface;
use pvc\interfaces\struct\tree\search\SearchStrategyInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;
use pvc\interfaces\validator\ValidatorInterface;
use pvc\struct\collection\factory\CollectionOrderedFactory;
use pvc\struct\tree\dto\factory\TreenodeValueObjectOrderedFactory;
use pvc\struct\tree\node\factory\TreenodeOrderedFactory;
use pvc\struct\tree\node\TreenodeFactory;
use pvc\struct\tree\node\TreenodeValueValidatorDefault;
use pvc\struct\tree\tree\TreeOrdered;
use pvc\struct\tree\treesearch\SearchFilterDefault;
use pvc\struct\tree\treesearch\SearchStrategyDepthFirst;
use function DI\create;
use function DI\get;

return [

    /**
     * stuff to create a tree node factory
     */
    CollectionFactoryInterface::class => create(CollectionOrderedFactory::class),
    NodeTypeFactoryInterface::class => create(TreenodeOrderedFactory::class),
    ValidatorInterface::class => create(TreenodeValueValidatorDefault::class),
    TreenodeFactoryInterface::class => create(TreenodeFactory::class)
        ->constructor(
            get(NodeTypeFactoryInterface::class),
            get(CollectionFactoryInterface::class),
            get(ValidatorInterface::class)
        ),

    /**
     * now create a tree
     */
    'treeId' => 0,
    TreeOrderedInterface::class => create(TreeOrdered::class)
        ->constructor(get('treeId'), get(TreenodeFactoryInterface::class)),

    /**
     * fixture has data suitable for constructing a tree
     */
    TreenodeValueObjectFactoryInterface::class => create(TreenodeValueObjectOrderedFactory::class),

    'fixture' => create(TreenodeConfigurationsFixture::class)
        ->constructor(get(TreenodeValueObjectFactoryInterface::class)),

    /**
     * once the tree is created and hydrated, we will search it using a search search object
     */
    SearchFilterInterface::class => create(SearchFilterDefault::class),

    SearchStrategyInterface::class => create(SearchStrategyDepthFirst::class)
        ->constructor(get(SearchFilterInterface::class)),
];
