<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Command\Cleaner;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Update the variant product to clean all the wrong boolean values.
 *
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WrongValuesOnVariantProductCleaner
{
    /**
     * Return true if the product has been modified, false otherwise
     *
     * @param ProductInterface $variantProduct
     *
     * @return bool
     */
    public function cleanProduct(ProductInterface $variantProduct): bool
    {
        if (!$variantProduct->isVariant()) {
            return false;
        }

        $isModified = false;
        $attributes = $variantProduct->getFamily()->getAttributes();
        foreach ($attributes as $attribute) {
            if ($this->isProductImpactedForAttribute($variantProduct, $attribute)) {
                $this->cleanProductForAttribute($variantProduct, $attribute);
                $isModified = true;
            }
        }

        return $isModified;
    }

    /**
     * We want to know which attribute that are in the parent level but actually in the children level
     *
     * @param ProductInterface   $variantProduct
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    private function isProductImpactedForAttribute(ProductInterface $variantProduct, AttributeInterface $attribute): bool
    {
        $familyVariant = $variantProduct->getFamilyVariant();
        $attributeLevel = $familyVariant->getLevelForAttributeCode($attribute->getCode());
        $hasAttributeInParentLevel = $attributeLevel !== $familyVariant->getNumberOfLevel();
        $attributeCodesInLastLevel = $variantProduct->getValuesForVariation()->getAttributesKeys();
        $hasValueForThisAttributeInLastLevel = in_array($attribute->getCode(), $attributeCodesInLastLevel);

        return $hasAttributeInParentLevel && $hasValueForThisAttributeInLastLevel;
    }


    /**
     * @param ProductInterface   $variantProduct
     * @param AttributeInterface $attribute
     */
    private function cleanProductForAttribute(ProductInterface $variantProduct, AttributeInterface $attribute): void
    {
        $values = $variantProduct->getValuesForVariation();
        $values->removeByAttribute($attribute);
        $variantProduct->setValues($values);
    }
}
