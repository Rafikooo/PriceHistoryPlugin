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

namespace Sylius\PriceHistoryPlugin\Infrastructure\EntityObserver;

use Sylius\PriceHistoryPlugin\Domain\Model\ChannelInterface;
use Sylius\PriceHistoryPlugin\Infrastructure\Doctrine\ORM\ChannelPricingLogEntryRepositoryInterface;
use Webmozart\Assert\Assert;

final class ProcessLowestPriceOnCheckingPeriodChangeObserver implements EntityObserverInterface
{
    public function __construct(private ChannelPricingLogEntryRepositoryInterface $channelPricingLogEntryRepository)
    {
    }

    public function onChange(object $entity): void
    {
        Assert::isInstanceOf($entity, ChannelInterface::class);

        $this->channelPricingLogEntryRepository->bulkUpdateLowestPricesBeforeDiscount($entity);
    }

    public function supports(object $entity): bool
    {
        return $entity instanceof ChannelInterface;
    }

    public function observedFields(): array
    {
        return ['lowestPriceForDiscountedProductsCheckingPeriod'];
    }
}
