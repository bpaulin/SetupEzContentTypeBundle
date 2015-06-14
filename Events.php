<?php

namespace Bpaulin\SetupEzContentTypeBundle;

/**
 * Class Events
 * @package Bpaulin\SetupEzContentTypeBundle
 */
final class Events
{
    /**
     * Events
     */
    const AFTER_GROUP_LOADING = "bpaulin_setupezcontenttype.after_group_loading";

    /**
     * Status
     */
    const STATUS_MISSING = 'missing';
    const STATUS_LOADED = 'loaded';
    const STATUS_CREATED = 'created';
}
