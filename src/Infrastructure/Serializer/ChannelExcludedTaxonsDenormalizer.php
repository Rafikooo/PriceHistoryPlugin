<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\PriceHistoryPlugin\Infrastructure\Serializer;

use ApiPlatform\Core\Api\IriConverterInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Webmozart\Assert\Assert;

final class ChannelExcludedTaxonsDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'sylius_price_history_channel_excluded_taxons_denormalizer_already_called';

    public function __construct(private IriConverterInterface $iriConverter)
    {
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return
            !isset($context[self::ALREADY_CALLED]) &&
            is_array($data) &&
            is_a($type, ChannelInterface::class, true)
        ;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        $context[self::ALREADY_CALLED] = true;
        $data = (array) $data;

        $channel = $this->denormalizer->denormalize($data, $type, $format, $context);
        Assert::isInstanceOf($channel, ChannelInterface::class);

        /** @var TaxonInterface $taxon */
        foreach ($channel->getTaxonsExcludedFromShowingLowestPrice() as $taxon) {
            $channel->removeTaxonExcludedFromShowingLowestPrice($taxon);
        }

        foreach ($data['taxonsExcludedFromShowingLowestPrice'] ?? [] as $excludedTaxonIri) {
            /** @var TaxonInterface $taxon */
            $taxon = $this->iriConverter->getItemFromIri($excludedTaxonIri);

            $channel->addTaxonExcludedFromShowingLowestPrice($taxon);
        }

        return $channel;
    }
}
