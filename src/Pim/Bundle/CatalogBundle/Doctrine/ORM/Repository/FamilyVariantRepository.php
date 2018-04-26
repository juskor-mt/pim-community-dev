<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Repository\FamilyVariantRepositoryInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantRepository extends EntityRepository implements FamilyVariantRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->findOneBy(['code' => $identifier]);
    }

    /**
     * {@inheritdoc}
     */
    public function findIdentifierByAttributeAxes(array $attributeAxesCode): array
    {
        $queryBuilder = $this->createQueryBuilder('familyVariant')
            ->select('familyVariant.code')
            ->innerJoin('familyVariant.variantAttributeSets', 'variantAttributeSets')
            ->innerJoin('variantAttributeSets.axes', 'axes')
            ->where('axes.code IN (:attributeCodes)')
            ->setParameter('attributeCodes', $attributeAxesCode);

        $codes = $queryBuilder->getQuery()
            ->getArrayResult();

        return array_map(
            function ($data) {
                return $data['code'];
            },
            $codes
        );
    }
}
