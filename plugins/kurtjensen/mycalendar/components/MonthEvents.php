<?php namespace KurtJensen\MyCalendar\Components;

use KurtJensen\MyCalendar\Components\Events;
use KurtJensen\MyCalendar\Components\Month;

class MonthEvents extends Month
{
    public $EventsComp = null;

    public function componentDetails()
    {
        return [
            'name' => 'kurtjensen.mycalendar::lang.month_events.name',
            'description' => 'kurtjensen.mycalendar::lang.month_events.description',
        ];
    }

    public function defineProperties()
    {
        $this->EventsComp = new Events();
        $this->EventsComp->compLink = 'MonthEvents';
        $properties = parent::defineProperties();

        return array_merge($properties, $this->EventsComp->defineProperties());
    }

    public function init()
    {
        $this->EventsComp->usePermissions = $this->property('usePermissions', 0);
        $this->EventsComp->dayspast = $this->property('dayspast', 120);
        $this->EventsComp->daysfuture = $this->property('daysfuture', 60);
    }

    public function onRun()
    {
        $this->events = $this->EventsComp->loadEvents();
    }

    public function onRender()
    {
        if ($this->property('loadstyle')) {
            $this->addCss('/plugins/kurtjensen/mycalendar/assets/css/calendar.css');
        }
        $this->month = in_array($this->property('month'), range(1, 12)) ? $this->property('month') : date('m');
        $this->year = in_array($this->property('year'), range(2014, 2030)) ? $this->property('year') : date('Y');
        $this->calcElements();
        $this->dayprops = $this->property('dayprops');
        $this->color = $this->property('color');
    }

    public function onShowEvent()
    {
        $this->EventsComp->compLink = 'MonthEvents';
        return $this->page['ev'] = $this->EventsComp->onShowEvent();
    }

}
