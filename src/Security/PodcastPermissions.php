<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Respinar\PodcastBundle\Security;

final class PodcastPermissions
{
    public const USER_CAN_EDIT_ARCHIVE    = 'contao_user.podcasts';
    public const USER_CAN_CREATE_ARCHIVES = 'contao_user.podcastp.create';
    public const USER_CAN_DELETE_ARCHIVES = 'contao_user.podcastp.delete';

    public const USER_CAN_EDIT_FEED    = 'contao_user.podcastfeeds';
    public const USER_CAN_CREATE_FEEDS = 'contao_user.podcastfeedp.create';
    public const USER_CAN_DELETE_FEEDS = 'contao_user.podcastfeedp.delete';
}
