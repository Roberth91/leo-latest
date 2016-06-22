<?php namespace KurtJensen\MyCalendar\Components;

use Cms\Classes\ComponentBase;
use Lang;

class EvList extends ComponentBase
{
    public $color;
    public $events;

    public function componentDetails()
    {
        return [
            'name' => 'kurtjensen.mycalendar::lang.evlist.name',
            'description' => 'kurtjensen.mycalendar::lang.evlist.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'events' => [
                'title' => 'kurtjensen.mycalendar::lang.evlist.events_title',
                'description' => 'kurtjensen.mycalendar::lang.evlist.events_description',
            ],
            'color' => [
                'title' => 'kurtjensen.mycalendar::lang.evlist.color_title',
                'description' => 'kurtjensen.mycalendar::lang.evlist.color_description',
                'type' => 'dropdown',
                'default' => 'red',
            ],
            'loadstyle' => [
                'title' => 'kurtjensen.mycalendar::lang.evlist.loadstyle_title',
                'description' => 'kurtjensen.mycalendar::lang.evlist.loadstyle_description',
                'type' => 'dropdown',
                'default' => '1',
                'options' => [
                    0 => 'kurtjensen.mycalendar::lang.evlist.opt_no',
                    1 => 'kurtjensen.mycalendar::lang.evlist.opt_yes'],
            ],
        ];
    }

    public function getColorOptions()
    {
        $colors = [
            'red' => Lang::get('kurtjensen.mycalendar::lang.month.color_red'),
            'green' => Lang::get('kurtjensen.mycalendar::lang.month.color_green'),
            'blue' => Lang::get('kurtjensen.mycalendar::lang.month.color_blue'),
            'yellow' => Lang::get('kurtjensen.mycalendar::lang.month.color_yellow'),
        ];
        return $colors;
    }

    public function onRender()
    {
        if ($this->property('loadstyle')) {
            $this->addCss('/plugins/kurtjensen/mycalendar/assets/css/calendar.css');
        }

        $this->color = $this->property('color');
        $this->events = $this->property('events');
    }
}
