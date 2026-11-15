<?php
if (!defined('DOKU_INC')) die();

class action_plugin_nopublicmediamanager extends \dokuwiki\Extension\ActionPlugin
{
    public function register(Doku_Event_Handler $controller): void
    {
        $controller->register_hook('MENU_ITEMS_ASSEMBLY', 'AFTER', $this, 'hideMediaManager');
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'blockMediaAction');
    }

    public function hideMediaManager(Doku_Event $event, $param): void
    {
        global $INFO;

        if ($event->data['view'] !== 'site') {
            return;
        }
        if (!empty($INFO['userinfo'])) {
            return;
        }

        $event->data['items'] = array_values(array_filter(
            $event->data['items'],
            fn($item) => !($item instanceof \dokuwiki\Menu\Item\Media)
        ));
    }

    public function blockMediaAction(Doku_Event $event, $param): void
    {
        global $INFO;

        // $event->data holds the requested action (the value of do=)
        if ($event->data !== 'media') {
            return;
        }
        if (!empty($INFO['userinfo'])) {
            return;
        }

        // Anonymous visitor: replace the action with "denied"
        $event->data = 'denied';
    }
}

