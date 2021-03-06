<?php

namespace spec\Pim\Bundle\ReferenceDataBundle\Doctrine\ORM\RequirementChecker;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\Model\ConfigurationInterface;
use Prophecy\Argument;

class ReferenceDataUniqueCodeCheckerSpec extends ObjectBehavior
{
    function let(EntityManagerInterface $em)
    {
        $this->beConstructedWith($em);
    }

    function it_checks_a_valid_reference_data($em, ConfigurationInterface $configuration, ClassMetadataInfo $metadata)
    {
        $em->getClassMetadata(Argument::any())->willReturn($metadata);
        $metadata->getFieldMapping('code')->willReturn([
            'fieldName'  => "code",
            'type'       => "string",
            'length'     => 255,
            'unique'     => true,
            'columnName' => "code"
        ]);

        $configuration->getClass()->willReturn('\StdClass');

        $this->check($configuration)->shouldReturn(true);
        $this->getFailure()->shouldReturn(null);
    }

    function it_checks_an_invalid_reference_data($em, ConfigurationInterface $configuration, ClassMetadataInfo $metadata)
    {
        $em->getClassMetadata(Argument::any())->willReturn($metadata);
        $metadata->getFieldMapping('code')->willReturn([
            'fieldName'  => "code",
            'type'       => "string",
            'length'     => 255,
            'unique'     => false,
            'columnName' => "code"
        ]);

        $configuration->getClass()->willReturn('\StdClass');

        $this->check($configuration)->shouldReturn(false);
        $this->getFailure()->shouldReturn(
            'Please configure a "code" column with a unique constraint in your Reference Data mapping.'
        );
    }
}
