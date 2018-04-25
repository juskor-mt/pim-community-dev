<?php

declare(strict_types=1);

namespace spec\Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\AggregatedCountProductAndProductModelValues;
use Pim\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Pim\Component\CatalogVolumeMonitoring\Volume\ReadModel\AverageMaxVolumes;
use Prophecy\Argument;

class AggregatedCountProductAndProductModelValuesSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection, 30);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AggregatedCountProductAndProductModelValues::class);
    }

    function it_is_a_count_query()
    {
        $this->shouldImplement(CountQuery::class);
    }

    function it_fetches_a_count_volume($connection, Statement $statement)
    {
        $connection->query(Argument::type('string'))->willReturn($statement);
        $statement->fetch()->willReturn([
            'max'     => 12,
            'average' => 7
        ]);

        $this->fetch()->shouldBeLike(new AverageMaxVolumes(12, 7, 30, 'average_max_product_and_product_model_values'));
    }

    function it_fetches_a_count_volume_with_an_empty_value_if_no_aggregated_volume_has_been_found($connection, Statement $statement)
    {
        $connection->query(Argument::type('string'))->willReturn($statement);
        $statement->fetch()->willReturn(null);

        $this->fetch()->shouldBeLike(new AverageMaxVolumes(0, 0, 30, 'average_max_product_and_product_model_values'));
    }
}
