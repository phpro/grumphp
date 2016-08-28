<?php

namespace spec\GrumPHP\Collection;

use PhpParser\NodeVisitor;
use PhpSpec\ObjectBehavior;

class NodeVisitorsCollectionSpec extends ObjectBehavior
{
    public function let(NodeVisitor $node1, NodeVisitor $node2)
    {
        $this->beConstructedWith(array($node1, $node2));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('GrumPHP\Collection\NodeVisitorsCollection');
    }

    function it_is_an_array_collection()
    {
        $this->shouldHaveType('Doctrine\Common\Collections\ArrayCollection');
    }
}
