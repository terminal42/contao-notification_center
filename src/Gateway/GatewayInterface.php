<?php

declare(strict_types=1);

namespace Terminal42\NotificationCenterBundle\Gateway;

use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotFinalizeParcelException;
use Terminal42\NotificationCenterBundle\Parcel\Parcel;
use Terminal42\NotificationCenterBundle\Receipt\Receipt;

interface GatewayInterface
{
    public function getName(): string;

    /**
     * In this method, Gateways are expected to finalize the
     * parcel in such a way, that there is no dynamic data anymore
     * when actually sending (@see sendParcel) the parcel.
     * At this stage you should:.
     *
     * - Replace tokens
     * - Replace insert tags
     * - Add stamps required to actually send the parcel
     * - etc.
     *
     * If you imagine a post office, here's where you actually
     * replace all the placeholders on your address label,
     * wrap and seal it, and it's ready to go. After that,
     * the parcel is considered immutable.
     *
     * @throws CouldNotFinalizeParcelException If the parcel cannot be finalized due to e.g. wrong/missing stamps, content etc.
     */
    public function finalizeParcel(Parcel $parcel): Parcel;

    /**
     * In this method, Gateways receive the finalized
     * parcel. It should be treated immutable at this stage.
     * Noting in this method should be dependent from the outside.
     * E.g. do not depend on something like the current request etc.
     * Only use data from the parcel, nothing else.
     *
     * This method MUST NOT throw an exception. It is expected to
     * always return a receipt.
     */
    public function sendParcel(Parcel $parcel): Receipt;
}
