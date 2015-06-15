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
    const AFTER_TYPE_DRAFT_LOADING = "bpaulin_setupezcontenttype.after_type_draft_loading";
    const AFTER_FIELD_DRAFT_LOADING = "bpaulin_setupezcontentfield.after_field_draft_loading";
    const AFTER_FIELD_STRUCTURE_LOADING = "bpaulin_setupezcontentfield.after_field_structure_loading";
    const AFTER_TYPE_STRUCTURE_LOADING = "bpaulin_setupezcontenttype.after_type_structure_loading";

    /**
     * Status
     */
    const STATUS_MISSING = 'missing';
    const STATUS_LOADED = 'loaded';
    const STATUS_CREATED = 'created';
    const STATUS_UPDATE_STRUCTURE = 'update';
    const STATUS_CREATE_STRUCTURE = 'create';
}
