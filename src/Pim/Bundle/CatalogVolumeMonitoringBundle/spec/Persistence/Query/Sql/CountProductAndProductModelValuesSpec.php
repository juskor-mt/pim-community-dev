<?php

declare(strict_types=1);

namespace spec\Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogVolumeMonitoringBundle\Persistence\Query\Sql\AggregatedCountProductAndProductModelValues;
use Pim\Component\CatalogVolumeMonitoring\Volume\Query\CountQuery;
use Pim\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;

class CountProductAndProductModelValuesSpec extends ObjectBehavior
{
    function let(CountQuery $countProductValuesQuery, CountQuery $countProductModelValuesQuery)
    {
        $this->beConstructedWith($countProductValuesQuery, $countProductModelValuesQuery, 12);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AggregatedCountProductAndProductModelValues::class);
    }

    function it_is_a_count_query()
    {
        $this->shouldImplement(CountQuery::class);
    }

    function it_gets_count_volume(
        $countProductValuesQuery,
        $countProductModelValuesQuery,
        CountVolume $countProductValues,
        CountVolume $countProductModelValues
    ) {
        $countProductValuesQuery->fetch()->willReturn($countProductValues);
        $countProductModelValuesQuery->fetch()->willReturn($countProductModelValues);

        $countProductValues->getVolume()->willReturn(5);
        $countProductModelValues->getVolume()->willReturn(4);

        $this->fetch()->shouldBeLike(new CountVolume(9, 12, 'count_product_and_product_model_values'));
    }
}
