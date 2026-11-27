<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Extension\Event;
use dokuwiki\Menu\Item\Media;

/**
 * Prevents anonymous visitors from accessing DokuWiki's media manager.
 *
 * Licensed under the GNU Affero General Public License, version 3 or later.
 * See the LICENSE file in the project root for the full license text.
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
class action_plugin_nopublicmediamanager extends ActionPlugin
{
    /** Register hooks to hide the media menu item and block the media action. */
    public function register(EventHandler $controller): void
    {
        $controller->register_hook('MENU_ITEMS_ASSEMBLY', 'AFTER', $this, 'hideMediaManager');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'blockMediaAction');
    }

    /**
     * Remove the media manager from the site menu for anonymous visitors.
     *
     * @param Event $event Menu assembly event; its data contains the view and items.
     * @param mixed      $param Optional hook parameter supplied by DokuWiki.
     */
    public function hideMediaManager(Event $event, $param): void
    {
        global $INFO;

        // Only alter the regular site menu; leave other menu views untouched.
        if ($event->data['view'] !== 'site') {
            return;
        }

        // Authenticated users retain normal access to the media manager.
        if (!empty($INFO['userinfo'])) {
            return;
        }

        // Filter out Media menu entries while preserving the remaining item order.
        $event->data['items'] = array_values(array_filter(
            $event->data['items'],
            fn($item) => !($item instanceof Media)
        ));
    }

    /**
     * Deny direct requests for the media action from anonymous visitors.
     *
     * @param Event $event Action preprocessing event; data contains the requested action.
     * @param mixed      $param Optional hook parameter supplied by DokuWiki.
     */
    public function blockMediaAction(Event $event, $param): void
    {
        global $INFO;

        // $event->data holds the requested action (the value of do=).
        if ($event->data !== 'media') {
            return;
        }

        // Authenticated users retain normal access to the media manager.
        if (!empty($INFO['userinfo'])) {
            return;
        }

        // Replace the media action so DokuWiki handles the request as denied.
        $event->data = 'denied';
    }
}
