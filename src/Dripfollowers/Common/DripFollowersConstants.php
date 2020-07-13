<?php

namespace DripFollowers\Common;

interface DripFollowersConstants {
    const OPT_UPSELL_NUMBER_PERCENTAGE = 'upsell-number-percentage';
    const OPT_UPSELL_PRICE_PERCENTAGE = 'upsell-price-percentage';
    const OPT_INSTAGRAM_CLIENT_ID = 'instagram-client-id';
    const OPT_INSTAGRAM_ACCESS_TOKEN = 'instagram-access-token';
    const OPT_INSTAGRAM_SERVICE_URL = 'instagram-service-url';
    const OPT_INSTAGRAM_SERVICE_ACCESS_ID = 'instagram-service-access-id';
    const OPT_PAYPAL_ACCOUNT = 'paypal-account';
    const OPT_PAYPAL_RETURN_PAGE = 'paypal-return-page';
    const OPT_SANDBOX = 'sandbox';
    const OPT_OPTIONS_NAME = 'drip-followers';
    const OPT_OPTIONS_GROUP = 'drip-followers-options';
    const OPT_EXPRESS_AVAILABILITY = 'express-availability';

    const OPT_VIEWS_AVAILABILITY = 'views-availability';

    const OPT_DRIPPED_AVAILABILITY = 'dripped-availability';
    const OPT_LIKES_AVAILABILITY = 'likes-availability';
    const OPT_DRIP_DAYS_BASE = 'drip-days-base';
    const OPT_SERVICE_DOWN_MSG = 'service-down-message';
    const COL_ORDER_CUSTOMER =          'order-customer';
    const COL_ORDER_CONTACT_EMAIL =     'order-contact-email';
    const COL_ORDER_SERVICE =           'order-service';
    const COL_ORDER_PACK =              'order-pack';
    const COL_ORDER_WITH_UPSELL =       'order-with-upsell';
    const COL_ORDER_NUMBER =            'order-number';
    const COL_ORDER_PROVIDER =          'order-provider';
    const COL_ORDER_TARGET =            'order-target';
    const COL_ORDER_PAYMENT_STATUS =    'order-payment-status';
    const COL_ORDER_PAYMENT_AMOUNT =    'order-payment-amount';
    const COL_ORDER_PAYMENT_TRX_ID =    'order-trx-id';
    const COL_ORDER_PROGRESS =          'order-progress';
    const COL_ORDER_DATE =              'order-date';
    const COL_ORDER_REMARKS =           'order-remarks';
    const COL_ORDER_TASK_ID =           'order-task-id';
    const COL_ORDER_ISSUE_NOTIFIED =    'order-issue-notified';
    const COL_ORDER_GIFTS =             'order-gifts';
    const COL_ORDER_INITIAL_COUNT =     'order-initial-count';
    const COL_ORDER_FINAL_COUNT =     	'order-final-count';
    const ORDER_COUNT_INITIAL_STAGE =	'INITIAL';
    const ORDER_COUNT_FINAL_STAGE =    	'FINAL';
    const CPT_DRIP_ORDER = 'driporder';
    const STATUS_PAYMENT_VALID = 'VALID';
    const STATUS_PAYMENT_INVALID = 'INVALID';
}