<?php

namespace HappyR\UserProjectBundle\Tests\Factory;

use HappyR\UserProjectBundle\Factory\ProjectFactory;
use Mockery as m;

/**
 * Class ProjectFactoryTest
 *
 * @author Tobias Nyholm
 *
 */
class ProjectFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMakePrivate()
    {
        $user=m::mock('HappyR\IdentifierInterface')
            ->shouldReceive('getId')->andReturn('4711')
            ->getMock();

        $project=m::mock('HappyR\UserProjectBundle\Entity\Project');
        $project
            ->shouldReceive('setName')->with('_private_4711')->andReturn($project)
            ->shouldReceive('setPublic')->with(false)->andReturn($project);

        $factory=new ProjectFactory();

        $factory->makePrivate($project, $user);
    }
}