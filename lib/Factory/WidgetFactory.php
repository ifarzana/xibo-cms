<?php
/*
 * Xibo - Digital Signage - http://www.xibo.org.uk
 * Copyright (C) 2015 Spring Signage Ltd
 *
 * This file (WidgetFactory.php) is part of Xibo.
 *
 * Xibo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Xibo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Xibo.  If not, see <http://www.gnu.org/licenses/>.
 */


namespace Xibo\Factory;


use Xibo\Entity\Widget;
use Xibo\Exception\NotFoundException;

class WidgetFactory
{
    /**
     * Load widgets by Playlist ID
     * @param int $playlistId
     * @return array[Widget]
     */
    public static function getByPlaylistId($playlistId)
    {
        return WidgetFactory::query(null, array('playlistId' => $playlistId));
    }

    /**
     * Get widget by widget id
     * @param $widgetId
     * @return Widget
     */
    public static function getById($widgetId)
    {
        $widgets = WidgetFactory::query(null, array('widgetId' => $widgetId));
        return $widgets[0];
    }

    /**
     * Load widget by widget id
     * @param $widgetId
     * @return Widget
     * @throws NotFoundException
     */
    public static function loadByWidgetId($widgetId)
    {
        $widgets = WidgetFactory::query(null, array('widgetId' => $widgetId));

        if (count($widgets) <= 0)
            throw new NotFoundException(__('Widget not found'));

        $widget = $widgets[0];
        /* @var Widget $widget */
        $widget->load();
        return $widget;
    }

    /**
     * Create a new widget
     * @param int $ownerId
     * @param int $playlistId
     * @param string $type
     * @param int $duration
     * @return Widget
     */
    public static function create($ownerId, $playlistId, $type, $duration)
    {
        $widget = new Widget();
        $widget->ownerId = $ownerId;
        $widget->playlistId = $playlistId;
        $widget->type = $type;
        $widget->duration = $duration;

        return $widget;
    }

    public static function query($sortOrder = null, $filterBy = null)
    {
        if ($sortOrder == null)
            $sortOrder = array('displayOrder');

        $entries = array();

        $params = array();
        $sql = 'SELECT * FROM `widget` WHERE 1 = 1';

        if (\Kit::GetParam('playlistId', $filterBy, _INT) != 0) {
            $sql .= ' AND playlistId = :playlistId';
            $params['playlistId'] = \Kit::GetParam('playlistId', $filterBy, _INT);
        }

        if (\Kit::GetParam('widgetId', $filterBy, _INT) != 0) {
            $sql .= ' AND widgetId = :widgetId';
            $params['widgetId'] = \Kit::GetParam('widgetId', $filterBy, _INT);
        }

        // Sorting?
        if (is_array($sortOrder))
            $sql .= ' ORDER BY ' . implode(',', $sortOrder);

        foreach (\PDOConnect::select($sql, $params) as $row) {
            $widget = new Widget();
            $widget->widgetId = \Kit::ValidateParam($row['widgetId'], _INT);
            $widget->playlistId = \Kit::ValidateParam($row['playlistId'], _INT);
            $widget->ownerId = \Kit::ValidateParam($row['ownerId'], _INT);
            $widget->type = \Kit::ValidateParam($row['type'], _WORD);
            $widget->duration = \Kit::ValidateParam($row['duration'], _INT);
            $widget->displayOrder = \Kit::ValidateParam($row['displayOrder'], _INT);

            $entries[] = $widget;
        }

        return $entries;
    }
}